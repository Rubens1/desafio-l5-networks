<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{ protected $table      = 'clientes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['cpf_cnpj', 'nome_razao_social', 'updated_at', 'created_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
