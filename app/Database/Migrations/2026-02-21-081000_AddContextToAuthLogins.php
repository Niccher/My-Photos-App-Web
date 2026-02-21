<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddContextToAuthLogins
 *
 * Shield's `auth_logins` table expects a `context` column to store
 * additional JSON data about the login attempt (e.g., device info).
 * This column was missing, causing `DatabaseException #1054`.
 */
class AddContextToAuthLogins extends Migration
{
    public function up()
    {
        // Add a nullable TEXT column named `context` after `user_agent`.
        $fields = [
            'context' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'user_agent',
            ],
        ];
        $this->forge->addColumn('auth_logins', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('auth_logins', 'context');
    }
}
