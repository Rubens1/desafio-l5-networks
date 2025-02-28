<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoProdutoModel extends Model
{
    protected $table = 'pedidos_produtos';

    protected $primaryKey = 'id';
    protected $allowedFields = ['pedido_id', 'produto_id', 'quantidade', 'preco_unitario', 'updated_at', 'created_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
