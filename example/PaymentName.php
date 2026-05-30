<?php

use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Contracts\AbstractPaymentGateway;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;

class PaymentName extends AbstractPaymentGateway
{
    public function __construct()
    {
        $this->bankCodeMap = [
            BankCode::MANDIRI->value => 'echannel',
            BankCode::BCA->value     => 'bca',
            BankCode::BNI->value     => 'bni',
        ];
    }

    public function payWithVirtualAccount(PaymentRequest $request): PaymentResponse
    {
        $bankCode = $this->resolveBankCode($request->getBank());
        // ... kirim ke API dengan $bankCode
    }
}