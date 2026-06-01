<?php

namespace Cloudbadak\PaymentHub\Enums;

/**
 * Canonical e-wallet codes used across all payment gateways.
 * Each gateway driver is responsible for mapping these to its own specific codes.
 */
enum CardlessCreditCode: string
{
    case AKULAKU    = 'AKULAKU';
    case KREDIVO    = 'KREDIVO';
    case HOME_CREDIT = 'HOME_CREDIT';
    case BRI_CREDIT = 'BRI_CREDIT';
}
