<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePedidosProdutosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pedido_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'produto_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'quantidade' => [
                'type' => 'INT',
                'constraint' => 5,
            ],
            'preco_unitario' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('produto_id', 'produtos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pedidos_produtos');
    }


    public function down()
    {
        $this->forge->dropTable('pedidos_produtos');
    }
}
