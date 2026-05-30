<?php

namespace Cloudbadak\PaymentHub\Enums;

/**
 * Canonical e-wallet codes used across all payment gateways.
 * Each gateway driver is responsible for mapping these to its own specific codes.
 */
enum EWalletCode: string
{
    case GOPAY      = 'GOPAY';
    case OVO        = 'OVO';
    case DANA       = 'DANA';
    case SHOPEEPAY  = 'SHOPEEPAY';
    case LINKAJA    = 'LINKAJA';
    case SAKUKU     = 'SAKUKU';
    case JENIUSPAY  = 'JENIUSPAY';
    case ASTRAPAY   = 'ASTRAPAY';
}
