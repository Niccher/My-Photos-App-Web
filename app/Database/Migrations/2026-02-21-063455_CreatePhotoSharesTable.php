<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhotoSharesTable extends Migration
{
    public function up()
    {
        // No FK constraints to avoid ordering issues with Shield's users table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'photo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'shared_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'shared_with' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'permission' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'view',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('photo_id');
        $this->forge->addKey('shared_with');
        $this->forge->createTable('photo_shares');
    }

    public function down()
    {
        $this->forge->dropTable('photo_shares', true);
    }
}
