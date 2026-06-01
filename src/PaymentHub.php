<?php

namespace Cloudbadak\PaymentHub;

use Cloudbadak\PaymentHub\Contracts\PaymentInterface;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;
use Cloudbadak\PaymentHub\Exceptions\UnsupportedPaymentMethodException;

class PaymentHub implements PaymentInterface
{
    protected PaymentInterface $gateway;
    
    public function __construct(PaymentInterface $paymentGateway)
    {
        $this->gateway = $paymentGateway;
    }

    public function charge(PaymentRequest $request): PaymentResponse
    {
        if($request->getBank()) {
            return $this->payWithVirtualAccount($request);
        } elseif($request->getWallet()) {
            return $this->payWithEWallet($request);
        } elseif($request->getOutlet()) {
            return $this->payWithOutlet($request);
        } elseif($request->getQRPayment()) {
            return $this->payWithQRPayment($request);
        } elseif($request->getCardTokenId()) {
            return $this->payWithCard($request);
        } elseif($request->getCardlessCredit()) {
            return $this->payWithCardlessCredit($request);
        } else {
            throw new UnsupportedPaymentMethodException("No payment method specified in request");
        }
    }

    public function payWithVirtualAccount(PaymentRequest $request): PaymentResponse
    {
        return $this->gateway->payWithVirtualAccount($request);
    }

    public function payWithEWallet(PaymentRequest $request): PaymentResponse
    {
        return $this->gateway->payWithEWallet($request);
    }

    public function payWithCard(PaymentRequest $request): PaymentResponse
    {
        return $this->gateway->payWithCard($request);
    }

    public function payWithQRPayment(PaymentRequest $request): PaymentResponse
    {
        return $this->gateway->payWithQRPayment($request);
    }

    public function payWithOutlet(PaymentRequest $request): PaymentResponse
    {
        return $this->gateway->payWithOutlet($request);
    }

    public function payWithCardlessCredit(PaymentRequest $request): PaymentResponse
    {
        return $this->gateway->payWithCardlessCredit($request);
    }

    public function get(string $orderId): PaymentResponse
    {
        return $this->gateway->get($orderId);
    }

    public function balance(): string
    {
        return $this->gateway->balance();
    }

    public function webhook(?string $payload = null): PaymentResponse
    {
        return $this->gateway->webhook($payload);
    }
}