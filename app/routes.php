<?php
// Routes

$app->get('/', '\App\Controller\HomeController:index')->setName('home');

// session routes
$app->get('/login', '\App\Controller\SessionController:login')->setName('login');
$app->post('/login', '\App\Controller\SessionController:post')->setName('login_post');
$app->get('/logout', '\App\Controller\SessionController:logout')->setName('logout');
$app->delete('/logout', '\App\Controller\SessionController:delete')->setName('logout_post');

// user routes
$app->get('/register', '\App\Controller\UsersController:register')->setName('register');
$app->post('/register', '\App\Controller\UsersController:post')->setName('register_post');

// transactions routes
$app->get('/transactions/create', '\App\Controller\TransactionsController:create')->setName('transactions_create');
$app->post('/transactions', '\App\Controller\TransactionsController:post')->setName('transactions_post');
$app->get('/transactions/{transaction_id}/edit', '\App\Controller\TransactionsController:edit')->setName('transactions_edit');
$app->put('/transactions/{transaction_id}', '\App\Controller\TransactionsController:update')->setName('transactions_update');
$app->delete('/transactions/{transaction_id}', '\App\Controller\TransactionsController:delete')->setName('transactions_delete');

// funds routes
$app->get('/funds', '\App\Controller\FundsController:index')->setName('funds');
$app->get('/funds/create', '\App\Controller\FundsController:create')->setName('funds_create');
$app->post('/funds', '\App\Controller\FundsController:post')->setName('funds_post');
$app->get('/funds/{fund_id}/edit', '\App\Controller\FundsController:edit')->setName('funds_edit');
$app->put('/funds/{fund_id}', '\App\Controller\FundsController:update')->setName('funds_update');
$app->delete('/funds/{fund_id}', '\App\Controller\FundsController:delete')->setName('funds_delete');
