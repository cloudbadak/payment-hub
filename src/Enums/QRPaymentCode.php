<?php

namespace Cloudbadak\PaymentHub\Enums;

/**
 * Canonical QR payment codes used across all payment gateways.
 * Each gateway driver is responsible for mapping these to its own specific codes.
 */
enum QRPaymentCode: string
{
    case QRIS = 'QRIS';
}
