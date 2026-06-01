<?php

namespace Cloudbadak\PaymentHub\Data;

use Cloudbadak\PaymentHub\Enums\PaymentStatus;
use Cloudbadak\PaymentHub\Enums\PaymentType;

class PaymentResponse
{
    public string $orderId;
    public string $paymentId;
    public PaymentStatus $status;
    public PaymentType $type;
    public ?int $amount = null;

    // For bank transfer
    public ?string $billerName = null; // atas nama penerima pembayaran
    public ?string $billerCode = null; // kode bank atau kode outlet
    public ?string $virtualAccountNumber = null; // nomor rekening virtual account

    // For QR Payment or e-wallet qrcode
    public ?string $qrCodeUrl = null; // URL gambar untuk menampilkan QR code
    public ?string $qrCodeString = null; // String yang harus dijadikan QR code

    // For retail outlet
    public ?string $paymentCode = null; // kode pembayaran yang harus ditunjukkan ke kasir

    // For e-wallet deep linking
    public ?string $paymentWebUrl = null; // URL for web-based payment
    public ?string $paymentAppUrl = null; // URL for app-based payment (deep link)

    // For card payment
    public ?string $cardType = null;  // credit or debit
    public ?string $cardBrand = null; // The name of the Acquiring Bank
    public ?string $cardMasked = null; // First 8-digits and last 4-digits of customer's card number
    public ?string $cardApprovalCode = null; // Approval code. It can be used for refund

    public ?string $createdAt = null;
    public ?string $paidAt = null;
    public ?string $expiredAt = null;
    public ?string $updatedAt = null;

    public function __construct(string $orderId, string $paymentId, PaymentStatus $status, ?int $amount = null)
    {
        $this->orderId = $orderId;
        $this->paymentId = $paymentId;
        $this->status = $status;
        $this->amount = $amount;
    }

    public function setType(PaymentType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setBankTransfer(string $virtualAccountNumber, string $billerCode, string $billerName): self
    {
        $this->virtualAccountNumber = $virtualAccountNumber;
        $this->billerCode = $billerCode;
        $this->billerName = $billerName;
        return $this;
    }

    public function setQRPaymentLink(string $qrCodeUrl): self
    {
        $this->qrCodeUrl = $qrCodeUrl;
        return $this;
    }

    public function setQRPaymentString(string $qrCodeString): self
    {
        $this->qrCodeString = $qrCodeString;
        return $this;
    }

    public function setRetailOutlet(string $paymentCode): self
    {
        $this->paymentCode = $paymentCode;
        return $this;
    }

    public function setPaymentUrlWeb(string $paymentWebUrl): self
    {
        $this->paymentWebUrl = $paymentWebUrl;
        return $this;
    }

    public function setPaymentUrlApp(string $paymentAppUrl): self
    {
        $this->paymentAppUrl = $paymentAppUrl;
        return $this;
    }

    public function setCard(?string $cardType = null, ?string $cardBrand = null, ?string $cardMasked = null, ?string $cardApprovalCode = null): self
    {
        $this->cardType = $cardType;
        $this->cardBrand = $cardBrand;
        $this->cardMasked = $cardMasked;
        $this->cardApprovalCode = $cardApprovalCode;
        return $this;
    }

    public function setTime(?string $createdAt = null, ?string $expiredAt = null, ?string $updatedAt = null, ?string $paidAt = null): self
    {
        $this->createdAt = $createdAt ? date('Y-m-d H:i:s', strtotime($createdAt)) : null;
        $this->expiredAt = $expiredAt ? date('Y-m-d H:i:s', strtotime($expiredAt)) : null;
        $this->updatedAt = $updatedAt ? date('Y-m-d H:i:s', strtotime($updatedAt)) : null;
        $this->paidAt = $paidAt ? date('Y-m-d H:i:s', strtotime($paidAt)) : null;
        return $this;
    }
}