<?php

namespace Cloudbadak\PaymentHub\Tests;

use PHPUnit\Framework\TestCase;
use Cloudbadak\PaymentHub\PaymentHub;
use Cloudbadak\PaymentHub\Driver\ApiRequest;
use Cloudbadak\PaymentHub\Providers\PivotPayment;
use Cloudbadak\PaymentHub\Data\Customer;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;
use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\CardlessCreditCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Enums\PaymentStatus;
use Cloudbadak\PaymentHub\Driver\CacheManager;

putenv("PIVOT_ENVIRONMENT=development");
putenv("PIVOT_ID=");
putenv("PIVOT_SECRET=");
putenv("PIVOT_WEBHOOK=");

class PivotPaymentTest extends TestCase
{
    protected $cacheConfig = [
        'default' => 'file',
        'drivers' => [
            'file' => [
                'type' => 'file',
                'path' => '/var/cache/payment-hub',  // Custom path
                'extension' => '.cache'
            ]
        ]
    ];

    public function test_token(): void
    {
        $this->markTestSkipped('Lulus testing!');

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));

        $service = new PaymentHub($gateway);
        $token = $service->token();
        $this->assertEquals('Bearer', explode(' ', $token ?? '')[0] ?? '');
    }

    public function test_balance(): void
    {
        $this->markTestSkipped('Lulus testing!');

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));

        $service = new PaymentHub($gateway);
        $balance = $service->balance();
        $this->assertIsNumeric($balance, 'Balance harus berupa angka yang valid');
        $this->assertGreaterThanOrEqual(0, (float) $balance, 'Balance tidak boleh minus');
    }

    public function test_pay_va(): void 
    {
        $this->markTestSkipped('Lulus testing!');

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));
        $service = new PaymentHub($gateway);

        $id = uniqid();
        $customer = new Customer("1", "John", "Doe", "john.doe@example.com", "081234567890");
        $paymentRequest = new PaymentRequest($id, 20000, $customer);
        $paymentRequest->setBank(BankCode::BRI);
        $paymentRequest->setReturnUrl("https://testing.example.com/payment/1466323342");
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals($id, $result->orderId);
        $this->assertIsString($result->virtualAccountNumber, 'VA harus berupa string');
        $this->assertNotEmpty($result->virtualAccountNumber, 'VA tidak boleh kosong');
    }

    public function test_pay_wallet(): void 
    {
        $this->markTestSkipped('Lulus testing!');

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));
        $service = new PaymentHub($gateway);

        $id = uniqid();
        $customer = new Customer("1", "John", "Doe", "john.doe@example.com", "081234567890");
        $paymentRequest = new PaymentRequest($id, 20000, $customer);
        $paymentRequest->setReturnUrl("https://testing.example.com/payment/1466323342");
        $paymentRequest->setWallet(EWalletCode::DANA);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals($id, $result->orderId);
        $this->assertIsString($result->paymentWebUrl, 'URL Wallet harus berupa string');
        $this->assertNotEmpty($result->paymentWebUrl, 'URL Wallet tidak boleh kosong');
    }

    public function test_pay_qr(): void 
    {
        $this->markTestIncomplete('Belum live testing!');

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));
        $service = new PaymentHub($gateway);

        $id = uniqid();
        $customer = new Customer("1", "John", "Doe", "john.doe@example.com", "081234567890");
        $paymentRequest = new PaymentRequest($id, 20000, $customer);
        $paymentRequest->setReturnUrl("https://testing.example.com/payment/1466323342");
        $paymentRequest->setQRPayment(QRPaymentCode::QRIS);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals($id, $result->orderId);
        $this->assertIsString($result->qrCodeUrl, 'URL QR harus berupa string');
        $this->assertNotEmpty($result->qrCodeUrl, 'URL QR tidak boleh kosong');
    }

    public function test_pay_card(): void 
    {
        $this->markTestSkipped('Lulus testing!');

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));
        $service = new PaymentHub($gateway);

        $id = uniqid();
        $customer = new Customer("1", "John", "Doe", "john.doe@example.com", "081234567890");
        $paymentRequest = new PaymentRequest($id, 20000, $customer);
        $paymentRequest->setReturnUrl("https://testing.example.com/payment/1466323342");
        $paymentRequest->setCardTokenId("card_token_id");
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals($id, $result->orderId);
        $this->assertIsString($result->paymentWebUrl, 'URL Card harus berupa string');
        $this->assertNotEmpty($result->paymentWebUrl, 'URL Card tidak boleh kosong');
    }

    public function test_webhook(): void 
    {
        $this->markTestSkipped('Lulus testing!');
        $_SERVER['HTTP_X_API_KEY'] = "GVb5GSocdHUMJVUuLZSCEO3nJbl7xaQij7mxMnEt";

        $cacheManager = new CacheManager($this->cacheConfig);
        $gateway = new PivotPayment($cacheManager->driver('file'));
        $service = new PaymentHub($gateway);

        $result = $service->webhook('{
            "event": "PAYMENT.PAID",
            "data": {
                "id": "bca0b57a-391e-4f6f-bfef-8cd37492ee5b",
                "clientReferenceId": "1750758552",
                "amount": {
                "value": 10001,
                "currency": "IDR"
                },
                "status": "PAID",
                "investigationStatus": null,
                "createdAt": "2025-06-24T09:49:13Z",
                "updatedAt": "2025-06-24T09:56:40.020691396Z",
                "expiryAt": "2025-12-30T23:59:00Z",
                "paymentUrl": "https://pay-stg.pivot-payment.com/detail"}}');

        $this->assertEquals(PaymentStatus::PAID, $result->status);
        $this->assertEquals('1750758552', $result->orderId);
    }
}