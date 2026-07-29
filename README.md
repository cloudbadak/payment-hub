# Payment Hub

Hubungkan ke payment gateway pilihan dengan berbagai kemudahan dalam instalasi dan switch ke payment gateway lain dikemudian hari.

## Penyedia Pembayaran yang Didukung

Saat ini pustaka hanya mendukung integrasi ke beberapa penyedia pembayaran. Jika Anda ingin turut serta mengembangkan pustaka dengan menambah dukungan untuk penyedia pembayaran lain silakan ajukan permintaan. Penyedia pembayaran yang didukung bisa bertambah dikemudian hari.

Penyedia pembayaran yang didukung saat ini:

- PivotPayment (Support)
- MidtransPayment (Masih dalam pengembangan)

## Instalasi

```bash
composer require cloudbadak/payment-hub
```

## Persiapan

Setiap payment provider memiliki karakteristik yang berbeda, namun di library ini saya sediakan dalam format yang hampir sama. Berikut adalah persiapan setiap payment provider yang tersedia disini:

```php
// Wajib
use Cloudbadak\PaymentHub\PaymentHub;
use Cloudbadak\PaymentHub\Driver\CacheManager;

// Pivot Payment
use Cloudbadak\PaymentHub\Providers\PivotPayment;
$cacheManager = new CacheManager($cacheConfig); // konfigurasi cache ada di bawah
$pivotPayment = new PivotPayment($cacheManager->driver('file'));
$paymentHub = new PaymentHub($pivotPayment);

// Midtrans Payment
use Cloudbadak\PaymentHub\Providers\MidtransPayment;
$midtransPayment = new MidtransPayment();
$paymentHub = new PaymentHub($midtransPayment);

```

## Cek Saldo

Gunakan perintah ini untuk mengambil data saldo dari vendor yang dipilih (tidak semua penyedia pembayaran mendukung fitur cek saldo).

Berikut penyedia pembayaran yang mendukung cek saldo:

- PivotPayment

```php
$balance = $paymentHub->balance();
```

## Menerima Pembayaran

Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

1. Membuat objek payment request

```php
use Cloudbadak\PaymentHub\Data\Customer;
use Cloudbadak\PaymentHub\Data\PaymentRequest;

$customer = new Customer("1", "John", "Doe", "john.doe@example.com", "081234567890");
$paymentRequest = new PaymentRequest("[order-id]", 20000, $customer);
$paymentRequest->setReturnUrl("https://testing.example.com/payment/1466323342");
```

2. Memilih metode pembayaran

```php
use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Enums\CardlessCreditCode;

// jika pakai virtual_account
$paymentRequest->setBank(BankCode::MANDIRI);

// jika pakai e-wallet
$paymentRequest->setWallet(EWalletCode::DANA);

// jika pakai outlet
$paymentRequest->setOutlet(OutletCode::ALFAMART);

// jika pakai qris
$paymentRequest->setQRPayment(QRPaymentCode::QRIS);

// jika pakai credit card
$paymentRequest->setCardTokenId("token_id");

// jika pakai pay later
$paymentRequest->setCardlessCredit(CardlessCreditCode::AKULAKU);
```

3. Menambahkan data items (opsional)

```php
use Cloudbadak\PaymentHub\Data\Item;

$items = [
    new Item("id", "Nama Produk 1", "Deskripsi 1", 1, 100000),
    new Item("id", "Nama Produk 2", "Deskripsi 2", 2, 50000),
]
$paymentRequest->setItems($items);
```

4. Menambahkan data seller (opsional)

```php
use Cloudbadak\PaymentHub\Data\Seller;

$seller = new Seller("id", "Nama Toko", "email@example.com", "08xxx");
$paymentRequest->setSeller($seller);
```

5. Melakukan request pembayaran

Mengembalikan objek `Cloudbadak\PaymentHub\Data\PaymentResponse`

```php
$response = $paymentHub->charge($order);

```

## Cek Transaksi

Gunakan ini untuk mengambil data transaksi. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
$response = $paymentHub->get('[order_id]');
```

## Ambil dan Validasi Data Webhook

Gunakan ini untuk memproses, validasi, dan mengambil data dari webhook. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
// jika mengambil payload secara otomatis
$response = $paymentHub->webhook();

// jika mengambil payload manual
$payload = '{}'; // lakukan fungsi pengambilan payload
$response = $paymentHub->webhook($payload);
```

## ENVIRONMENT (development dan production)

ENVIRONMENT yang tersedia di pustaka ini hanya `development` dan `production`. Silakan atur pada konfigurasi `*_ENVIRONMENT`.

1. PivotPayment

```bash
PIVOT_ENVIRONMENT = development
PIVOT_ID =
PIVOT_SECRET =
PIVOT_WEBHOOK =
```

2. MidtransPayment

```bash
MIDTRANS_ENVIRONMENT = development
MIDTRANS_SERVER_KEY =
MIDTRANS_CLIENT_KEY =
```
