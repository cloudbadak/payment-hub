<?php
namespace Cloudbadak\PaymentHub\Driver;
class Security
{
    private const CIPHER = 'aes-256-gcm';
    private const TAG_LENGTH = 16; // Standar panjang tag GCM (128 bit / 16 bytes)

    /**
     * Mengenkripsi data menggunakan AES-256-GCM
     */
    public static function encrypt(string $plaintext, string $key): string
    {
        // 1. Kunci harus persis 32 bytes (256-bit)
        $keyBin = hash('sha256', $key, true);

        // 2. Buat IV (Initialization Vector) acak sepanjang 12 bytes (standar rekomendasi NIST untuk GCM)
        $ivLength = openssl_cipher_iv_length(self::CIPHER); // 12 bytes
        $iv = openssl_random_pseudo_bytes($ivLength);

        // 3. Eksekusi Enkripsi
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $keyBin,
            OPENSSL_RAW_DATA,
            $iv,
            $tag, // Tag dipassing via reference dan akan diisi oleh OpenSSL
            '',   // Additional Authenticated Data (AAD) jika ada
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new Exception("Enkripsi gagal.");
        }

        // 4. Gabungkan IV + Tag + Ciphertext menjadi satu string base64 agar mudah disimpan
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Mendekripsi data terenkripsi AES-256-GCM
     */
    public static function decrypt(string $base64Payload, string $key): string
    {
        $keyBin = hash('sha256', $key, true);
        $decoded = base64_decode($base64Payload, true);

        if ($decoded === false) {
            throw new Exception("Payload base64 tidak valid.");
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER); // 12 bytes

        // Ekstrak IV, Tag, dan Ciphertext dari payload
        $iv = substr($decoded, 0, $ivLength);
        $tag = substr($decoded, $ivLength, self::TAG_LENGTH);
        $ciphertext = substr($decoded, $ivLength + self::TAG_LENGTH);

        // Eksekusi Dekripsi
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $keyBin,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            // Jika tag tidak cocok atau data telah dimodifikasi, fungsi akan melempar error
            throw new Exception("Dekripsi gagal! Data dimodifikasi atau kunci salah.");
        }

        return $plaintext;
    }
}