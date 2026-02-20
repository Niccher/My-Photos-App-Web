<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationToPhotos extends Migration
{
    public function up()
    {
        $fields = [
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
                'after' => 'mime_type'
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
                'after' => 'latitude'
            ],
        ];
        $this->forge->addColumn('photos', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('photos', 'latitude');
        $this->forge->dropColumn('photos', 'longitude');
    }
}
