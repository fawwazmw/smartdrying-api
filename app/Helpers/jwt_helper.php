<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services; // Untuk base_url jika diperlukan
use CodeIgniter\Exceptions\PageNotFoundException; // Contoh exception jika key tidak ada

/**
 * Membuat JSON Web Token (JWT).
 *
 * @param int    $userId  ID pengguna.
 * @param string $email   Email pengguna.
 * @param array  $additionalData Data tambahan untuk dimasukkan ke payload (opsional).
 * @return string Token JWT yang dihasilkan.
 * @throws \Exception Jika JWT_SECRET tidak dikonfigurasi.
 */
function createJWT(int $userId, string $email, array $additionalData = []): string
{
    // 1. Ambil secret key dari .env (LEBIH AMAN!)
    $key = getenv('JWT_SECRET');
    if (empty($key)) {
        // Catat error atau lempar exception jika key tidak ada
        log_message('critical', 'JWT_SECRET tidak disetel di file .env.');
        // Anda bisa memilih untuk melempar exception agar lebih jelas saat development
        throw new \RuntimeException('Konfigurasi JWT_SECRET tidak ditemukan. Silakan set di file .env.');
    }

    // 2. Ambil konfigurasi waktu kadaluarsa dari .env (opsional, dengan default)
    $expirationTime = getenv('JWT_EXPIRATION_SEC') ?: 3600; // Default 1 jam (3600 detik)

    $iat = time(); // Issued at: Waktu token dibuat
    $exp = $iat + (int)$expirationTime; // Expiration time: Waktu token kadaluarsa

    // 3. Issuer dan Audience sebaiknya dari base URL aplikasi
    $issuer = config('App')->baseURL ?: 'http://localhost:8080'; // Fallback jika baseURL tidak diset
    $audience = $issuer;

    $payload = [
        'iss' => $issuer,       // Issuer: Siapa yang mengeluarkan token
        'aud' => $audience,     // Audience: Untuk siapa token ini
        'iat' => $iat,          // Issued At: Waktu token diterbitkan
        'exp' => $exp,          // Expiration Time: Waktu token kadaluarsa
        // 4. Data pengguna yang penting dan TIDAK SENSITIF
        'uid' => $userId,       // User ID
        'email' => $email,
        // Anda bisa menambahkan data lain yang relevan di sini dari $additionalData
        // Contoh: 'role' => 'admin'
    ];

    // Gabungkan additionalData jika ada
    if (!empty($additionalData)) {
        $payload = array_merge($payload, $additionalData);
    }

    return JWT::encode($payload, $key, 'HS256'); // Algoritma HS256
}

/**
 * Memvalidasi JSON Web Token (JWT).
 *
 * @param string $token Token JWT yang akan divalidasi.
 * @return object|false Objek payload jika token valid, false jika tidak valid atau error.
 */
function validateJWT(string $token)
{
    // 1. Ambil secret key dari .env
    $key = getenv('JWT_SECRET');
    if (empty($key)) {
        log_message('critical', 'JWT_SECRET tidak disetel di file .env saat validasi.');
        return false; // Tidak bisa validasi tanpa key
    }

    try {
        // Perhatikan penggunaan 'new Key($key, 'HS256')' untuk library firebase/php-jwt versi baru
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded; // Kembalikan payload jika valid
    } catch (\Firebase\JWT\ExpiredException $e) {
        // Token sudah kadaluarsa
        log_message('info', 'JWT Expired: ' . $e->getMessage());
        return false;
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        // Signature tidak valid (token mungkin diubah atau key salah)
        log_message('error', 'JWT Signature Invalid: ' . $e->getMessage());
        return false;
    } catch (\Exception $e) {
        // Error umum lainnya saat decode (misal: token malformed)
        log_message('error', 'JWT Decode Error: ' . $e->getMessage());
        return false;
    }
}