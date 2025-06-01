<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
// Firebase\JWT\JWT dan Key tidak perlu di-import di sini jika sudah dihandle oleh helper

class Auth extends ResourceController
{
    public function __construct()
    {
        // Load helper jwt, bisa juga di BaseController atau autoload
        helper('jwt');
    }

    public function login()
    {
        $model = new UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        if (!$email || !$password) {
            return $this->failValidationErrors('Email dan password wajib diisi.');
        }

        $user = $model->where('email', $email)->first();

        if (!$user || !password_verify((string)$password, $user['password_hash'])) {
            return $this->failUnauthorized('Email atau password salah');
        }

        // --- PERBAIKAN: Menggunakan createJWT dari helper ---
        try {
            // Panggil fungsi createJWT dari jwt_helper.php
            // Pastikan helper 'jwt' sudah di-load (lihat __construct atau BaseController)
            $token = createJWT($user['id'], $user['email'] /*, ['role' => $user['role']] jika ada role */);
            return $this->respond(['token' => $token, 'message' => 'Login berhasil']);
        } catch (\Exception $e) {
            log_message('error', '[AuthLogin] Gagal membuat JWT: ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan internal saat memproses login Anda.');
        }
    }
}