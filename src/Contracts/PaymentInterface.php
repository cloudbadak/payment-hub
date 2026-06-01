<?php

namespace Cloudbadak\PaymentHub\Contracts;

use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;

interface PaymentInterface
{
    public function payWithVirtualAccount(PaymentRequest $request): PaymentResponse;
    public function payWithEWallet(PaymentRequest $request): PaymentResponse;
    public function payWithCard(PaymentRequest $request): PaymentResponse;
    public function payWithQRPayment(PaymentRequest $request): PaymentResponse;
    public function payWithOutlet(PaymentRequest $request): PaymentResponse;
    public function payWithCardlessCredit(PaymentRequest $request): PaymentResponse;

    public function get(string $orderId): PaymentResponse;
    public function balance(): string;

    public function webhook(?string $payload = null): PaymentResponse;
}
