<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileFieldsToUsers extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL AFTER email");
        $this->db->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER phone");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE users DROP COLUMN phone");
        $this->db->query("ALTER TABLE users DROP COLUMN avatar");
    }
}
