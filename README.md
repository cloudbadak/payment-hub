# Payment Hub

Hubungkan ke payment gateway pilihan dengan berbagai kemudahan dalam instalasi dan switch ke payment gateway lain dikemudian hari.

## Penyedia Pembayaran yang Didukung

Saat ini pustaka hanya mendukung integrasi ke beberapa penyedia pembayaran. Jika Anda ingin turut serta mengembangkan pustaka dengan menambah dukungan untuk penyedia pembayaran lain silakan ajukan permintaan. Penyedia pembayaran yang didukung bisa bertambah dikemudian hari.

Penyedia pembayaran yang didukung saat ini:

- Midtrans (PaymentMidtrans)

## Instalasi

```bash
composer require cloudbadak/payment-hub
```

Install juga sub-library sesuai dengan payment gateway yang Anda gunakan (bisa salah satu atau beberapa atau semuanya sesuai kebutuhan)

```bash
composer require cloudbadak/payment-midtrans
```

## Cek Saldo

Gunakan perintah ini untuk mengambil data saldo dari vendor yang dipilih (tidak semua penyedia pembayaran mendukung fitur cek saldo).

Berikut penyedia pembayaran yang mendukung cek saldo:

- iPaymu (PaymentIpaymu)
- Xendit (PaymentXendit)

```php
use Cloudbadak\PaymentHub\PaymentHub;

$paymentHub = new PaymentHub(new PaymentMidtrans());
$balance = (string) $paymentHub->balance();
```

## Menerima Pembayaran

Gunakan kelas penyedia pembayaran pada konstruktor `PaymentHub`, misalkan `PaymentMidtrans`. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
use Cloudbadak\PaymentHub\PaymentHub;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;

use Cloudbadak\PaymentHubMidtrans\PaymentMidtrans;
```

```php
$amount = 100000;
$orderId = "[unique_id]";

// Contoh: va dengan midtrans
$order = new PaymentRequest($orderId, $amount);
$order->setBank(BankCode::MANDIRI);

$paymentHub = new PaymentHub(new PaymentMidtrans());
$response = $paymentHub->charge($order);
```

### Metode pembayaran yang didukung

Transfer bank atau virtual account bank.
Berisi enum dari `Cloudbadak\PaymentHub\Enums\BankCode` yang di dukung.

```php
$order->setBank(BankCode::MANDIRI);
```

E-wallet seperti (Gopay, Shopeepay, OVO, dan Dana).
Berisi enum dari `Cloudbadak\PaymentHub\Enums\EWalletCode` yang di dukung.

```php
$order->setWallet(EWalletCode::GOPAY);
```

QRIS (QR Payment).
Berisi enum dari `Cloudbadak\PaymentHub\Enums\QRPaymentCode` yang di dukung.

```php
$order->setQRPayment(QRPaymentCode::QRIS);
```

Retail Outlet (Indomaret, Alfamart).
Berisi enum dari `Cloudbadak\PaymentHub\Enums\OutletCode` yang di dukung.

```php
$order->setOutlet(OutletCode::ALFAMART);
```

Card Payment (Visa, Mastercard).
Berisi `token_id` dari proses inquiry menggunakan SDK frontend dari penyedia pembayaran yang dipilih.

```php
$order->setCardTokenId("[token_id]");
```

## Cek Transaksi

Gunakan ini untuk mengambil data transaksi. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
$paymentHub = new PaymentHub(new PaymentMidtrans());
$response = $paymentHub->get('[order_id]');
```

## Ambil dan Validasi Data Webhook

Gunakan ini untuk memproses, validasi, dan mengambil data dari webhook. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
$paymentHub = new PaymentHub(new PaymentMidtrans());
$response = $paymentHub->webhook();
```
