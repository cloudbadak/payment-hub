<?php

namespace Cloudbadak\PaymentHub\Providers;

/**
 * Environment:
 * - PIVOT_ENVIRONMENT (development|production)
 * - PIVOT_ID
 * - PIVOT_SECRET
 * - PIVOT_WEBHOOK
 */

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
use Cloudbadak\PaymentHub\Driver\Cache;
use Cloudbadak\PaymentHub\Driver\Security;

class PivotPayment extends AbstractPaymentGateway
{
    protected ApiRequest $apiRequest;
    protected bool $isProduction = false;
    protected string $baseProd = 'https://api.pivot-payment.com/';
    protected string $baseSand = 'https://api-stg.pivot-payment.com/';

    protected array $headers = [];
    protected Cache $cache;
    
    public function __construct(Cache $cache, ?string $requestId = null)
    {
        $this->cache = $cache;
        $this->bankCodeMap = [
            BankCode::MANDIRI->value => 'MANDIRI',
            BankCode::BCA->value => 'BCA',
            BankCode::BNI->value => 'BNI',
            BankCode::BRI->value => 'BRI',
            BankCode::BSI->value => 'BSI',
            BankCode::CIMB->value => 'CIMB',
            BankCode::PERMATA->value => 'PERMATA',
            BankCode::DANAMON->value => 'DANAMON',
            BankCode::SMBC->value => 'SMBC',
            BankCode::BNC->value => 'BNC',
        ];

        $this->walletCodeMap = [
            EWalletCode::DANA->value => 'DANA',
            EWalletCode::SHOPEEPAY->value => 'SHOPEEPAY',
        ];

        $this->qrPaymentCodeMap = [
            QRPaymentCode::QRIS->value => 'QRIS',
        ];

        $this->isProduction = getenv('PIVOT_ENVIRONMENT') === 'production';
        $this->apiRequest = new ApiRequest(
            $this->isProduction ? $this->baseProd : $this->baseSand,
            5,
            $this->headers
        );
        $this->headers = ['Content-Type' => 'application/json'];
        if($requestId){
            $this->headers['X-REQUEST-ID'] = $requestId;
        }
    }

    private function addTokenToRequest(): bool
    {
        $token = $this->token();
        if(!$token){
            return false;
        }

        $this->headers['Authorization'] = $token ?: null;
        return is_string($this->headers['Authorization']);
    }

    /**
     * BALANCE & GET PAYMENT
     */

    public function token(): ?string
    {
        $pivotId = getenv('PIVOT_ID');
        $pivotSecret = getenv('PIVOT_SECRET');

        if(strlen($pivotId ?? '') == 0 || strlen($pivotSecret ?? '') == 0){
            throw new RequestClientException("PIVOT_ID dan/atau PIVOT_SECRET belum disiapkan!");
        }

        $accessTokenEncrypted = $this->cache->get("paymenthub-pivot-credential");
        if($accessTokenEncrypted){
            try {
                return Security::decrypt($accessTokenEncrypted, $pivotSecret);
            } catch (\Exception $e) {
                throw new PaymentException($e->getMessage());
            }
        }

        $payload = ["grantType" => "client_credentials"];
        $response = $this->apiRequest->post('v1/access-token', $payload, [
            'X-MERCHANT-ID' => $pivotId,
            'X-MERCHANT-SECRET' => $pivotSecret,
        ]);

        if(!isset($response['data']['accessToken']) || !isset($response['data']['tokenType'])){
            $errorCode = $response['message'] ?? $response['code'] ?? null;
            throw new RequestServerException($errorCode ? "Pivot Error: $errorCode" : "Access Token gagal diperbaharui!");
        }
        
        try {
            $accessToken = implode(" ", [$response['data']['tokenType'], $response['data']['accessToken']]);
            $expires = $response['data']['expiresIn'] ?? 900;
            
            $accessTokenEncrypted = Security::encrypt($accessToken, $pivotSecret);
            $this->cache->save("paymenthub-pivot-credential", $accessTokenEncrypted, $expires ?: 900);
            return $accessToken;
        } catch (\Exception $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    public function balance(string $usecase = 'DISBURSEMENT'): ?string
    {
        // $usecase = DISBURSEMENT|PAYMENT|WALLET
        if (!$this->addTokenToRequest()) {
            throw new RequestClientException("Failed to obtain access token");
        }
        $response = $this->apiRequest->get('v1/balances', ['usecase' => $usecase], $this->headers);
        if(!$this->apiRequest->isOk()){
            $errorCode = $response['message'] ?? $response['code'] ?? null;
            throw new RequestServerException($errorCode ? 'Pivot Error: '.$errorCode : 'Kegagalan memanggil API');
        }
        return $response['data']['availableBalance']['value'] ?? null;
    }

    public function get(string $orderId): PaymentResponse
    {
        $request = $this->apiRequest->get("v2/payments", ['clientReferenceId' => $orderId], $this->headers);
        if(!$this->apiRequest->isOk()){
            $errorCode = $response['message'] ?? $response['code'] ?? null;
            throw new RequestServerException($errorCode ? 'Pivot Error: '.$errorCode : 'Kegagalan memanggil API');
        }
        return $this->makeResponse($request['data'][0] ?? null, $this->apiRequest->getResponseCode());
    }

    /**
     * ACCEPT PAYMENTS
     */

    public function payWithVirtualAccount(PaymentRequest $request): ?PaymentResponse
    {
        $bankCode = $this->resolveBankCode($request->getBank());
        $customer = $request->getCustomer();
        $payload = [
            'mode' => 'API',
            'paymentMethod' => ['type' => 'VIRTUAL_ACCOUNT'],
            'paymentMethodOptions' => [
                'virtualAccount' => ['channel' => $bankCode, 'virtualAccountName' => $customer->getFullName()]
            ],
        ];
        return $this->createPaymentSession($request, $payload);
    }

    public function payWithEWallet(PaymentRequest $request): ?PaymentResponse
    {
        $walletCode = $this->resolveWalletCode($request->getWallet());
        $payload = [
            'mode' => 'API',
            'paymentMethod' => ['type' => 'EWALLET'],
            'paymentMethodOptions' => [
                'ewallet' => ['channel' => $walletCode],
            ]
        ];
        return $this->createPaymentSession($request, $payload);
    }

    public function payWithCard(PaymentRequest $request): ?PaymentResponse
    {
        // via redirect
        $payload = [
            'mode' => 'REDIRECT',
            'paymentMethod' => ['type' => 'CARD'],
            'paymentMethodOptions' => [
                'card' => ['captureMethod' => 'automatic', 'threeDsMethod' => 'CHALLENGE'],
            ]
        ];
        return $this->createPaymentSession($request, $payload);
    }

    public function payWithQRPayment(PaymentRequest $request): ?PaymentResponse
    {
        $payload = [
            'mode' => 'API',
            'paymentMethod' => ['type' => 'QR'],
            'paymentMethodOptions' => [
                'qr' => ['expiryAt' => date('c', strtotime('+15 minute'))],
            ]
        ];
        return $this->createPaymentSession($request, $payload);
    }

    public function payWithOutlet(PaymentRequest $request): ?PaymentResponse
    {
        $outletCode = $this->resolveOutletCode($request->getOutlet());
        throw new UnsupportedPaymentMethodException("Pivot does not support payWithOutlet");
    }

    public function payWithCardlessCredit(PaymentRequest $request): ?PaymentResponse
    {
        $creditCode = $this->resolveCardlessCreditCode($request->getCardlessCredit());
        throw new UnsupportedPaymentMethodException("Pivot does not support payWithCardlessCredit");
    }

    /**
     * WEBHOOK
     */

    public function webhook(?string $payload = null): ?PaymentResponse
    {
        $headerApiKey = 'X-API-Key';
        $callbackApiKey = getenv('PIVOT_WEBHOOK');
        $this->verifyApiKeyHeader($headerApiKey, $callbackApiKey);

        $rawBody = $payload ?? file_get_contents('php://input');
        if (empty($rawBody)) {
            throw new RequestClientException('Pivot webhook: empty request body.');
        }

        $data = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            throw new RequestClientException('Pivot webhook: invalid JSON payload.');
        }

        $orderId = $data['data']['clientReferenceId'] ?? null;
        $transactionId = $data['data']['id'] ?? null;
        $status = $data['data']['status'] ?? null;
        $grossAmount = $data['data']['amount']['value'] ?? 0;

        return new PaymentResponse(
            $orderId,
            $transactionId,
            $this->fetchStatus($data['data'] ?? null),
            (int) $grossAmount
        );
    }

    /**
     * INTERNAL HELPERS
     */

    private function verifyApiKeyHeader(string $headerName, string $expectedKey): void
    {
        if (empty($expectedKey)) {
            throw new RequestClientException("PIVOT_WEBHOOK environment variable is not configured!");
        }

        // Get the API key from request headers
        $incomingApiKey = $this->getHeaderValue($headerName);

        if (empty($incomingApiKey)) {
            throw new RequestClientException("Missing {$headerName} header in request.");
        }

        // Compare API keys using hash_equals to prevent timing attacks
        if (!hash_equals($expectedKey, $incomingApiKey)) {
            throw new RequestClientException("Invalid {$headerName} header value.");
        }
    }

    private function getHeaderValue(string $headerName): ?string
    {
        // Convert header name to HTTP_ prefix format (e.g., X-API-Key -> HTTP_X_API_KEY)
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));

        // Try to get from $_SERVER first
        if (isset($_SERVER[$headerKey])) {
            return $_SERVER[$headerKey];
        }

        // Fallback to getallheaders() if available
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers[$headerName])) {
                return $headers[$headerName];
            }
            // Case-insensitive search
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, $headerName) === 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function createPaymentSession(PaymentRequest $request, array $payload): ?PaymentResponse
    {
        if (!$this->addTokenToRequest()) {
            throw new RequestClientException("Failed to obtain access token");
        }
        $payload['clientReferenceId'] = $request->getOrderId();
        $payload['amount'] = ['value' => $request->getAmount(), 'currency' => 'IDR'];
        $payload['paymentType'] = $payload['paymentType'] ?? 'SINGLE';
        $payload['autoConfirm'] = true;
        $payload['redirectUrl'] = [
            'successReturnUrl' => $request->getSuccessReturnUrl(),
            'failureReturnUrl' => $request->getFailureReturnUrl(),
            'expirationReturnUrl' => $request->getExpirationReturnUrl()
        ];
        $payload = $this->requestCustomer($payload, $request->getCustomer());
        $payload = $this->requestItems($payload, $request->getItems()); // is required
        $response = $this->apiRequest->post('v2/payments', $payload, $this->headers);
        if(!$this->apiRequest->isOk()){
            $errorCode = $response['message'] ?? $response['code'] ?? null;
            throw new RequestServerException(json_encode($response));
            throw new RequestServerException($errorCode ? 'Pivot Error: '.$errorCode : 'Kegagalan memanggil API');
        }
        return $this->makeResponse($response['data'] ?? null, $this->apiRequest->getResponseCode());
    }

    private function requestCustomer(array $payload, $customer = null)
    {
        if(!$customer) return $payload;
        $payload['customer'] = [
            'givenName' => $customer->getFirstName(),
            'surname' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'phoneNumber' => [
                'countryCode' => $customer->getPhoneCode(),
                'number' => $customer->getPhoneNumber()
            ]
        ];
        return $payload;
    }

    private function requestItems(array $payload, ?array $items = null)
    {
        if(!$items) return $payload;
        foreach ($items as $item) {
            $payload['orderInformation']['productDetails'][] = [
                'type' => 'PHYSICAL',
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'quantity' => $item->getQuantity(),
                'price' => ['value' => $item->getPrice(), 'currency' => 'IDR'],
            ];
        }
        return $payload;
    }

    private function fetchStatus($data)
    {
        $transactionStatus = strtolower($data['status'] ?? '');

        if ($transactionStatus === 'paid') {
            return PaymentStatus::PAID;
        }

        if ($transactionStatus === 'expired') {
            return PaymentStatus::EXPIRED;
        }
        
        return PaymentStatus::UNPAID;
    }

    private function fetchType($data){
        $paymentType = strtoupper($data['paymentMethod']['type'] ?? '');

        if($paymentType == 'CARD'){
            return PaymentType::CARD;
        } elseif($paymentType == 'VIRTUAL_ACCOUNT') {
            return PaymentType::VIRTUAL_ACCOUNT;
        } elseif($paymentType == 'EWALLET') {
            return PaymentType::E_WALLET;
        } elseif($paymentType == 'QR') {
            return PaymentType::QR_PAYMENT;
        } elseif(in_array($paymentType, ['cstore', 'alfamart', 'indomaret'])) {
            return PaymentType::RETAIL_OUTLET;
        } elseif(in_array($paymentType, ['akulaku', 'kredivo'])) {
            return PaymentType::CARDLESS_CREDIT;
        }

        return null;
    }

    private function makeResponse(?array $request = null, ?int $httpCode = null): ?PaymentResponse
    {
        $statusCode = (int) ($httpCode ?? 400);
        if($statusCode >= 500) {
            throw new RequestServerException('Failed, HTTP Code: ' . $statusCode);
        } elseif($statusCode >= 400) {
            throw new RequestClientException('Failed, HTTP Code: ' . $statusCode);
        } elseif(!in_array($statusCode, [200, 201])) {
            throw new RequestServerException('Failed, HTTP Code: ' . ($statusCode ?? 'Unknown'));
        }

        if(!$request) {
            throw new RequestServerException('Failed, HTTP Code: ' . ($statusCode ?? 'Unknown'));
        }

        $orderId = $request['clientReferenceId'] ?? '';
        $transactionId = $request['id'] ?? '';
        $status = $this->fetchStatus($request);
        $amount = isset($request['amount']['value']) ? (int) $request['amount']['value'] : null;

        if((empty($orderId) && empty($transactionId)) || $status === null) {
            throw new RequestServerException($request['status_message'] ?? 'Failed to fetch payment status: Invalid response from Midtrans API');
        }

        $response = new PaymentResponse($orderId, $transactionId, $status, $amount);
        $response->setType($this->fetchType($request));
        $response->setTime(
            $request['createdAt'] ?? null,
            $request['expiryAt'] ?? null,
            $request['updatedAt'] ?? null,
            empty($request['chargeDetails']) ? null : end($request['chargeDetails'])['paidAt'] ?? null,
        );

        if($request['paymentUrl'] ?? false){
            if(strtolower(substr($request['paymentUrl'], 0, 4)) == 'http'){
                $response->setPaymentUrlWeb($request['paymentUrl']);
            }else{
                $response->setPaymentUrlApp($request['paymentUrl']);
            }
        }

        $charges = $request['chargeDetails'] ?? [];
        if($charges){
            $charge = end($charges);
            if(($charge['virtualAccount']['virtualAccountNumber'] ?? false)){
                $response->setBankTransfer(
                    $charge['virtualAccount']['virtualAccountNumber'] ?? null,
                    null,
                    $charge['virtualAccount']['virtualAccountName'] ?? null
                );
            }
            if(($charge['card']['binInformations']['brand'] ?? false)){
                $response->setCard(
                    $charge['card']['binInformations']['brand'] ?? null,
                    $charge['card']['binInformations']['issuingBank'] ?? null,
                    ($charge['card']['first6'] ?? false) ? $charge['card']['first6'] . 'xxxx' . $charge['card']['last4'] : null,
                    $charge['card']['authorizationCode'] ?? null
                );
            }
            if(($charge['qr']['qrUrl'] ?? false)){
                $response->setQRPaymentLink($charge['qr']['qrUrl']);
            }
            if(($charge['qr']['qrContent'] ?? false)){
                $response->setQRPaymentString($charge['qr']['qrContent']);
            }
            
            // setRetailOutlet tidak digunakan
            // setPaymentUrlApp tidak digunakan
        }

        return $response;
    }
}