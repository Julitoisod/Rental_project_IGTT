<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnerIdToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', 'owner_id');
    }
}
