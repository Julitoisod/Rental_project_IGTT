<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookedStatus extends Migration
{
    public function up()
    {
        // Extend the status ENUM to include 'booked'
        $this->db->query("ALTER TABLE rentals MODIFY status ENUM('booked','pending','ongoing','returned','overdue') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE rentals MODIFY status ENUM('pending','ongoing','returned','overdue') NOT NULL DEFAULT 'pending'");
    }
}
