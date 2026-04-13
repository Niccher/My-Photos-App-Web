<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileHashToPhotos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('photos', [
            'file_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null'       => true,
                'after'      => 'size',
            ],
        ]);
        
        // Add index for faster lookup
        $this->db->query("CREATE INDEX photos_file_hash_idx ON photos(file_hash)");
    }

    public function down()
    {
        $this->forge->dropColumn('photos', 'file_hash');
    }
}
