<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentReferenceToRentals extends Migration
{
    public function up()
    {
        // Add payment_reference image field
        $this->forge->addColumn('rentals', [
            'payment_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'overstay_charge',
            ],
        ]);

        // Add awaiting_payment to the status enum
        $this->db->query("ALTER TABLE rentals MODIFY COLUMN status ENUM('awaiting_payment','booked','pending','ongoing','returned','overdue') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->forge->dropColumn('rentals', 'payment_reference');
        $this->db->query("ALTER TABLE rentals MODIFY COLUMN status ENUM('booked','pending','ongoing','returned','overdue') NOT NULL DEFAULT 'pending'");
    }
}
