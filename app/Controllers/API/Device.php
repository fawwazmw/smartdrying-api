<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait; // Penting untuk $this->respond() dll.

class Device extends ResourceController
{
    use ResponseTrait; // Pastikan ini ada

    protected $modelName = 'App\Models\DeviceModel';
    protected $format    = 'json'; // Format respons default

    public function __construct()
    {
        // Jika Anda memiliki helper atau service untuk autentikasi/JWT yang perlu di-load
        // helper('jwt'); // Contoh
    }

    /**
     * Menampilkan daftar semua device milik user yang terautentikasi.
     * Endpoint: GET /api/devices
     */
    public function index()
    {
        // --- PENTING: Implementasi Autentikasi & Otorisasi ---
        // Langkah ini mengasumsikan Anda memiliki Filter Autentikasi (misalnya JWTFilter)
        // yang sudah memvalidasi token dan menyediakan ID user yang login.
        // Jika filter Anda menyimpan data user di $this->request->user:
        // $loggedInUserId = $this->request->user->id; // Ganti 'user' dan 'id' sesuai implementasi filter Anda

        // --- CONTOH JIKA BELUM ADA FILTER USER ID OTOMATIS ---
        // Anda mungkin perlu mengambil user ID dari token secara manual di sini JIKA filter belum menyediakannya.
        // Ini kurang ideal, filter lebih baik. Contoh kasar (PERLU LIBRARY JWT YANG SAMA & HELPER):
        /*
        helper('jwt'); // Pastikan helper jwt_helper.php sudah ada dan di-load
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;
        if (!empty($authHeader) && strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
        }

        if (!$token) {
            return $this->failUnauthorized('Token tidak tersedia atau format salah.');
        }

        try {
            $decodedToken = validateJWT($token); // Fungsi dari jwt_helper.php
            if ($decodedToken === false || !isset($decodedToken->uid)) {
                return $this->failUnauthorized('Token tidak valid atau kadaluarsa.');
            }
            $loggedInUserId = $decodedToken->uid;
        } catch (\Exception $e) {
            log_message('error', '[DeviceController] Error validasi token: ' . $e->getMessage());
            return $this->failUnauthorized('Error saat validasi token: ' . $e->getMessage());
        }
        */
        // --- AKHIR CONTOH JIKA BELUM ADA FILTER USER ID OTOMATIS ---
        
        // Untuk sekarang, mari kita asumsikan $loggedInUserId sudah didapatkan.
        // Jika Anda belum mengimplementasikan pengambilan $loggedInUserId dari token,
        // Anda bisa hardcode sementara untuk testing, TAPI JANGAN LUPA DIGANTI!
        // $loggedInUserId = 1; // CONTOH HARDCODE UNTUK USER ID 1 (HAPUS INI DI PRODUKSI)
        // Atau, jika Anda ingin mengembalikan SEMUA device (TIDAK AMAN TANPA PAGINATION & OTORISASI LEVEL ADMIN):
        // $devices = $this->model->findAll();

        // --- CARA YANG BENAR SETELAH MENDAPATKAN $loggedInUserId ---
        // (Ganti dengan logika $loggedInUserId yang sebenarnya dari token)
        // Untuk testing dan memastikan Flutter tidak error 501, kita coba kembalikan semua device dulu,
        // TAPI ini harus segera diamankan dengan filter user_id.

        // --- IMPLEMENTASI SEMENTARA (mengembalikan semua device, HARUS DIAMANKAN!) ---
        // $devices = $this->model->findAll();
        // echo "PERINGATAN: Endpoint /api/devices belum difilter berdasarkan user yang login! Semua device dikembalikan.\n";
        // --- AKHIR IMPLEMENTASI SEMENTARA ---

        // --- IMPLEMENTASI YANG SEHARUSNYA (setelah mendapatkan $loggedInUserId dari token) ---
        // Placeholder $loggedInUserId (ganti dengan cara Anda mendapatkan ID user dari token)
        // Jika Anda belum punya filter yang mengisi $this->request->user,
        // Anda perlu logika untuk decode token di sini atau di BaseController/Filter.
        // Untuk contoh ini, mari kita asumsikan Anda akan mengimplementasikannya.
        // Untuk sekarang, agar tidak error 501, kita akan coba kembalikan SEMUA devices,
        // tapi ini TIDAK AMAN dan perlu segera diperbaiki dengan filter user.
        // Ganti baris di bawah ini dengan logika yang benar untuk mengambil $loggedInUserId
        
        // Untuk sekarang, kita akan coba ambil semua device. FLUTTER ANDA MENGHARAPKAN FIELD 'data'.
        // Pastikan model Anda sudah benar.
        $loggedInUserId = null; // Akan kita coba dapatkan dari token

        // (Salin blok kode pengambilan $loggedInUserId dari token seperti contoh di atas jika belum ada filter)
        // Untuk sementara, kita tidak akan filter by user dulu agar fokus ke 501
        // Ini akan mengembalikan SEMUA device. Anda harus segera mengamankannya!
        $devices = $this->model->findAll();
        // $devices = $this->model->where('user_id', $loggedInUserId)->findAll(); // Ini yang seharusnya


        if (empty($devices)) {
            return $this->respond([
                'status'   => 200, // Atau 404 jika memang tidak ada device sama sekali untuk user
                'error'    => false,
                'messages' => ['message' => 'Tidak ada device ditemukan.'],
                'data'     => []
            ]);
        }

        return $this->respond([
            'status'   => 200,
            'error'    => false,
            'messages' => ['message' => 'Devices berhasil diambil.'],
            'data'     => $devices
        ]);
    }

    /**
     * Menampilkan detail satu device.
     * Endpoint: GET /api/devices/{id}
     */
    public function show($id = null)
    {
        // TODO: Implementasi otorisasi, pastikan user yang login berhak melihat device ini.
        $device = $this->model->find($id);
        if ($device) {
            return $this->respond([
                'status' => 200, 'error' => false, 'data' => $device, 'messages' => ['message' => 'Device ditemukan.']
            ]);
        }
        return $this->failNotFound('Device dengan ID ' . $id . ' tidak ditemukan.');
    }

    /**
     * Membuat device baru.
     * Endpoint: POST /api/devices
     */
    public function create()
    {
        // TODO: Implementasi otorisasi dan validasi
        // $loggedInUserId = ... (dapatkan dari token)
        $data = $this->request->getJSON(true);
        // $data['user_id'] = $loggedInUserId; // Set user_id otomatis

        // Validasi data sebelum insert
        // if (!$this->model->validate($data)) {
        //     return $this->failValidationErrors($this->model->errors());
        // }

        $id = $this->model->insert($data);
        if ($this->model->errors()) {
             return $this->fail($this->model->errors());
        }
        if ($id === false) { // Double check jika insert gagal tanpa error validasi spesifik
            return $this->failServerError('Gagal membuat device.');
        }

        $newDevice = $this->model->find($id);
        return $this->respondCreated([
            'status' => 201, 'error' => false, 'data' => $newDevice, 'messages' => ['message' => 'Device berhasil dibuat.']
        ]);
    }

    // Anda mungkin juga perlu method update() dan delete()
}