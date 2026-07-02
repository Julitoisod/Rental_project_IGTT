<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtraImagesToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'image2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'image',
            ],
            'image3' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'image2',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', ['image2', 'image3']);
    }
}
