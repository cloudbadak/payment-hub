<?php

namespace Cloudbadak\PaymentHub\Data;

enum PaymentStatus
{
    case UNPAID; // belum dibayar
    case PAID; // sudah dibayar (lunas)
    case EXPIRED; // sudah kadaluarsa
    case FAILED; // gagal
    case REFUNDED; // sudah dikembalikan
}