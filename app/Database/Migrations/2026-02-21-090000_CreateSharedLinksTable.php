<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSharedLinksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'photo_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'access_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'unique'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('photo_id', 'photos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('shared_links');
    }

    public function down()
    {
        $this->forge->dropTable('shared_links');
    }
}
