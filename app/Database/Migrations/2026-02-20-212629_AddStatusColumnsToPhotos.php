<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusColumnsToPhotos extends Migration
{
    public function up()
    {
        //
        $fields = [
            'is_archived' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'after'      => 'exif_data',
            ],
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'is_archived',
            ],
        ];
        $this->forge->addColumn('photos', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('photos', ['is_archived', 'deleted_at']);
    }
}
