<?php
// Routes

$container = $app->getContainer();

$requireAuth = new \App\Middleware\RequireAuth($container);

// home will use transactions (for now)
$app->get('/', '\App\Controller\HomeController:index');
$app->post('/switch-language', '\App\Controller\HomeController:switchLanguage');

// session routes
$app->get('/login', '\App\Controller\SessionController:login')->setName('login');
$app->post('/login', '\App\Controller\SessionController:post')->setName('login_post');
$app->get('/logout', '\App\Controller\SessionController:logout')->setName('logout');
$app->delete('/logout', '\App\Controller\SessionController:delete')->setName('logout_post');

// user routes
$app->get('/register', '\App\Controller\UsersController:register')->setName('register');
$app->post('/register', '\App\Controller\UsersController:post')->setName('register_post');

$app->group('/transactions', function() {
    $this->get('', '\App\Controller\TransactionsController:index')->setName('transactions');
    $this->get('/create', '\App\Controller\TransactionsController:create')->setName('transactions_create');
    $this->post('', '\App\Controller\TransactionsController:post')->setName('transactions_post');
    $this->get('/{transaction_id}/edit', '\App\Controller\TransactionsController:edit')->setName('transactions_edit');
    $this->put('/{transaction_id}', '\App\Controller\TransactionsController:update')->setName('transactions_update');
    $this->delete('/{transaction_id}', '\App\Controller\TransactionsController:delete')->setName('transactions_delete');
})->add($requireAuth);

// funds routes
$app->group('/funds', function() {
    $this->get('', '\App\Controller\FundsController:index')->setName('funds');
    $this->get('/create', '\App\Controller\FundsController:create')->setName('funds_create');
    $this->post('', '\App\Controller\FundsController:post')->setName('funds_post');
    $this->get('/{fund_id}/edit', '\App\Controller\FundsController:edit')->setName('funds_edit');
    $this->put('/{fund_id}', '\App\Controller\FundsController:update')->setName('funds_update');
    $this->delete('/{fund_id}', '\App\Controller\FundsController:delete')->setName('funds_delete');

    $this->post('/switch', '\App\Controller\FundsController:switch')->setName('funds_switch');
})->add($requireAuth);

// transactions routes
$app->group('/categories', function() {
    $this->get('', '\App\Controller\CategoriesController:index')->setName('categories');
    $this->get('/create', '\App\Controller\CategoriesController:create')->setName('categories_create');
    $this->post('', '\App\Controller\CategoriesController:post')->setName('categories_post');
    $this->get('/{category_id}/edit', '\App\Controller\CategoriesController:edit')->setName('categories_edit');
    $this->put('/{category_id}', '\App\Controller\CategoriesController:update')->setName('categories_update');
    $this->delete('/{category_id}', '\App\Controller\CategoriesController:delete')->setName('categories_delete');
})->add($requireAuth);

// transactions routes
$app->group('/data', function() {
    $this->get('/categories', '\App\Controller\DataController:categories')->setName('data_categories');
    $this->get('/groups', '\App\Controller\DataController:groups')->setName('data_groups');

    // highcharts
    $this->get('/expenses', '\App\Controller\DataController:expenses')->setName('data_groups');
})->add($requireAuth);

// transactions routes
$app->group('/groups', function() {
    $this->get('', '\App\Controller\GroupsController:index')->setName('groups');
    $this->get('/create', '\App\Controller\GroupsController:create')->setName('groups_create');
    $this->post('', '\App\Controller\GroupsController:post')->setName('groups_post');
    $this->get('/{group_id}/edit', '\App\Controller\GroupsController:edit')->setName('groups_edit');
    $this->put('/{group_id}', '\App\Controller\GroupsController:update')->setName('groups_update');
    $this->delete('/{group_id}', '\App\Controller\GroupsController:delete')->setName('groups_delete');
})->add($requireAuth);
