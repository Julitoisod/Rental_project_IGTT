<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddValidIdToUsers extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE users ADD COLUMN valid_id VARCHAR(255) NULL AFTER avatar");
        $this->db->query("ALTER TABLE users ADD COLUMN valid_id_type VARCHAR(50) NULL AFTER valid_id");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE users DROP COLUMN valid_id");
        $this->db->query("ALTER TABLE users DROP COLUMN valid_id_type");
    }
}
