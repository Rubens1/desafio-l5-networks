<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\{PedidoModel, PedidoProdutoModel, ProdutoModel, ClienteModel};
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Database;
class PedidosController extends BaseController
{

    private $key = "uma_chave_super_secreta_bem_grande_para_maior_seguranca";


    private function verificarToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return false;
        }

        try {
            $token = explode(" ", $authHeader)[1];
            return JWT::decode($token, new Key($this->key, 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function pedidos()
    {

        $tokenValido = $this->verificarToken();
        if (!$tokenValido) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'mensagem' => 'Token inválido ou não fornecido.',
                'retorno' => []
            ]);
        }

        try {
            $model = new PedidoModel();
            $pedidos = $model->findAll();

            if (empty($pedidos)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Nenhum pedido encontrado.',
                    'retorno' => []
                ]);
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Lista de pedidos encontrado com sucesso.',
                'retorno' => $pedidos
            ]);
        } catch (\Throwable $th) {

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage() // Opcional, útil para debug
            ]);
        }
    }

    public function cadastrar()
    {

        $tokenValido = $this->verificarToken();
        if (!$tokenValido) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'mensagem' => 'Token inválido ou não fornecido.',
                'retorno' => []
            ]);
        }

        try {
            $pedidoModel = new PedidoModel();
            $produtoPedidoModel = new PedidoProdutoModel();
            $produtoModel = new ProdutoModel();

            $data = $this->request->getJSON(true);

            if (!isset($data['cliente_id']) || empty($data['cliente_id'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O campo cliente_id é obrigatório.'
                ]);
            }

            if (!isset($data['produtos']) || !is_array($data['produtos']) || empty($data['produtos'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'É necessário informar pelo menos um produto para o pedido.'
                ]);
            }

            $db = Database::connect();
            $db->transBegin();

            $totalPedido = 0;
            $produtosValidados = [];

            foreach ($data['produtos'] as $produto) {
                if (!isset($produto['produto_id']) || !isset($produto['quantidade'])) {
                    $db->transRollback();
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 400,
                        'mensagem' => 'Cada produto deve conter produto_id e quantidade.'
                    ]);
                }

                $produtoData = $produtoModel->find($produto['produto_id']);

                if (!$produtoData) {
                    $db->transRollback();
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 404,
                        'mensagem' => "Produto ID {$produto['produto_id']} não encontrado."
                    ]);
                }

                if ($produtoData['quantidade'] < $produto['quantidade']) {
                    $db->transRollback();
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 400,
                        'mensagem' => "Estoque insuficiente para o produto ID {$produto['produto_id']}."
                    ]);
                }

                $produtosValidados[] = [
                    'produto_id' => $produto['produto_id'],
                    'quantidade' => $produto['quantidade'],
                    'preco' => $produtoData['preco'],
                    'estoque_atual' => $produtoData['quantidade']
                ];

                $totalPedido += $produto['quantidade'] * $produtoData['preco'];
            }

            $pedidoId = $pedidoModel->insert([
                'cliente_id' => $data['cliente_id'],
                'status' => 'Em Aberto'
            ], true);

            foreach ($produtosValidados as $produto) {
                $produtoPedidoModel->insert([
                    'pedido_id' => $pedidoId,
                    'produto_id' => $produto['produto_id'],
                    'quantidade' => $produto['quantidade'],
                    'preco_unitario' => $produto['preco']
                ]);

                $novoEstoque = $produto['estoque_atual'] - $produto['quantidade'];
                $produtoModel->update($produto['produto_id'], ['quantidade' => $novoEstoque]);
            }

            $db->transCommit();

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 201,
                'mensagem' => 'Pedido cadastrado com sucesso!',
                'retorno' => ['pedido_id' => $pedidoId, 'total_pedido' => $totalPedido]
            ]);
        } catch (\Throwable $th) {
            // Em caso de erro, cancela tudo
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage()
            ]);
        }
    }


    public function editar($id)
    {

        $tokenValido = $this->verificarToken();
        if (!$tokenValido) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'mensagem' => 'Token inválido ou não fornecido.',
                'retorno' => []
            ]);
        }

        try {
            $pedidoModel = new PedidoModel();
            $produtoPedidoModel = new PedidoProdutoModel();
            $produtoModel = new ProdutoModel();

            $data = $this->request->getJSON(true);

            $pedido = $pedidoModel->find($id);
            if (!$pedido) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Pedido não encontrado.'
                ]);
            }

            if (!isset($data['cliente_id']) || empty($data['cliente_id'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O campo cliente_id é obrigatório.'
                ]);
            }

            if (!isset($data['produtos']) || !is_array($data['produtos']) || empty($data['produtos'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'É necessário informar pelo menos um produto para o pedido.'
                ]);
            }

            foreach ($data['produtos'] as $produto) {
                $produtoData = $produtoModel->find($produto['produto_id']);
                if (!$produtoData) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 404,
                        'mensagem' => "Produto ID {$produto['produto_id']} não encontrado."
                    ]);
                }

                $produtoPedidoExistente = $produtoPedidoModel->where('pedido_id', $id)
                    ->where('produto_id', $produto['produto_id'])
                    ->first();

                $quantidadeNova = $produto['quantidade'];
                $quantidadeAntiga = $produtoPedidoExistente ? $produtoPedidoExistente['quantidade'] : 0;

                if ($quantidadeNova > $quantidadeAntiga) {
                    $diferenca = $quantidadeNova - $quantidadeAntiga;
                    if ($produtoData['quantidade'] < $diferenca) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'status' => 400,
                            'mensagem' => "Estoque insuficiente para o produto ID {$produto['produto_id']}."
                        ]);
                    }
                }
            }

            $pedidoModel->update($id, ['cliente_id' => $data['cliente_id'], 'updated_at' => date('Y-m-d H:i:s')]);

            $totalPedido = 0;
            foreach ($data['produtos'] as $produto) {
                $produtoData = $produtoModel->find($produto['produto_id']);
                $produtoPedidoExistente = $produtoPedidoModel->where('pedido_id', $id)
                    ->where('produto_id', $produto['produto_id'])
                    ->first();

                $quantidadeNova = $produto['quantidade'];
                $quantidadeAntiga = $produtoPedidoExistente ? $produtoPedidoExistente['quantidade'] : 0;

                $subtotal = $quantidadeNova * $produtoData['preco']; // **Calculando o subtotal**
                $totalPedido += $subtotal; // **Somando ao total do pedido**

                if ($produtoPedidoExistente) {
                    if ($quantidadeNova < $quantidadeAntiga) {
                        $diferenca = $quantidadeAntiga - $quantidadeNova;
                        $novoEstoque = $produtoData['quantidade'] + $diferenca;
                        $produtoModel->update($produto['produto_id'], ['quantidade' => $novoEstoque]);
                    }

                    if ($quantidadeNova > $quantidadeAntiga) {
                        $diferenca = $quantidadeNova - $quantidadeAntiga;
                        $novoEstoque = $produtoData['quantidade'] - $diferenca;
                        $produtoModel->update($produto['produto_id'], ['quantidade' => $novoEstoque]);
                    }

                    $produtoPedidoModel->update($produtoPedidoExistente['id'], [
                        'quantidade' => $quantidadeNova,
                        'preco_unitario' => $produtoData['preco']
                    ]);
                } else {
                    $produtoPedidoModel->insert([
                        'pedido_id' => $id,
                        'produto_id' => $produto['produto_id'],
                        'quantidade' => $produto['quantidade'],
                        'preco_unitario' => $produtoData['preco']
                    ]);

                    $novoEstoque = $produtoData['quantidade'] - $produto['quantidade'];
                    $produtoModel->update($produto['produto_id'], ['quantidade' => $novoEstoque]);
                }
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Pedido atualizado com sucesso!',
                'retorno' => ['pedido_id' => $id, 'total_pedido' => $totalPedido]
            ]);
        } catch (\Throwable $th) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage()
            ]);
        }
    }


    public function pedido($id)
    {

        $tokenValido = $this->verificarToken();
        if (!$tokenValido) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'mensagem' => 'Token inválido ou não fornecido.',
                'retorno' => []
            ]);
        }

        try {
            $pedidoModel = new PedidoModel();
            $produtoPedidoModel = new PedidoProdutoModel();
            $produtoModel = new ProdutoModel();
            $clienteModel = new ClienteModel();

            $pedido = $pedidoModel->find($id);
            if (!$pedido) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Pedido não encontrado.'
                ]);
            }

            $cliente = $clienteModel->find($pedido['cliente_id']);
            if (!$cliente) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Cliente não encontrado.'
                ]);
            }

            $produtosPedido = $produtoPedidoModel->where('pedido_id', $id)->findAll();

            $produtosDetalhados = [];
            foreach ($produtosPedido as $produtoPedido) {
                $produto = $produtoModel->find($produtoPedido['produto_id']);
                if ($produto) {
                    $produtosDetalhados[] = [
                        'produto_id' => $produto['id'],
                        'nome' => $produto['nome'],
                        'quantidade' => $produtoPedido['quantidade'],
                        'preco_unitario' => $produtoPedido['preco_unitario'],
                        'subtotal' => $produtoPedido['quantidade'] * $produtoPedido['preco_unitario']
                    ];
                }
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Pedido encontrado.',
                'dados' => [
                    'pedido' => $pedido,
                    'cliente' => $cliente,
                    'produtos' => $produtosDetalhados
                ]
            ]);
        } catch (\Throwable $th) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage()
            ]);
        }
    }

    public function excluir($id)
    {

        $tokenValido = $this->verificarToken();
        if (!$tokenValido) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'mensagem' => 'Token inválido ou não fornecido.',
                'retorno' => []
            ]);
        }

        try {
            $pedidoModel = new PedidoModel();
            $produtoPedidoModel = new PedidoProdutoModel();
            $produtoModel = new ProdutoModel();

            $pedido = $pedidoModel->find($id);
            if (!$pedido) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Pedido não encontrado.'
                ]);
            }

            // Retorna o estoque dos produtos antes de excluir
            $produtos = $produtoPedidoModel->where('pedido_id', $id)->findAll();
            foreach ($produtos as $produto) {
                $produtoData = $produtoModel->find($produto['produto_id']);
                if ($produtoData) {
                    $novoEstoque = $produtoData['quantidade'] + $produto['quantidade'];
                    $produtoModel->update($produto['produto_id'], ['quantidade' => $novoEstoque]);
                }
            }

            // Remove os produtos relacionados ao pedido
            $produtoPedidoModel->where('pedido_id', $id)->delete();

            // Remove o pedido
            $pedidoModel->delete($id);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Pedido excluído com sucesso!'
            ]);
        } catch (\Throwable $th) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage()
            ]);
        }
    }

    public function atualizarStatus($id)
    {

        $tokenValido = $this->verificarToken();
        if (!$tokenValido) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 401,
                'mensagem' => 'Token inválido ou não fornecido.',
                'retorno' => []
            ]);
        }

        try {
            $pedidoModel = new PedidoModel();

            // Buscar o pedido
            $pedido = $pedidoModel->find($id);
            if (!$pedido) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Pedido não encontrado.'
                ]);
            }

            // Obter o novo status do corpo da requisição
            $data = $this->request->getJSON(true);
            if (!isset($data['status']) || empty($data['status'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O campo status é obrigatório.'
                ]);
            }

            // Lista de status permitidos
            $statusPermitidos = ['Em Aberto', 'Pago', 'Cancelado'];

            // Verifica se o status enviado é válido
            if (!in_array($data['status'], $statusPermitidos)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'Status inválido. Os status permitidos são: Em Aberto, Pago, Cancelado.'
                ]);
            }

            // Atualizar o status e a data do updated_at
            $pedidoModel->update($id, [
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s') // Atualiza a data de modificação
            ]);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Status do pedido atualizado com sucesso.',
                'retorno' => [
                    'pedido_id' => $id,
                    'novo_status' => $data['status']
                ]
            ]);
        } catch (\Throwable $th) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage()
            ]);
        }
    }

}
