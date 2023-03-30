<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Products extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'p_id' =>[
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'p_name' =>[
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'p_description' =>[
                'type' => 'TEXT',
                'null' => true,
            ],
            'p_price' =>[
                'type' => 'DOUBLE',
            ],
            'p_slug' =>[
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'p_created datetime default current_timestamp',
            'p_updated datetime default current_timestamp on update current_timestamp'
        ]);
        $this->forge->addKey('p_id', true);
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}
