<?php

namespace Cloudbadak\PaymentHub\Contracts;

use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Exceptions\UnsupportedPaymentMethodException;

/**
 * Base class yang bisa di-extend oleh setiap gateway driver.
 * Menyediakan method helper untuk mapping canonical enum → kode spesifik gateway.
 *
 * Setiap driver wajib mendefinisikan array mapping-nya sendiri di constructor
 * atau sebagai property.
 */
abstract class AbstractPaymentGateway implements PaymentInterface
{
    /**
     * Mapping dari BankCode kanonikal ke kode spesifik gateway ini.
     * @var array<string, string>
     */
    protected array $bankCodeMap = [];

    /**
     * Mapping dari EWalletCode kanonikal ke kode spesifik gateway ini.
     *
     * @var array<string, string>
     */
    protected array $walletCodeMap = [];

    /**
     * Mapping dari OutletCode kanonikal ke kode spesifik gateway ini.
     *
     * @var array<string, string>
     */
    protected array $outletCodeMap = [];

    /**
    * Mapping dari QRPaymentCode kanonikal ke kode spesifik gateway ini.
    *
    * @var array<string, string>
    */
    protected array $qrPaymentCodeMap = [];

    /**
    * Mapping dari CardlessCreditCode kanonikal ke kode spesifik gateway ini.
    *
    * @var array<string, string>
    */
    protected array $cardlessCreditCodeMap = [];

    /**
     * Terjemahkan BankCode kanonikal ke kode string yang dipakai gateway ini.
     *
     * @throws UnsupportedPaymentMethodException jika bank tidak didukung gateway ini
     */
    protected function resolveBankCode(BankCode $bank): string
    {
        $code = $this->bankCodeMap[$bank->value] ?? null;

        if ($code === null) {
            throw new UnsupportedPaymentMethodException(
                "Bank [{$bank->value}] is not supported by " . static::class
            );
        }

        return $code;
    }

    /**
     * Terjemahkan EWalletCode kanonikal ke kode string yang dipakai gateway ini.
     *
     * @throws UnsupportedPaymentMethodException jika wallet tidak didukung gateway ini
     */
    protected function resolveWalletCode(EWalletCode $wallet): string
    {
        $code = $this->walletCodeMap[$wallet->value] ?? null;

        if ($code === null) {
            throw new UnsupportedPaymentMethodException(
                "E-Wallet [{$wallet->value}] is not supported by " . static::class
            );
        }

        return $code;
    }

    /**
     * Terjemahkan QRPaymentCode kanonikal ke kode string yang dipakai gateway ini.
     *
     * @throws UnsupportedPaymentMethodException jika QR payment tidak didukung gateway ini
     */
    protected function resolveQRPaymentCode(QRPaymentCode $qrPayment): string
    {
        $code = $this->qrPaymentCodeMap[$qrPayment->value] ?? null;

        if ($code === null) {
            throw new UnsupportedPaymentMethodException(
                "QR Payment [{$qrPayment->value}] is not supported by " . static::class
            );
        }

        return $code;
    }

    /**
     * Terjemahkan OutletCode kanonikal ke kode string yang dipakai gateway ini.
     *
     * @throws UnsupportedPaymentMethodException jika outlet tidak didukung gateway ini
     */
    protected function resolveOutletCode(OutletCode $outlet): string
    {
        $code = $this->outletCodeMap[$outlet->value] ?? null;

        if ($code === null) {
            throw new UnsupportedPaymentMethodException(
                "Outlet [{$outlet->value}] is not supported by " . static::class
            );
        }

        return $code;
    }

    /**
     * Terjemahkan CardlessCreditCode kanonikal ke kode string yang dipakai gateway ini.
     *
     * @throws UnsupportedPaymentMethodException jika cardless credit tidak didukung gateway ini
     */
    protected function resolveCardlessCreditCode(CardlessCreditCode $cardlessCredit): string
    {
        $code = $this->cardlessCreditCodeMap[$cardlessCredit->value] ?? null;

        if ($code === null) {
            throw new UnsupportedPaymentMethodException(
                "Cardless Credit [{$cardlessCredit->value}] is not supported by " . static::class
            );
        }

        return $code;
    }
}