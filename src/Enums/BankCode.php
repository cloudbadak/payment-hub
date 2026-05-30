<?php

namespace Cloudbadak\PaymentHub\Enums;

/**
 * Canonical bank codes used across all payment gateways.
 * Each gateway driver is responsible for mapping these to its own specific codes.
 */
enum BankCode: string
{
    case MANDIRI    = 'MANDIRI';
    case BCA        = 'BCA';
    case BNI        = 'BNI';
    case BRI        = 'BRI';
    case PERMATA    = 'PERMATA';
    case CIMB       = 'CIMB';
    case DANAMON    = 'DANAMON';
    case BSI        = 'BSI';
    case BTN        = 'BTN';
    case MAYBANK    = 'MAYBANK';
    case MUAMALAT   = 'MUAMALAT';
    case SAHABAT    = 'SAHABAT';
    case SEABANK    = 'SEABANK';
    case SAQU       = 'SAQU';
}
