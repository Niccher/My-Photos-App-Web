<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddContextToAuthTokenLogins
 *
 * Shield's `auth_token_logins` table also expects a `context` column
 * for storing metadata about token-based login attempts.
 */
class AddContextToAuthTokenLogins extends Migration
{
    public function up()
    {
        $fields = [
            'context' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'user_agent',
            ],
        ];
        $this->forge->addColumn('auth_token_logins', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('auth_token_logins', 'context');
    }
}
