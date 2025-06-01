<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua ID user yang ada
        $userModel = new \App\Models\UserModel(); // Atau $this->db->table('users')->get()->getResultArray();
        $users = $userModel->select('id')->findAll();

        if (empty($users)) {
            echo "No users found. Run UserSeeder first.\n";
            return;
        }

        $devicesData = [];
        $deviceNames = ['Jemuran Utama', 'Jemuran Balkon', 'Jemuran Eksperimen'];
        $locations = ['Belakang Rumah', 'Lantai 2', 'Laboratorium'];

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) { // Setiap user punya 1 atau 2 device
                $devicesData[] = [
                    'user_id'     => $user['id'],
                    'device_name' => $deviceNames[array_rand($deviceNames)] . ' ' . ($i + 1),
                    'location'    => $locations[array_rand($locations)],
                    'created_at'  => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (!empty($devicesData)) {
            $this->db->table('devices')->insertBatch($devicesData);
            echo "DeviceSeeder run successfully with " . count($devicesData) . " devices.\n";
        } else {
            echo "No devices data generated to seed.\n";
        }
    }
}