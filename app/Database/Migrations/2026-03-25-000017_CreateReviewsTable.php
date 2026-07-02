<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'rental_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id'   => ['type' => 'INT', 'unsigned' => true],
            'item_id'   => ['type' => 'INT', 'unsigned' => true],
            'rating'    => ['type' => 'TINYINT', 'unsigned' => true, 'constraint' => 1],
            'comment'   => ['type' => 'TEXT', 'null' => true],
            'created_at'=> ['type' => 'DATETIME', 'null' => true],
            'updated_at'=> ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rental_id');      // one review per rental
        $this->forge->addKey('item_id');
        $this->forge->createTable('reviews');
    }

    public function down()
    {
        $this->forge->dropTable('reviews');
    }
}
