<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlbumsTables extends Migration
{
    public function up()
    {
        // 1. Albums Table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'cover_photo_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('albums');

        // 2. Album Photos (Pivot Table)
        $this->forge->addField([
            'album_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'photo_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'added_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addForeignKey('album_id', 'albums', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('photo_id', 'photos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('album_photos');
    }

    public function down()
    {
        $this->forge->dropTable('album_photos');
        $this->forge->dropTable('albums');
    }
}
