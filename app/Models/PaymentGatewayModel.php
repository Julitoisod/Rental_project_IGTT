<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentGatewayModel extends Model
{
    protected $table         = 'payment_gateways';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['owner_id', 'name', 'type', 'account_number', 'qr_code'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'owner_id'       => 'required|integer',
        'name'           => 'required|min_length[2]|max_length[100]',
        'type'           => 'required|max_length[50]',
        'account_number' => 'required|max_length[100]',
    ];

    protected $skipValidation = false;
}
