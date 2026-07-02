<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name'       => 'Toyota Avanza',
                'category_id'=> 1,
                'quantity'   => 4,
                'price'      => 450000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Meeting Room A',
                'category_id'=> 2,
                'quantity'   => 1,
                'price'      => 250000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Projector Epson X1',
                'category_id'=> 3,
                'quantity'   => 5,
                'price'      => 80000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('items')->insertBatch($items);
    }
}
