# Payment Hub

Hubungkan ke payment gateway pilihan dengan berbagai kemudahan dalam instalasi dan switch ke payment gateway lain dikemudian hari.

## Penyedia Pembayaran yang Didukung

Saat ini pustaka hanya mendukung integrasi ke beberapa penyedia pembayaran. Jika Anda ingin turut serta mengembangkan pustaka dengan menambah dukungan untuk penyedia pembayaran lain silakan ajukan permintaan. Penyedia pembayaran yang didukung bisa bertambah dikemudian hari.

Penyedia pembayaran yang didukung saat ini:

- Midtrans (MidtransPayment)

## Instalasi

```bash
composer require cloudbadak/payment-hub
```

## Cek Saldo

Gunakan perintah ini untuk mengambil data saldo dari vendor yang dipilih (tidak semua penyedia pembayaran mendukung fitur cek saldo).

Berikut penyedia pembayaran yang mendukung cek saldo:

- iPaymu (IpaymuPayment)
- Xendit (XenditPayment)

```php
use Cloudbadak\PaymentHub\PaymentHub;
use Cloudbadak\PaymentHub\Providers\IpaymuPayment; // gunakan sesuai payment provider yang dipakai

$paymentHub = new PaymentHub(new IpaymuPayment());
$balance = (string) $paymentHub->balance();
```

## Menerima Pembayaran

Gunakan kelas penyedia pembayaran pada konstruktor `PaymentHub`, misalkan `MidtransPayment`. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

1. Inisialisasi kelas yang dibutuhkan

```php
use Cloudbadak\PaymentHub\PaymentHub;
use Cloudbadak\PaymentHub\Data\PaymentRequest;
use Cloudbadak\PaymentHub\Data\PaymentResponse;

// gunakan sesuai payment provider yang dipakai
use Cloudbadak\PaymentHub\Providers\MidtransPayment;
```

2. Membuat objek payment request

```php
$orderId = "[unique_id]";
$amount = 100000;

$order = new PaymentRequest($orderId, $amount);
```

3. Memilih metode pembayaran

```php
use Cloudbadak\PaymentHub\Enums\BankCode;
use Cloudbadak\PaymentHub\Enums\EWalletCode;
use Cloudbadak\PaymentHub\Enums\OutletCode;
use Cloudbadak\PaymentHub\Enums\QRPaymentCode;
use Cloudbadak\PaymentHub\Enums\CardlessCreditCode;

// jika pakai virtual_account
$order->setBank(BankCode::MANDIRI);

// jika pakai e-wallet
$order->setWallet(EWalletCode::OVO);

// jika pakai outlet
$order->setOutlet(OutletCode::ALFAMART);

// jika pakai qris
$order->setQRPayment(QRPaymentCode::QRIS);

// jika pakai credit card
$order->setCardTokenId("token_id");

// jika pakai pay later
$order->setCardlessCredit(CardlessCreditCode::AKULAKU);
```

4. Menambahkan data customer (opsional)

```php
use Cloudbadak\PaymentHub\Data\Customer;

$customer = new Customer(
    "cust_id",
    "Nama Depan"
    "Nama Belakang",
    "email@example.com",
    "08xxx"
);
$order->setCustomer($customer);
```

5. Menambahkan data items (opsional)

```php
use Cloudbadak\PaymentHub\Data\Item;

$items = [
    new Item("id", "Nama Produk 1", "Deskripsi 1", 1, 100000),
    new Item("id", "Nama Produk 2", "Deskripsi 2", 2, 50000),
]
$order->setItems($items);
```

6. Menambahkan data seller (opsional)

```php
use Cloudbadak\PaymentHub\Data\Seller;

$seller = new Seller("id", "Nama Toko", "email@example.com", "08xxx");
$order->setSeller($seller);
```

7. Melakukan request pembayaran

```php
$paymentHub = new PaymentHub(new MidtransPayment());
$response = $paymentHub->charge($order);

```

## Cek Transaksi

Gunakan ini untuk mengambil data transaksi. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
$paymentHub = new PaymentHub(new MidtransPayment());
$response = $paymentHub->get('[order_id]');
```

## Ambil dan Validasi Data Webhook

Gunakan ini untuk memproses, validasi, dan mengambil data dari webhook. Semua metode pembayaran mengembalikan data objek `Cloudbadak\PaymentHub\Data\PaymentResponse`.

```php
$paymentHub = new PaymentHub(new MidtransPayment());
$response = $paymentHub->webhook();
```

## ENVIRONMENT (development dan production)

ENVIRONMENT yang tersedia di pustaka ini hanya `development` dan `production`. Silakan atur pada konfigurasi `*_ENVIRONMENT`.

1. Midtrans

```bash
MIDTRANS_ENVIRONMENT = development
MIDTRANS_SERVER_KEY = server_key
MIDTRANS_CLIENT_KEY = client_key
```
