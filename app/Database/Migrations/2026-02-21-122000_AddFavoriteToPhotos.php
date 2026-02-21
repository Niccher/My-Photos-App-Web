<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFavoriteToPhotos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('photos', [
            'is_favorite' => ['type' => 'BOOLEAN', 'default' => false, 'after' => 'is_archived'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('photos', 'is_favorite');
    }
}
