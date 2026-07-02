<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdNameToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'id_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
                'after'      => 'valid_id_type',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'id_name');
    }
}
