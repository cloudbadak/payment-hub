<?php

namespace Cloudbadak\PaymentHub\Enums;

enum PaymentType: string
{
    case CARD = 'card';
    case E_WALLET = 'e-wallet';
    case VIRTUAL_ACCOUNT = 'virtual-account';
    case QR_PAYMENT = 'qr-payment';
    case RETAIL_OUTLET = 'retail-outlet';
    case CARDLESS_CREDIT = 'cardless-credit';
}