<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class UsuariosController extends ResourceController
{
    use ResponseTrait;

    private $key = "uma_chave_super_secreta_bem_grande_para_maior_seguranca";

    public function cadastrar()
    {
        $model = new UsuarioModel();
        $data = $this->request->getJSON(true);

        if (!isset($data['nome']) || !isset($data['usuario']) || !isset($data['senha'])) {
            return $this->fail('Nome, usuario e senha são obrigatórios.', 400);
        }

        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        $model->insert($data);

        return $this->respondCreated(["mensagem" => "Usuário cadastrado com sucesso!"]);
    }

    public function login()
    {
            try{
            $model = new UsuarioModel();
            $data = $this->request->getJSON(true);

            $usuario = $model->where('usuario', $data['usuario'])->first();
            if (!$usuario || !password_verify($data['senha'], $usuario['senha'])) {
                return $this->failUnauthorized("Credenciais inválidas.");
            }

            $payload = [
                'iat' => time(),
                'exp' => time() + 3600,
                'sub' => $usuario['id'],
                'nome' => $usuario['nome']
            ];

            $token = JWT::encode($payload, $this->key, 'HS256');
            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'mensagem' => 'Login feito com sucesso.',
                "token" => $token
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage() 
            ]);
        }
    }

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

    public function usuarios()
    {
        try {
            if (!$this->verificarToken()) {
                return $this->failUnauthorized("Acesso negado.");
            }

            $model = new UsuarioModel();
            return $this->respond($model->findAll());
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

        if (!$this->verificarToken()) {
            return $this->failUnauthorized("Acesso negado.");
        }
        
        try {

            $model = new UsuarioModel();
            $data = $this->request->getJSON(true);
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (isset($data['senha'])) {
                $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
            }

            $model->update($id, $data);
            return $this->response->setStatusCode(200)->setJSON([
                "status" => 200,
                "mensagem" => "Usuário atualizado com sucesso!"
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
        try {
            if (!$this->verificarToken()) {
                return $this->failUnauthorized("Acesso negado.");
            }
    
            $model = new UsuarioModel();
            $usuario = $model->find($id);
    
            if (!$usuario) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 404,
                    'mensagem' => "Usuário não encontrado."
                ]);
            }
    
            $model->delete($id);
    
            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                "mensagem" => "Usuário excluído com sucesso!"
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage()
            ]);
        }
    }    

    public function usuario($id)
    {
        try {
               
            if (!$this->verificarToken()) {
                return $this->failUnauthorized("Acesso negado.");
            }

            $model = new UsuarioModel();
            $usuario = $model->find($id);
            if (!$usuario) {
                return $this->failNotFound("Usuário não encontrado.");
            }
            return $this->respond($usuario);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'mensagem' => 'Erro interno no servidor.',
                'retorno' => $e->getMessage() 
            ]);
        }
    }
}