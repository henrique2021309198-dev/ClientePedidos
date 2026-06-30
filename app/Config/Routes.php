<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'ClientePedido::index');
$routes->get('totem', 'ClientePedido::totem');
$routes->get('produtos', 'ClientePedido::produtos');
$routes->get('carrinho', 'ClientePedido::carrinho');
$routes->get('checkout', 'ClientePedido::checkout');
$routes->get('nota/(:any)', 'ClientePedido::nota/$1');

$routes->get('api/produtos', 'ClientePedido::apiProdutos');
$routes->get('api/totens', 'ClientePedido::apiTotens');
$routes->post('api/totens', 'ClientePedido::apiCriarTotem');
$routes->post('api/checkout', 'ClientePedido::apiCheckout');
