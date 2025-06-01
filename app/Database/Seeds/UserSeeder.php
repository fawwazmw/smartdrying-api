<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $usersData = [
            [
                'name'          => 'Admin User',
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Regular User',
                'email'         => 'user@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Mahesa Adi',
                'email'         => 'mahesa.adi@example.com',
                'password_hash' => password_hash('mahesapass', PASSWORD_DEFAULT),
                'created_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        // Menggunakan Query Builder untuk insert data
        // $this->db->table('users')->insertBatch($usersData);

        // Atau insert satu per satu jika ingin mendapatkan ID (meskipun tidak diperlukan di seeder ini)
        foreach ($usersData as $data) {
            $this->db->table('users')->insert($data);
        }

        echo "UserSeeder run successfully.\n";
    }
}