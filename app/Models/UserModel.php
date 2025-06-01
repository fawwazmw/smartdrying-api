<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true; // Sesuai dengan SERIAL/AUTO_INCREMENT di DB

    protected $returnType       = 'array'; // Atau 'App\Entities\User' jika Anda menggunakan Entitas
    protected $useSoftDeletes   = false; // Set true jika Anda ingin menggunakan soft deletes

    // Kolom yang diizinkan untuk diisi melalui mass assignment (insert/update)
    // 'created_at' dan 'updated_at' akan diurus oleh $useTimestamps jika true
    protected $allowedFields    = ['name', 'email', 'password_hash'];

    // Apakah menggunakan timestamps (created_at, updated_at)
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime'; // Format timestamp (datetime, date, int)
    protected $createdField     = 'created_at'; // Nama kolom untuk created_at
    protected $updatedField     = '';         // Nama kolom untuk updated_at (kosongkan jika tidak ada)
    // protected $deletedField  = 'deleted_at'; // Untuk soft deletes

    // Aturan validasi
    // Placeholder {id} penting untuk aturan 'is_unique' saat update
    protected $validationRules = [
        'name'     => 'required|string|min_length[3]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]|max_length[100]',
        // Validasi untuk 'password' (sebelum di-hash) sebaiknya dilakukan di Controller
        // atau jika Anda membuat field 'password' di $allowedFields dan menghash via callback di model.
        // Contoh jika 'password' ada di $allowedFields dan divalidasi di model:
        // 'password' => 'required|min_length[8]',
        'password_hash' => 'required|string', // Ini adalah hasil hash, jadi hanya 'required'
    ];

    // Pesan error custom untuk validasi
    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama wajib diisi.',
            'min_length' => 'Nama minimal harus 3 karakter.',
            'max_length' => 'Nama maksimal 100 karakter.',
        ],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Maaf, email tersebut sudah terdaftar.',
            'max_length'  => 'Email maksimal 100 karakter.',
        ],
        // 'password' => [
        //     'required'   => 'Password wajib diisi.',
        //     'min_length' => 'Password minimal harus 8 karakter.',
        // ],
    ];

    protected $skipValidation       = false; // Set true untuk menonaktifkan validasi sementara
    protected $cleanValidationRules = true; // Membersihkan aturan validasi setelah digunakan

    // Callbacks (opsional, contoh untuk hashing password jika dilakukan di model)
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = ['hashPassword'];
    // protected $beforeUpdate   = ['hashPassword'];

    /**
     * Contoh fungsi callback untuk hashing password.
     * Ini hanya akan berjalan jika 'password' ada di $data['data']
     * dan Anda mengirim field 'password' (bukan 'password_hash') ke metode insert/update model.
     */
    /*
    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        // Hapus field password asli agar tidak mencoba menyimpannya ke kolom 'password'
        unset($data['data']['password']);

        return $data;
    }
    */
}