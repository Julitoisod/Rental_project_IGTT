<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdVerifiedToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_id_verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'valid_id_type',
            ],
            'id_verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'is_id_verified',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['is_id_verified', 'id_verified_at']);
    }
}
