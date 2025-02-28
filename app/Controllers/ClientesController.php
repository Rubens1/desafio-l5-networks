<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class ClientesController extends BaseController
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

    public function clientes()
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
            $model = new ClienteModel();
            $clientes = $model->findAll();
    
            if (empty($clientes)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Nenhum cliente encontrado.',
                    'retorno' => []
                ]);
            }
    
            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Lista de clientes encontrado com sucesso.',
                'retorno' => $clientes
            ]);
        } catch (\Throwable $th) {
           
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage() 
            ]);
        }
    }
    
    public function cliente($id)
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
            $model = new ClienteModel();

            $cliente = $model->find($id);

            if (!$cliente) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Cliente não encontrado.'
                ]);
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'retorno' => $cliente
            ]);
        } catch (\Throwable $th) {

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $th->getMessage()
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
            $model = new ClienteModel();
            $validacaoController = new ValidacaoController();

            $data = $this->request->getJSON(true);
            $cpfCnpj = $data['cpf_cnpj'];

            if (!isset($cpfCnpj)) {
                $mensagem = (strlen($cpfCnpj) <= 14) ? 'CPF não fornecido.' : 'CNPJ não fornecido.';

                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'message' => $mensagem
                ]);
            }


            $validacao = $validacaoController->validarCpfCnpj($cpfCnpj);

            if ($validacao['status'] === 'erro') {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'message' => $validacao['message']
                ]);
            }

            if (!isset($data['nome_razao_social'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O campo nome_razao_social é obrigatório.'
                ]);
            }

            if ($model->where('cpf_cnpj', $cpfCnpj)->first()) {
                $mensagem = (strlen($cpfCnpj) <= 14) ? 'Já existe um cliente com este CPF.' : 'Já existe um cliente com este CNPJ.';

                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => $mensagem
                ]);
            }

            $model->insert($data);

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 201,
                'mensagem' => 'Cliente cadastrado com sucesso!'
            ]);
        } catch (\Throwable $th) {

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
            $model = new ClienteModel();
            $validacaoController = new ValidacaoController();

            $data = $this->request->getJSON(true);

            if (!isset($data['cpf_cnpj'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'CPF/CNPJ não fornecido.'
                ]);
            }

            $cpfCnpj = $data['cpf_cnpj'];
            $data['updated_at'] = date('Y-m-d H:i:s');

            $validacao = $validacaoController->validarCpfCnpj($cpfCnpj);

            if ($validacao['status'] === 'erro') {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => $validacao['message']
                ]);
            }

            if (!isset($data['nome_razao_social'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 400,
                    'mensagem' => 'O campo nome_razao_social é obrigatório.'
                ]);
            }

            $cliente = $model->find($id);
            if (!$cliente) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Cliente não encontrado.'
                ]);
            }

            $model->update($id, $data);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Cliente atualizado com sucesso!'
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
            $model = new ClienteModel();

            $cliente = $model->find($id);
            if (!$cliente) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => 'Cliente não encontrado.'
                ]);
            }

            $model->delete($id);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Cliente excluído com sucesso!'
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
