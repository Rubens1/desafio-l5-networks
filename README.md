
# 🚀 Desafio Back-end L5 NETWORKS

## 📌 Introdução
Este projeto é um sistema de cadastro de pedidos, onde cada venda está associada a um cliente e a produtos específicos. Para garantir a segurança e controle de acesso, todas as operações, como cadastro, listagem, edição, exclusão e visualização de registros individuais, exigem permissões apropriadas.

O sistema foi desenvolvido utilizando **CodeIgniter 4** com **PHP 8.1** e implementa autenticação JWT através da biblioteca **firebase/php-jwt**.

---

## 🏗️ Arquitetura do Projeto
O projeto segue a arquitetura MVC (Model-View-Controller), garantindo organização e separação de responsabilidades.

### 📂 Estrutura de Diretórios
```
├── app
│   ├── Controllers
│   │   ├── UsuariosController.php
│   │   ├── ClientesController.php
│   │   ├── ProdutosController.php
│   │   ├── PedidosController.php
│   │   ├── PedidosProdutosController.php
│   │   ├── ValidacaoController.php
│   ├── Models
│   │   ├── UsuarioModel.php
│   │   ├── ClienteModel.php
│   │   ├── ProdutoModel.php
│   │   ├── PedidoModel.php
│   │   ├── PedidosProdutoModel.php
│   ├── Views
│   ├── Config
│   ├── Database
│       ├── 2025-02-26-010821_CreateClientesTable.php
│       ├── 2025-02-26-010907_CreateProdutosTable.php
│       ├── 2025-02-26-010920_CreatePedidosTable.php
│       ├── 2025-02-26-010938_CreatePedidosProdutosTable.php
│       ├── 2025-02-27-030357_CreateUsuariosTable.php
├── public
├── vendor
├── .env
├── composer.json
├── README.md
```

---

## 🔑 Autenticação e Segurança
- O sistema implementa **autenticação baseada em tokens JWT**, garantindo que apenas usuários autenticados possam acessar os endpoints protegidos.
- A biblioteca **firebase/php-jwt** é utilizada para a geração e validação dos tokens JWT.
- O middleware de autenticação impede o acesso não autorizado aos controllers do sistema.

---

## 📌 Funcionalidades Principais

### 🧑‍💼 Gerenciamento de Usuários
- Cadastro, listagem, edição, informação do usuário por id e exclusão de usuários.
- Autenticação JWT para controle de acesso.
- Validação de permissões para operações CRUD.

### 👥 Clientes
- Cadastro, edição, exclusão, informação de cliente por id e listagem de clientes.
- Associação de pedidos a clientes.

### 📦 Produtos
- Cadastro, edição, exclusão, informação do produto por id e listagem de produtos.
- Relacionamento entre produtos e pedidos.

### 📄 Pedidos
- Cadastro, edição, exclusão, informação de pedido por id e listagem de pedidos.
- Associação de pedidos a clientes.
- Controle de status do pedido.

### 🔄 Pedidos e Produtos
- Tabela intermediária para vincular pedidos a múltiplos produtos
- Associação a pedidos e produtos.
- Controle de quantidade e valores por produto dentro do pedido.

---

## ⚙️ Tecnologias Utilizadas
- **PHP 8.1**
- **CodeIgniter 4**
- **MySQL** (Banco de dados relacional)
- **JWT (JSON Web Token)** para autenticação
- **Composer** para gerenciamento de dependências

---

## 📜 Instalação e Configuração
### 📥 Clonando o repositório
```bash
git clone https://github.com/Rubens1/desafio-l5-networks.git
cd desafio-l5-networks
```

### 📦 Instalando dependências
```bash
composer install
```

### 🔧 Configurando variáveis de ambiente
Renomeie o arquivo `.env.example` para `.env` e configure as credenciais do banco de dados e chave JWT:
```env
database.default.hostname = localhost
database.default.database = networks
database.default.username = root
database.default.password = senha
database.default.DBDriver = MySQLi
CI_ENVIRONMENT = development
```

### 🔄 Executando Migrations
```bash
php spark migrate
```

### ▶️ Iniciando o servidor
```bash
php spark serve
```
O sistema estará disponível em `http://localhost:8081`

---

🚀 Desenvolvido por **Rubens Nogueira** para **L5 NETWORKS** 💻


