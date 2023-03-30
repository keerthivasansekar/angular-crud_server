<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'user_email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'user_password' => [
                'type' => 'VARCHAR',
                'constraint' => '512',
            ],
            'user_fp_token' => [
                'type' => 'VARCHAR',
                'constraint' => '512',
            ],
            'user_deleted' => [
                'type' => 'BOOLEAN',
            ],
            'created_on' => [
                'type' => 'DATETIME',
            ],
            'updated_on' => [
                'type' => 'DATETIME',
            ],
            'deleted_on' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
