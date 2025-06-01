<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql; // Untuk CURRENT_TIMESTAMP

class CreateSensorDataTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'device_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false, // Asumsikan device_id wajib ada
            ],
            'temperature' => [
                'type' => 'FLOAT',
                'null' => false,
            ],
            'humidity' => [
                'type' => 'FLOAT',
                'null' => false,
            ],
            'rack_status' => [
                'type'       => "ENUM('open', 'closed')", // Menggunakan ENUM untuk MySQL
                'null'       => false, // Sesuaikan jika bisa null
            ],
            'recorded_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'null'    => false,
            ],
        ]);
        $this->forge->addKey('id', true); // Primary key
        $this->forge->addForeignKey('device_id', 'devices', 'id', 'CASCADE', 'CASCADE'); // Foreign key
        $this->forge->createTable('sensor_data');
    }

    public function down()
    {
        $this->forge->dropTable('sensor_data');
    }
}