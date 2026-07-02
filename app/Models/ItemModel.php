<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['owner_id', 'name', 'category_id', 'quantity', 'price', 'image', 'image2', 'image3', 'description', 'rate_option_1', 'rate_option_2', 'overstay_charge', 'rental_details'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'        => 'required|min_length[3]|max_length[120]',
        'category_id' => 'required|integer',
        'quantity'    => 'required|integer|greater_than_equal_to[0]',
    ];

    protected $skipValidation = false;
}
