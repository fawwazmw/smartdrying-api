<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time; // Untuk manipulasi waktu

class SensorDataSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua ID device yang ada
        $deviceModel = new \App\Models\DeviceModel(); // Atau $this->db->table('devices')->get()->getResultArray();
        $devices = $deviceModel->select('id')->findAll();

        if (empty($devices)) {
            echo "No devices found. Run DeviceSeeder first.\n";
            return;
        }

        $sensorDataBatch = [];
        $rackStatusOptions = ['open', 'closed'];

        foreach ($devices as $device) {
            $numberOfRecords = rand(5, 15); // Setiap device punya 5-15 data sensor
            for ($i = 0; $i < $numberOfRecords; $i++) {
                // Buat timestamp mundur dari sekarang untuk variasi data
                $time = Time::now()->subHours(rand(0, 24 * 3))->subMinutes(rand(0, 59));

                $sensorDataBatch[] = [
                    'device_id'   => $device['id'],
                    'temperature' => rand(250, 350) / 10.0, // Suhu antara 25.0 - 35.0 C
                    'humidity'    => rand(400, 800) / 10.0,  // Kelembaban antara 40.0 - 80.0 %
                    'rack_status' => $rackStatusOptions[array_rand($rackStatusOptions)],
                    'recorded_at' => $time->toDateTimeString(), // Format YYYY-MM-DD HH:MM:SS
                ];
            }
        }

        if (!empty($sensorDataBatch)) {
            $this->db->table('sensor_data')->insertBatch($sensorDataBatch);
            echo "SensorDataSeeder run successfully with " . count($sensorDataBatch) . " records.\n";
        } else {
            echo "No sensor data generated to seed.\n";
        }
    }
}