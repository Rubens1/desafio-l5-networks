<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/clientes', 'ClientesController::clientes');
$routes->get('/info-cliente/(:num)', 'ClientesController::cliente/$1');
$routes->post('/cadastrar-cliente', 'ClientesController::cadastrar');
$routes->put('/editar-cliente/(:num)', 'ClientesController::editar/$1');
$routes->delete('/excluir-cliente/(:num)', 'ClientesController::excluir/$1');

$routes->get('/produtos', 'ProdutosController::produtos');
$routes->get('/info-produto/(:num)', 'ProdutosController::produto/$1');
$routes->post('/cadastrar-produto', 'ProdutosController::cadastrar');
$routes->put('/editar-produto/(:num)', 'ProdutosController::editar/$1');
$routes->delete('/excluir-produto/(:num)', 'ProdutosController::excluir/$1');

$routes->get('/pedidos', 'PedidosController::pedidos');
$routes->get('/info-pedido/(:num)', 'PedidosController::pedido/$1');
$routes->post('/cadastrar-pedido', 'PedidosController::cadastrar');
$routes->put('/editar-pedido/(:num)', 'PedidosController::editar/$1');
$routes->put('/editar-status/(:num)', 'PedidosController::atualizarStatus/$1');
$routes->delete('/excluir-pedido/(:num)', 'PedidosController::excluir/$1');

$routes->get('/usuarios', 'UsuariosController::usuarios');
$routes->get('/info-produto/(:num)', 'UsuariosController::usuario/$1');
$routes->post('/cadastrar-usuario', 'UsuariosController::cadastrar');
$routes->put('/editar-usuario/(:num)', 'UsuariosController::editar/$1');
$routes->delete('/excluir-usuario/(:num)', 'UsuariosController::excluir/$1');
$routes->post('/login', 'UsuariosController::login');
