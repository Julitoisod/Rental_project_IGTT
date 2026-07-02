<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdOcrTextToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'id_ocr_text' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'id_name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'id_ocr_text');
    }
}
