<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRentalRatesToItems extends Migration
{
    public function up()
    {
        // Add rental rate options and details to items table
        $this->forge->addColumn('items', [
            'rate_option_1' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'First rental rate option: {hours, price}',
            ],
            'rate_option_2' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Second rental rate option: {hours, price}',
            ],
            'overstay_charge' => [
                'type' => 'DECIMAL',
                'constraint' => [12, 2],
                'default' => 100,
                'comment' => 'Extra charge per additional period after 24 hours',
            ],
            'rental_details' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Owner-defined rental terms and conditions',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', ['rate_option_1', 'rate_option_2', 'overstay_charge', 'rental_details']);
    }
}
