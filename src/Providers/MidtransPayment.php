<?php

namespace Cloudbadak\PaymentHub\Providers;

use Cloudbadak\PaymentHub\Contracts\AbstractPaymentGateway;
use Cloudbadak\PaymentHub\Data\PaymentResponse;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Enums\PaymentStatus;
use Cloudbadak\PaymentHub\Enums\PaymentType;
use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Driver\ApiRequest;
use Cloudbadak\PaymentHub\Exceptions\PaymentException;
use Cloudbadak\PaymentHub\Exceptions\RequestServerException;
use Cloudbadak\PaymentHub\Exceptions\RequestClientException;
use Cloudbadak\PaymentHub\Exceptions\UnsupportedPaymentMethodException;

class MidtransPayment extends AbstractPaymentGateway
{
    protected ApiRequest $apiRequest;
    protected bool $isProduction = false;

    protected string $serverKey;
    protected string $clientKey;
    protected array $headers = [];
    
    public function __construct()
    {
        $this->bankCodeMap = [
            BankCode::MANDIRI->value => 'echannel',
            BankCode::BCA->value => 'bca',
            BankCode::BNI->value => 'bni',
            BankCode::BRI->value => 'bri',
            BankCode::CIMB->value => 'cimb',
            BankCode::PERMATA->value => 'permata',
            BankCode::SEABANK->value => 'seabank',
        ];

        $this->walletCodeMap = [
            EWalletCode::GOPAY->value => 'gopay',
            EWalletCode::OVO->value => 'ovo',
            EWalletCode::DANA->value => 'dana',
            EWalletCode::SHOPEEPAY->value => 'shopeepay',
        ];

        $this->outletCodeMap = [
            OutletCode::ALFAMART->value => 'alfamart',
            OutletCode::INDOMARET->value => 'indomaret',
        ];

        $this->qrPaymentCodeMap = [
            QRPaymentCode::QRIS->value => 'qris',
        ];

        $this->cardlessCreditCodeMap = [
            CardlessCreditCode::AKULAKU->value => 'akulaku',
            CardlessCreditCode::KREDIVO->value => 'kredivo',
        ];

        $this->isProduction = getenv('MIDTRANS_ENVIRONMENT') === 'production';
        $this->serverKey = getenv('MIDTRANS_SERVER_KEY');
        $this->clientKey = getenv('MIDTRANS_CLIENT_KEY');
        $this->headers = [
            'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            'Content-Type' => 'application/json',
        ];
        $this->apiRequest = new ApiRequest(
            $this->isProduction ? 'https://api.midtrans.com/v2/' : 'https://api.sandbox.midtrans.com/v2/',
            5,
            $this->headers
        );
    }

    /**
     * BALANCE & GET PAYMENT
     */

    public function balance(): string
    {
        throw new UnsupportedPaymentMethodException("Midtrans does not support get balance");
    }

    public function get(string $orderId): PaymentResponse
    {
        $request = $this->apiRequest->get($orderId . '/status');
        return $this->makeResponse($request, $this->apiRequest->getResponseCode());
    }

    /**
     * ACCEPT PAYMENTS
     */

    public function payWithVirtualAccount(PaymentRequest $request): PaymentResponse
    {
        $bankCode = $this->resolveBankCode($request->getBank());
        $payload = [
            "payment_type" => 'bank_transfer',
            "transaction_details" => [
                "order_id" => $request->getOrderId(),
                "gross_amount" => $request->getAmount(),
            ],
            "bank_transfer" => [
                "bank" => $bankCode,
            ]
        ];

        if($bankCode === 'echannel') {
            $payload['payment_type'] = 'echannel';
            $payload['echannel'] = [
                "bill_info1" => "Payment for Order ID: " . $request->getOrderId(),
                "bill_info2" => "Please follow the instructions sent by Midtrans to complete your payment."
            ];
            unset($payload['bank_transfer']);
        }

        if($bankCode === 'permata' && $request->getCustomer() && $request->getCustomer()->getFullName()) {
            $payload['bank_transfer']['permata']['recipient_name'] = $request->getCustomer()->getFullName();
        }

        $payload = $this->setOptionalPayload($request, $payload);
        $response = $this->apiRequest->post('charge', $payload);
        return $this->makeResponse($response, $this->apiRequest->getResponseCode());
    }

    public function payWithEWallet(PaymentRequest $request): PaymentResponse
    {
        $walletCode = $this->resolveWalletCode($request->getEWallet());
        $payload = [
            "payment_type" => $walletCode,
            "transaction_details" => [
                "order_id" => $request->getOrderId(),
                "gross_amount" => $request->getAmount(),
            ],
            $walletCode => [
                "enable_callback" => false,
                "callback_url" => null,
                "payer_phone_number" => $request->getCustomer() ? $request->getCustomer()->getPhone() : null,
            ]
        ];

        $payload = $this->setOptionalPayload($request, $payload);
        $response = $this->apiRequest->post('charge', $payload);
        return $this->makeResponse($response, $this->apiRequest->getResponseCode());
    }

    public function payWithCard(PaymentRequest $request): PaymentResponse
    {
        $payload = [
            "payment_type" => 'credit_card',
            "transaction_details" => [
                "order_id" => $request->getOrderId(),
                "gross_amount" => $request->getAmount(),
            ],
            "credit_card" => [
                "token_id" => $request->getCardTokenId(),
            ],
        ];

        $payload = $this->setOptionalPayload($request, $payload);
        $response = $this->apiRequest->post('charge', $payload);
        return $this->makeResponse($response, $this->apiRequest->getResponseCode());
    }

    public function payWithQRPayment(PaymentRequest $request): PaymentResponse
    {
        $qrCode = $this->resolveQRPaymentCode($request->getQrPayment());
        $payload = [
            "payment_type" => $qrCode,
            "transaction_details" => [
                "order_id" => $request->getOrderId(),
                "gross_amount" => $request->getAmount(),
            ],
        ];

        $payload = $this->setOptionalPayload($request, $payload);
        $response = $this->apiRequest->post('charge', $payload);
        return $this->makeResponse($response, $this->apiRequest->getResponseCode());
    }

    public function payWithOutlet(PaymentRequest $request): PaymentResponse
    {
        $outletCode = $this->resolveOutletCode($request->getOutlet());
        $payload = [
            "payment_type" => "cstore",
            "transaction_details" => [
                "order_id" => $request->getOrderId(),
                "gross_amount" => $request->getAmount(),
            ],
            "cstore" => [
                "store" => $outletCode
            ]
        ];

        $payload = $this->setOptionalPayload($request, $payload);
        $response = $this->apiRequest->post('charge', $payload);
        return $this->makeResponse($response, $this->apiRequest->getResponseCode());
    }

    public function payWithCardlessCredit(PaymentRequest $request): PaymentResponse
    {
        $creditCode = $this->resolveCardlessCreditCode($request->getCardlessCredit());
        $payload = [
            "payment_type" => $creditCode,
            "transaction_details" => [
                "order_id" => $request->getOrderId(),
                "gross_amount" => $request->getAmount(),
                "currency" => "IDR",
            ]
        ];

        $payload = $this->setOptionalPayload($request, $payload);
        $response = $this->apiRequest->post('charge', $payload);
        return $this->makeResponse($response, $this->apiRequest->getResponseCode());
    }

    /**
     * WEBHOOK
     */

    public function webhook(?string $payload = null): PaymentResponse
    {
        $rawBody = file_get_contents('php://input');
        if (empty($rawBody)) {
            throw new RequestException('Midtrans webhook: empty request body.');
        }

        $data = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            throw new RequestException('Midtrans webhook: invalid JSON payload.');
        }

        $orderId = $data['order_id'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;
        $statusCode = $data['status_code'] ?? null;
        $grossAmount = $data['gross_amount'] ?? null;
        $incomingSig = $data['signature_key'] ?? null;

        $expectedSig = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        if (! hash_equals($expectedSig, $incomingSig)) {
            throw new RequestException('Midtrans webhook: invalid signature.');
        }

        return new PaymentResponse(
            $orderId,
            $transactionId,
            $this->fetchStatus($data),
            (int) $grossAmount
        );
    }

    /**
     * INTERNAL HELPERS
     */

    private function setOptionalPayload(PaymentRequest $request, array $payload): array
    {
        if($request->getItems()) {
            $payload['item_details'] = array_map(function($item) {
                return [
                    'id' => $item->id ?? null,
                    'price' => $item->price ?? null,
                    'quantity' => $item->quantity ?? null,
                    'name' => $item->name ?? null,
                ];
            }, $request->getItems());
        }

        if($request->getCustomer()) {
            $payload['customer_details'] = [
                'first_name' => $request->getCustomer()->getFirstName() ?? null,
                'last_name' => $request->getCustomer()->getLastName() ?? null,
                'email' => $request->getCustomer()->getEmail() ?? null,
                'phone' => $request->getCustomer()->getPhone() ?? null,
            ];
        }

        if($request->getSeller()) {
            $payload['seller_details'] = [
                'id' => $request->getSeller()->getId() ?? null,
                'name' => $request->getSeller()->getName() ?? null,
                'email' => $request->getSeller()->getEmail() ?? null,
                'url' => $request->getSeller()->getUrl() ?? null,
            ];
        }

        return $payload;
    }

    private function fetchStatus($data)
    {
        $transactionStatus = strtolower($data['transaction_status'] ?? '');
        $fraudStatus = strtolower($data['fraud_status'] ?? '');

        if ($transactionStatus === 'capture') {
            $status = ($fraudStatus === 'accept') ? PaymentStatus::PAID : PaymentStatus::UNPAID;
        } elseif ($transactionStatus === 'settlement') {
            $status = PaymentStatus::PAID;
        } elseif ($transactionStatus === 'pending') {
            $status = PaymentStatus::UNPAID;
        } elseif($transactionStatus === 'refund' || $transactionStatus === 'partial_refund') {
            $status = PaymentStatus::REFUND;
        } else {
            $status = PaymentStatus::UNPAID;
        }

        if($status == PaymentStatus::UNPAID && isset($data['expiry_time']) && strtotime($data['expiry_time']) < time()) {
            $status = PaymentStatus::EXPIRED;
        }
        return $status;
    }

    private function fetchType($request){
        $paymentType = strtolower($request['payment_type'] ?? '');

        if($paymentType == 'credit_card'){
            return PaymentType::CARD;
        } elseif($paymentType == 'bank_transfer') {
            return PaymentType::VIRTUAL_ACCOUNT;
        } elseif($paymentType == 'echannel') {
            return PaymentType::VIRTUAL_ACCOUNT;
        } elseif(in_array($paymentType, ['gopay', 'shopeepay', 'ovo', 'dana'])) {
            return PaymentType::E_WALLET;
        } elseif($paymentType == 'qris') {
            return PaymentType::QR_PAYMENT;
        } elseif(in_array($paymentType, ['cstore', 'alfamart', 'indomaret'])) {
            return PaymentType::RETAIL_OUTLET;
        } elseif(in_array($paymentType, ['akulaku', 'kredivo'])) {
            return PaymentType::CARDLESS_CREDIT;
        }

        return null;
    }

    private function makeResponse(array $request, int $responseCode)
    {
        $statusCode = (int) ($request['status_code'] ?? $responseCode ?? 400);
        if($statusCode >= 500) {
            throw new RequestServerException('Failed: ' . ($request['status_message'] ?? 'Unknown error'));
        } elseif($statusCode >= 400) {
            throw new RequestClientException('Failed: ' . ($request['status_message'] ?? 'Unknown error'));
        } elseif(!in_array($statusCode, [200, 201])) {
            throw new RequestServerException('Failed: ' . ($request['status_message'] ?? 'Unknown error'));
        }

        $orderId = $request['order_id'] ?? '';
        $transactionId = $request['transaction_id'] ?? '';
        $status = $this->fetchStatus($request);
        $amount = isset($request['gross_amount']) ? (int) $request['gross_amount'] : null;

        if((empty($orderId) && empty($transactionId)) || $status === null) {
            throw new RequestServerException($request['status_message'] ?? 'Failed to fetch payment status: Invalid response from Midtrans API');
        }

        $response = new PaymentResponse($orderId, $transactionId, $status, $amount);
        $response->setType($this->fetchType($request));
        $response->setTime($request['transaction_time'] ?? null, $request['expiry_time'] ?? null);

        if(($request['permata_va_number'] ?? false) ){
            $response->setBankTransfer($request['permata_va_number']);
        }

        if(($request['va_numbers'] ?? false) && is_array($request['va_numbers'])) {
            $vaInfo = end($request['va_numbers']);
            if ($vaInfo) {
                $response->setBankTransfer($vaInfo['va_number'] ?? null);
            }
        }

        if($request['bill_key'] ?? false){
            $response->setBankTransfer($request['bill_key'], $request['biller_code'] ?? null);
        }

        if($request['masked_card'] ?? false) {
            $response->setCard($request['card_type'] ?? null, $request['card_brand'] ?? $request['bank'] ?? null, $request['masked_card'] ?? null, $request['approval_code'] ?? null);
        }

        if($request['actions'] ?? null) {
            foreach ($request['actions'] as $action) {
                if ($action['name'] === 'generate-qr-code') {
                    $response->setQRPaymentLink($action['url']);
                } elseif ($action['name'] === 'generate-qr-code-v2') {
                    $response->setQRPaymentLink($action['url']);
                } elseif ($action['name'] === 'deeplink-redirect') {
                    $response->setPaymentUrlWeb($action['url']);
                }
            }
        }

        if($request['redirect_url'] ?? null) {
            $response->setPaymentUrlWeb($request['redirect_url']);
        }

        if($request['payment_code'] ?? null) {
            $response->setRetailOutlet($request['payment_code']);
        }

        return $response;
    }
}