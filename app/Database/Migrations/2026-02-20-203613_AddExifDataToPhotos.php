<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExifDataToPhotos extends Migration
{
    public function up()
    {
        $fields = [
            'exif_data' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'longitude'
            ],
        ];
        $this->forge->addColumn('photos', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('photos', 'exif_data');
    }
}
