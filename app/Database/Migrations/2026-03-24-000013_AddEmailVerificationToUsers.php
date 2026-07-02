<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerificationToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'email_verify_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'id_ocr_text',
            ],
            'email_verify_expires' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'email_verify_code',
            ],
            'email_verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'email_verify_expires',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'email_verify_code');
        $this->forge->dropColumn('users', 'email_verify_expires');
        $this->forge->dropColumn('users', 'email_verified_at');
    }
}
