<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql; // Untuk CURRENT_TIMESTAMP

class CreateDevicesTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Sesuaikan jika user_id wajib ada
            ],
            'device_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true, // Sesuai skema awal, bisa diubah jika wajib
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'null'    => false,
            ],
        ]);
        $this->forge->addKey('id', true); // Primary key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE'); // Foreign key
        // Argumen ke-4: ON DELETE, Argumen ke-5: ON UPDATE (CASCADE juga untuk update jika diinginkan)
        // Jika user_id boleh NULL dan tidak ingin ada aksi CASCADE saat user_id = NULL (misalnya jika user dihapus),
        // maka user_id tidak boleh null atau gunakan SET NULL jika user_id bisa null
        // Karena skema awal `REFERENCES users(id) ON DELETE CASCADE`, maka user_id sebaiknya NOT NULL
        // Jika user_id bisa NULL, `ON DELETE CASCADE` mungkin tidak berlaku seperti yang diharapkan jika foreign key adalah NULL.
        // Saya akan asumsikan user_id adalah NOT NULL berdasarkan penggunaan ON DELETE CASCADE. Jika bisa NULL, harap sesuaikan.
        // Untuk itu, ubah 'null' => true menjadi 'null' => false pada user_id jika memang wajib.
        // Jika user_id wajib, maka:
        // 'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],

        $this->forge->createTable('devices');
    }

    public function down()
    {
        $this->forge->dropTable('devices');
    }
}