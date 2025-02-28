<?php

namespace App\Controllers;

use App\Models\ProdutoModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ProdutosController extends ResourceController
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

    public function produtos()
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
            $model = new ProdutoModel();
            $produtos = $model->findAll();

            if (empty($produtos)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Nenhum produto encontrado.',
                    'retorno' => []
                ]);
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Lista de produtos encontrado com sucesso.',
                'retorno' => $produtos
            ]);
        } catch (\Exception $e) {
           
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage()
            ]);
        }
    }

    public function produto($id)
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
            $model = new ProdutoModel();
            $produto = $model->find($id);

            if (!$produto) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'retorno' => $produto
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage()
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
            $model = new ProdutoModel();
            $data = $this->request->getJSON(true);

            if (!isset($data['nome']) || empty($data['nome'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O campo nome é obrigatório.'
                ]);
            }

            if (!isset($data['preco']) || !is_numeric($data['preco']) || $data['preco'] <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O preço deve ser um número positivo.'
                ]);
            }

            if (!isset($data['quantidade']) || !is_numeric($data['quantidade']) || $data['quantidade'] < 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'A quantidade deve ser um número inteiro maior ou igual a zero.'
                ]);
            }

            $model->insert($data);

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 201,
                'mensagem' => 'Produto cadastrado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage()
            ]);
        }
    }

    public function editar($id)
    {
        try {
            $model = new ProdutoModel();
            $data = $this->request->getJSON(true);
            $data['updated_at'] = date('Y-m-d H:i:s');

            $produto = $model->find($id);
            if (!$produto) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }

            if (isset($data['preco']) && (!is_numeric($data['preco']) || $data['preco'] <= 0)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O preço deve ser um número positivo.'
                ]);
            }

            if (isset($data['quantidade']) && (!is_numeric($data['quantidade']) || $data['quantidade'] < 0)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'A quantidade deve ser um número inteiro maior ou igual a zero.'
                ]);
            }

            $model->update($id, $data);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Produto atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage()
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
            $model = new ProdutoModel();
            $produto = $model->find($id);

            if (!$produto) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }

            $model->delete($id);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Produto excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage()
            ]);
        }
    }
}
