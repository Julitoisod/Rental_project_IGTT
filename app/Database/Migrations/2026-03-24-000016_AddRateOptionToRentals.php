<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRateOptionToRentals extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rentals', [
            'rate_option' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Which rate option was booked (1 or 2)',
            ],
            'actual_return_time' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Actual return time when item is returned',
            ],
            'overstay_charge' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0,
                'comment' => 'Additional overstay charges applied',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('rentals', ['rate_option', 'actual_return_time', 'overstay_charge']);
    }
}
