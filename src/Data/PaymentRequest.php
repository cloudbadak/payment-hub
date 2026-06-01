<?php

namespace Cloudbadak\PaymentHub\Data;

use Cloudbadak\PaymentHub\Data\Customer;
use Cloudbadak\PaymentHub\Data\Seller;
use Cloudbadak\PaymentHub\Data\Item;
use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Enums\CardlessCreditCode;

class PaymentRequest
{
    public string $orderId;
    public int $amount;
    public ?Customer $customer = null;
    public ?Seller $seller = null;
    public ?array $items = [];

    /** Digunakan oleh payWithVirtualAccount() */
    public ?BankCode $bank = null;

    /** Digunakan oleh payWithEWallet() */
    public ?EWalletCode $wallet = null;

    /** Digunakan oleh payWithOutlet() */
    public ?OutletCode $outlet = null;

    /** Digunakan oleh payWithQRPayment() */
    public ?QRPaymentCode $qrPayment = null;

    /** Digunakan oleh payWithCard() */
    public ?string $cardTokenId = null;

    /** Digunakan oleh payWithCardlessCredit() */
    public ?CardlessCreditCode $cardlessCredit = null;

    public function __construct(string $orderId, int $amount, ?Customer $customer = null, ?array $items = []){
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->customer = $customer;
        $this->items = $items ?? [];
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function getBank(): ?BankCode
    {
        return $this->bank;
    }

    public function getWallet(): ?EWalletCode
    {
        return $this->wallet;
    }

    public function getOutlet(): ?OutletCode
    {
        return $this->outlet;
    }

    public function getQRPayment(): ?QRPaymentCode
    {
        return $this->qrPayment;
    }

    public function getCardlessCredit(): ?CardlessCreditCode
    {
        return $this->cardlessCredit;
    }

    public function getCardTokenId(): ?string
    {
        return $this->cardTokenId;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setBank(?BankCode $bank): void
    {
        $this->bank = $bank;
    }

    public function setWallet(?EWalletCode $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function setOutlet(?OutletCode $outlet): void
    {
        $this->outlet = $outlet;
    }

    public function setQRPayment(?QRPaymentCode $qrPayment): void
    {
        $this->qrPayment = $qrPayment;
    }

    public function setCardTokenId(?string $cardTokenId): void
    {
        $this->cardTokenId = $cardTokenId;
    }

    public function setCardlessCredit(?CardlessCreditCode $cardlessCredit): void
    {
        $this->cardlessCredit = $cardlessCredit;
    }

    public function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

    public function setSeller(?Seller $seller): void
    {
        $this->seller = $seller;
    }
}