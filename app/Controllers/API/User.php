<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
// Model sudah di-set di $modelName, jadi tidak perlu 'use App\Models\UserModel;' di sini kecuali untuk type hinting jika diperlukan.

class User extends ResourceController
{
    protected $modelName = 'App\Models\UserModel'; // Pastikan ini merujuk ke UserModel yang sudah diperbarui
    protected $format    = 'json';

    public function __construct()
    {
        // Load helper yang dibutuhkan, seperti jwt
        helper(['jwt', 'form']); // 'form' helper mungkin berguna untuk validasi, tapi tidak wajib di API murni
    }

    /**
     * Membuat user baru (Registrasi).
     * Endpoint: POST /api/users
     * atau POST /api/register (jika Anda membuat rute alias)
     */
    public function create()
    {
        $model = new $this->modelName(); // Instansiasi model
        $data = $this->request->getJSON(true); // Ambil data JSON sebagai array

        // Aturan validasi bisa diambil dari model atau didefinisikan di sini.
        // Jika aturan ada di model, Anda bisa menggunakan $model->getValidationRules().
        // Untuk registrasi, kita definisikan di sini untuk kejelasan, termasuk password.
        $validationRules = [
            'name'     => 'required|string|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]|max_length[100]', // is_unique tanpa {id} untuk create
            'password' => 'required|min_length[8]',
            // Opsional: tambahkan validasi untuk konfirmasi password jika ada di form Flutter
            // 'password_confirm' => 'required|matches[password]'
        ];

        // Pesan validasi custom bisa diambil dari model atau didefinisikan di sini.
        $validationMessages = $model->getValidationMessages(); // Ambil dari model jika ada
        // Atau tambahkan/override di sini:
        $validationMessages['email']['is_unique'] = 'Email ini sudah digunakan oleh akun lain.';
        // $validationMessages['password_confirm']['matches'] = 'Konfirmasi password tidak cocok.';


        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Siapkan data untuk disimpan ke database
        $insertData = [
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            // 'created_at' akan diisi otomatis oleh Model jika $useTimestamps = true
        ];

        try {
            $userId = $model->insert($insertData);

            if ($userId === false) {
                // Jika $model->insert() gagal karena validasi di model (jika skipValidation false)
                // atau karena error database.
                log_message('warning', 'Gagal insert user baru (registrasi): ' . json_encode($model->errors()));
                return $this->fail($model->errors() ?: ['error' => 'Gagal mendaftarkan pengguna. Silakan coba lagi.']);
            }

            // Buat token JWT untuk user yang baru terdaftar
            // Pastikan fungsi createJWT sudah ada di helper 'jwt' dan di-load
            $token = createJWT($userId, $insertData['email']);

            // Data user yang akan dikembalikan (tanpa password_hash)
            $userResponse = [
                'id'    => $userId,
                'name'  => $insertData['name'],
                'email' => $insertData['email'],
                // Anda bisa mengambil created_at dari data yang baru di-fetch jika perlu:
                // 'created_at' => $model->find($userId)['created_at']
            ];

            return $this->respondCreated([
                'status'  => 201,
                'error'   => false,
                'message' => 'Registrasi berhasil. Token JWT telah dibuat.',
                'data'    => [
                    'user'  => $userResponse,
                    'token' => $token
                ]
            ], 'Registrasi Pengguna Berhasil');

        } catch (\Exception $e) {
            log_message('error', '[UserCreate - Registrasi] Exception: {message}. Trace: {trace}', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->failServerError('Terjadi kesalahan pada server saat memproses registrasi Anda.');
        }
    }

    /**
     * Menampilkan daftar semua user.
     * Endpoint: GET /api/users
     */
    public function index()
    {
        $users = $this->model->findAll();
        $cleanedUsers = [];
        foreach ($users as $user) {
            if (isset($user['password_hash'])) {
                unset($user['password_hash']); // Jangan kirim hash password
            }
            $cleanedUsers[] = $user;
        }
        return $this->respond([
            'status' => 200,
            'error'  => false,
            'data'   => $cleanedUsers
        ]);
    }

    /**
     * Menampilkan detail satu user.
     * Endpoint: GET /api/users/{id}
     */
    public function show($id = null)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('User dengan ID ' . $id . ' tidak ditemukan.');
        }

        if (isset($user['password_hash'])) {
            unset($user['password_hash']); // Jangan kirim hash password
        }

        return $this->respond([
            'status' => 200,
            'error'  => false,
            'data'   => $user
        ]);
    }

    /**
     * Mengupdate data user.
     * Endpoint: PUT /api/users/{id} atau PATCH /api/users/{id}
     * Implementasi ini memerlukan logika otorisasi (user hanya bisa update diri sendiri atau admin bisa update).
     */
    // public function update($id = null)
    // {
    //     // 1. Dapatkan ID user yang login dari token (otorisasi)
    //     // 2. Validasi data input (gunakan aturan validasi model dengan {id} untuk is_unique)
    //     //    $validationRules = $this->model->getValidationRules(['except' => ['password_hash']]); // Sesuaikan
    //     //    Contoh jika user update profilnya sendiri (nama, email):
    //     //    $validationRules['email'] = "required|valid_email|is_unique[users.email,id,$id]|max_length[100]";
    //     // 3. Jika password diupdate, hash password baru.
    //     // 4. Siapkan data untuk update.
    //     // 5. Panggil $this->model->update($id, $dataToUpdate);
    //     // 6. Berikan response.
    //     return $this->fail('Fungsi update belum diimplementasikan sepenuhnya.', 501);
    // }

    /**
     * Menghapus user.
     * Endpoint: DELETE /api/users/{id}
     * Implementasi ini memerlukan logika otorisasi.
     */
    // public function delete($id = null)
    // {
    //     // 1. Dapatkan ID user yang login dari token (otorisasi)
    //     // 2. Panggil $this->model->delete($id);
    //     // 3. Berikan response (biasanya 204 No Content atau 200 OK dengan pesan).
    //     $user = $this->model->find($id);
    //     if (!$user) {
    //         return $this->failNotFound('User dengan ID ' . $id . ' tidak ditemukan.');
    //     }
    //     // if ($this->model->delete($id)) {
    //     //    return $this->respondDeleted(['id' => $id], 'User berhasil dihapus.');
    //     // }
    //     // return $this->fail('Gagal menghapus user.', 400);
    //     return $this->fail('Fungsi delete belum diimplementasikan sepenuhnya.', 501);
    // }
}