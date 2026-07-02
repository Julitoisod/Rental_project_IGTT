<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUsersRoleEnum extends Migration
{
    public function up()
    {
        // Change role ENUM from (admin, user) to (snd_admin, owner, customer)
        $fields = [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['snd_admin', 'owner', 'customer'],
                'default'    => 'customer',
            ],
        ];
        $this->forge->modifyColumn('users', $fields);
    }

    public function down()
    {
        $fields = [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'user'],
                'default'    => 'user',
            ],
        ];
        $this->forge->modifyColumn('users', $fields);
    }
}
