<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class SensorData extends ResourceController
{
    use ResponseTrait; // Penting untuk $this->respond(), $this->fail(), dll.

    protected $modelName = 'App\Models\SensorDataModel';
    protected $format    = 'json'; // Format respons default

    public function __construct()
    {
        // helper('jwt'); // Load helper jika diperlukan untuk otorisasi di sini
                         // Idealnya, otorisasi sudah ditangani oleh Filter API
    }

    /**
     * Menampilkan daftar data sensor untuk device tertentu.
     * Dipanggil oleh: GET /api/devices/{deviceId}/sensor-data
     * Parameter $deviceId akan otomatis diisi oleh CI4 dari (:num) di rute.
     */
    public function index($deviceId = null)
    {
        if ($deviceId === null) {
            return $this->failNotFound('Device ID wajib disertakan untuk mengambil data sensor.');
        }

        // --- Implementasi Otorisasi (PENTING) ---
        // Di sini Anda harus memastikan bahwa user yang sedang login
        // (berdasarkan token JWT yang sudah divalidasi oleh Filter API Anda)
        // berhak untuk melihat data sensor dari $deviceId ini.
        // Contoh:
        // $loggedInUserId = $this->request->user->id; // Jika Filter Anda menyediakan ini
        // $deviceModel = new \App\Models\DeviceModel();
        // $device = $deviceModel->where('id', $deviceId)->where('user_id', $loggedInUserId)->first();
        // if (!$device) {
        //     return $this->failForbidden('Anda tidak memiliki izin untuk mengakses data sensor device ini.');
        // }
        // --- Akhir Otorisasi ---

        // Ambil parameter query dari URL (misalnya ?limit=1&orderBy=recorded_at&direction=desc)
        $limit = $this->request->getGet('limit');
        $orderBy = $this->request->getGet('orderBy') ?? 'recorded_at'; // Default order
        $direction = $this->request->getGet('direction') ?? 'DESC';   // Default direction

        // Query data sensor
        $queryBuilder = $this->model->where('device_id', $deviceId)
                                   ->orderBy($orderBy, strtoupper($direction));
        
        if ($limit !== null && is_numeric($limit) && (int)$limit > 0) {
            $data = $queryBuilder->findAll((int)$limit);
        } else {
            // Jika tidak ada limit atau limit tidak valid, ambil semua (atau default pagination dari model)
            // Pertimbangkan untuk selalu memberi limit default jika tidak ada, untuk mencegah pengambilan data besar.
            $data = $queryBuilder->findAll(10); // Contoh: default limit 10 jika tidak dispesifikkan
        }

        if (empty($data)) {
            return $this->respond([
                'status'   => 200, // Atau 404 jika lebih sesuai
                'error'    => false,
                'messages' => ['message' => 'Belum ada data sensor untuk device ini atau device tidak ditemukan.'],
                'data'     => [] // Kembalikan array kosong
            ]);
        }
        
        return $this->respond([
            'status'   => 200,
            'error'    => false,
            'messages' => ['message' => 'Data sensor berhasil diambil.'],
            'data'     => $data // $data di sini adalah array dari hasil findAll()
        ]);
    }

    /**
     * Membuat data sensor baru untuk device tertentu.
     * Endpoint: POST /api/devices/{deviceId}/sensor-data
     * (Anda perlu menambahkan rute POST 'devices/(:num)/sensor-data' di Routes.php jika ingin menggunakan ini)
     */
    public function create($deviceId = null) // Method create bisa menerima deviceId jika dirutekan dengan $1
    {
        if ($deviceId === null) {
             return $this->failNotFound('Device ID wajib disertakan untuk mengirim data sensor.');
        }

        // --- Implementasi Otorisasi (PENTING) ---
        // Pastikan request ini (mungkin dari device IoT itu sendiri atau user)
        // berhak mengirim data ke $deviceId ini.

        $data = $this->request->getJSON(true); // Ambil data JSON dari body request
        
        // Tambahkan device_id ke data yang akan disimpan
        $data['device_id'] = $deviceId;

        // Validasi data (gunakan aturan validasi di SensorDataModel)
        if ($this->model->validate($data) === false) {
            return $this->failValidationErrors($this->model->errors());
        }

        // Jika 'recorded_at' tidak dikirim, model Anda mungkin sudah mengaturnya
        // atau Anda bisa set di sini:
        // if (!isset($data['recorded_at'])) {
        //    $data['recorded_at'] = date('Y-m-d H:i:s');
        // }
        
        $insertedId = $this->model->insert($data);

        if ($insertedId === false) {
            log_message('warning', 'Gagal insert data sensor: ' . json_encode($this->model->errors()));
            return $this->fail($this->model->errors() ?: ['error' => 'Gagal menyimpan data sensor.']);
        }

        $newData = $this->model->find($insertedId);
        return $this->respondCreated([
            'status' => 201, 
            'error' => false, 
            'data' => $newData, 
            'messages' => ['message' => 'Data sensor berhasil disimpan.']
        ]);
    }

    // Anda bisa menambahkan method show($id = null) jika ingin mengambil satu data sensor spesifik berdasarkan ID data sensornya,
    // tapi ini mungkin kurang umum dibandingkan mengambil data sensor berdasarkan deviceId.
    // public function show($id = null) { ... }
}