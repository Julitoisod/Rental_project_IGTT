<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookingFeeToRentals extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE rentals ADD COLUMN booking_fee DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total_cost"
        );
    }

    public function down()
    {
        $this->db->query("ALTER TABLE rentals DROP COLUMN booking_fee");
    }
}
