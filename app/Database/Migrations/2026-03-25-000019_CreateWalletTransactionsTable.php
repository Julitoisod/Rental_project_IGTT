<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWalletTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true],
            'type'        => ['type' => 'ENUM', 'constraint' => ['top_up', 'deduction', 'refund'], 'default' => 'top_up'],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'balance_after' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'reference'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],  // e.g. "Rental #11"
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('wallet_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('wallet_transactions');
    }
}
