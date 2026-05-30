<?php

namespace Cloudbadak\PaymentHub\Enums;

/**
 * Canonical retail outlet codes used across all payment gateways.
 * Each gateway driver is responsible for mapping these to its own specific codes.
 */
enum OutletCode: string
{
    case ALFAMART   = 'ALFAMART';
    case INDOMARET  = 'INDOMARET';
}
