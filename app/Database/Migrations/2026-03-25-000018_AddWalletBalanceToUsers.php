<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWalletBalanceToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'wallet_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'role',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'wallet_balance');
    }
}
