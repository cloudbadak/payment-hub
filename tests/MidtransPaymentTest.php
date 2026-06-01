<?php

namespace Cloudbadak\PaymentHub\Tests;

use PHPUnit\Framework\TestCase;
use Cloudbadak\PaymentHub\PaymentHub;
use Cloudbadak\PaymentHub\Driver\ApiRequest;
use Cloudbadak\PaymentHub\Providers\MidtransPayment;
use Cloudbadak\PaymentHub\Data\Customer;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;
use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\CardlessCreditCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Enums\PaymentStatus;

class MidtransPaymentTest extends TestCase
{

    public function test_get_balance(): void
    {
        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('balance')->willReturn("100000");
        $service = new PaymentHub($curlMock);

        $result = $service->balance();

        $this->assertEquals("100000", $result);
    }

    public function test_get_transaction(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('get')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $result = $service->get("1466323342");

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_virtual_account(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('payWithVirtualAccount')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $paymentRequest = new PaymentRequest("1466323342", 20000, new Customer("John Doe", "john.doe@example.com", "081234567890"));
        $paymentRequest->setBank(BankCode::BRI);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_wallet(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('payWithEWallet')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $paymentRequest = new PaymentRequest("1466323342", 20000, new Customer("John Doe", "john.doe@example.com", "081234567890"));
        $paymentRequest->setWallet(EWalletCode::GOPAY);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_card(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('payWithCard')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $paymentRequest = new PaymentRequest("1466323342", 20000, new Customer("John Doe", "john.doe@example.com", "081234567890"));
        $paymentRequest->setCardTokenId("card_token_id");
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_cardless_credit(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('payWithCardlessCredit')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $paymentRequest = new PaymentRequest("1466323342", 20000, new Customer("John Doe", "john.doe@example.com", "081234567890"));
        $paymentRequest->setCardlessCredit(CardlessCreditCode::AKULAKU);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_outlet(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('payWithOutlet')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $paymentRequest = new PaymentRequest("1466323342", 20000, new Customer("John Doe", "john.doe@example.com", "081234567890"));
        $paymentRequest->setOutlet(OutletCode::ALFAMART);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_qr_payment(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::UNPAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('payWithQRPayment')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $paymentRequest = new PaymentRequest("1466323342", 20000, new Customer("John Doe", "john.doe@example.com", "081234567890"));
        $paymentRequest->setQRPayment(QRPaymentCode::QRIS);
        $result = $service->charge($paymentRequest);

        $this->assertEquals(PaymentStatus::UNPAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }

    public function test_webhook(): void
    {
        $fakeResponse = new PaymentResponse("1466323342", "pay_1234567890", PaymentStatus::PAID, 20000);

        $curlMock = $this->createMock(MidtransPayment::class);
        $curlMock->method('webhook')->willReturn($fakeResponse);
        $service = new PaymentHub($curlMock);

        $result = $service->webhook();

        $this->assertEquals(PaymentStatus::PAID, $result->status);
        $this->assertEquals('1466323342', $result->orderId);
    }
}