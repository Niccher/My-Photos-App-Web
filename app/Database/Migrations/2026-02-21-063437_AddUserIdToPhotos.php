<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToPhotos extends Migration
{
    public function up()
    {
        //
        $fields = [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ];

        $this->forge->addColumn('photos', $fields);

        // We could add a foreign key, but since we are modifying an existing table, 
        // it's safer to just add the column first. Shield's users table uses 'id' (INT UNSIGNED).
    }

    public function down()
    {
        $this->forge->dropColumn('photos', 'user_id');
    }
}
