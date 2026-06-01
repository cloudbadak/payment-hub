<?php

namespace Cloudbadak\PaymentHub\Enums;

enum PaymentStatus
{
    case UNPAID; // belum dibayar
    case PAID; // sudah dibayar (lunas)
    case EXPIRED; // sudah kadaluarsa
    case FAILED; // gagal
    case REFUND; // sudah dikembalikan
}