<?php

namespace Cloudbadak\PaymentHub\Data;

use Cloudbadak\PaymentHub\Data\PaymentStatus;

class PaymentResponse
{
    public string $orderId;
    public string $paymentId;
    public PaymentStatus $status;
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
}