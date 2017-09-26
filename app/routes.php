<?php
// Routes

use App\Middleware;

$container = $app->getContainer();

$app->group('', function() use ($app, $container) { // attach middleware: RememberMe, csrf

    // home will use transactions (for now)
    // we add setFilters coz it's required by dashboard - which we serve under / now
    $app->get('/', '\App\Controller\HomeController:index')->setName('home')->add(new Middleware\SetFilters($container));

    // session routes
    $app->group('/session', function() use ($app) {
        $app->get('/login', '\App\Controller\SessionController:login')->setName('session_login');
        $app->post('/login', '\App\Controller\SessionController:post')->setName('session_login_post');
    });

    // users routes
    $app->group('/users', function() use ($app) {
        $app->get('/register', '\App\Controller\UsersController:register')->setName('users_register');
        $app->post('/register', '\App\Controller\UsersController:post')->setName('users_register_post');
        $app->post('/switch-language', '\App\Controller\HomeController:switchLanguage')->setName('users_switch_language');
    });

    $app->group('', function() use ($app) { // attach middleware: requireAuth, setFilters

        // session routes
        $app->group('/session', function() use ($app) {
            $app->get('/logout', '\App\Controller\SessionController:logout')->setName('session_logout');
            $app->delete('/logout', '\App\Controller\SessionController:delete')->setName('session_logout_delete');
        });

        // funds routes
        $app->group('/funds', function() use ($app) {
            $app->get('', '\App\Controller\FundsController:index')->setName('funds');
            $app->get('/create', '\App\Controller\FundsController:create')->setName('funds_create');
            $app->post('', '\App\Controller\FundsController:post')->setName('funds_post');
            $app->get('/{fund_id}/edit', '\App\Controller\FundsController:edit')->setName('funds_edit');
            $app->put('/{fund_id}', '\App\Controller\FundsController:update')->setName('funds_update');
            $app->delete('/{fund_id}', '\App\Controller\FundsController:delete')->setName('funds_delete');

            $app->post('/switch', '\App\Controller\FundsController:switch')->setName('funds_switch');
        });

        $app->group('/transactions', function() use ($app) {
            $app->get('', '\App\Controller\TransactionsController:index')->setName('transactions');
            $app->get('/create', '\App\Controller\TransactionsController:create')->setName('transactions_create');
            $app->post('', '\App\Controller\TransactionsController:post')->setName('transactions_post');
            $app->get('/{transaction_id}/edit', '\App\Controller\TransactionsController:edit')->setName('transactions_edit');
            $app->put('/{transaction_id}', '\App\Controller\TransactionsController:update')->setName('transactions_update');
            $app->delete('/{transaction_id}', '\App\Controller\TransactionsController:delete')->setName('transactions_delete');
        });

        // categories routes
        $app->group('/categories', function() use ($app) {
            $app->get('', '\App\Controller\CategoriesController:index')->setName('categories');
            $app->get('/create', '\App\Controller\CategoriesController:create')->setName('categories_create');
            $app->post('', '\App\Controller\CategoriesController:post')->setName('categories_post');
            $app->get('/{category_id}/edit', '\App\Controller\CategoriesController:edit')->setName('categories_edit');
            $app->put('/{category_id}', '\App\Controller\CategoriesController:update')->setName('categories_update');
            $app->delete('/{category_id}', '\App\Controller\CategoriesController:delete')->setName('categories_delete');
        });

        // data routes
        $app->group('/data', function() use ($app) {
            $app->get('/categories', '\App\Controller\DataController:categories')->setName('data_categories');
            $app->get('/groups', '\App\Controller\DataController:groups')->setName('data_groups');
            $app->get('/tags', '\App\Controller\DataController:tags')->setName('data_tags');

            // highcharts
            $app->get('/expenses', '\App\Controller\DataController:expenses')->setName('data_groups');
        });

        // groups routes
        $app->group('/groups', function() use ($app) {
            $app->get('', '\App\Controller\GroupsController:index')->setName('groups');
            $app->get('/create', '\App\Controller\GroupsController:create')->setName('groups_create');
            $app->post('', '\App\Controller\GroupsController:post')->setName('groups_post');
            $app->get('/{group_id}/edit', '\App\Controller\GroupsController:edit')->setName('groups_edit');
            $app->put('/{group_id}', '\App\Controller\GroupsController:update')->setName('groups_update');
            $app->delete('/{group_id}', '\App\Controller\GroupsController:delete')->setName('groups_delete');
        });

        // tags routes
        $app->group('/tags', function() use ($app) {
            $app->get('', '\App\Controller\TagsController:index')->setName('tags');
            $app->get('/create', '\App\Controller\TagsController:create')->setName('tags_create');
            $app->post('', '\App\Controller\TagsController:post')->setName('tags_post');
            $app->get('/{tag_id}/edit', '\App\Controller\TagsController:edit')->setName('tags_edit');
            $app->put('/{tag_id}', '\App\Controller\TagsController:update')->setName('tags_update');
            $app->delete('/{tag_id}', '\App\Controller\TagsController:delete')->setName('tags_delete');
        });

        // users routes
        $app->group('/users', function() use ($app) {
            $app->get('/settings', '\App\Controller\UsersController:edit')->setName('users_edit');
            $app->put('/settings', '\App\Controller\UsersController:update')->setName('users_update');
            $app->delete('/{user_id}', '\App\Controller\UsersController:delete')->setName('users_delete');
        });
    })
    ->add(new Middleware\RequireAuth($container))
    ->add(new Middleware\SetFilters($container));
})
->add(new Middleware\RememberMe($container))
->add(new Middleware\Csrf($container));



/**
 * App routes
 * These require a token to be present as they don't rely on PHPSESSID
 */

$app->group('', function() use ($app, $container) { // attach middleware: PrepareApiResponse

    // session routes
    $app->map(['POST', 'OPTIONS'], '/api/session/login', '\App\Controller\Api\SessionController:post')->setName('api_session_post');
    $app->map(['DELETE', 'OPTIONS'], '/api/session/logout', '\App\Controller\Api\SessionController:delete')->setName('api_session_delete');

    $app->group('/api', function() use ($app) { // attach middleware: RequireApiToken

        // funds routes
        $app->group('/funds', function() use ($app) {
            $app->map(['GET', 'OPTIONS'], '', '\App\Controller\Api\FundsController:index')->setName('api_funds');
            $app->map(['POST', 'OPTIONS'], '/create', '\App\Controller\Api\FundsController:post')->setName('api_funds_post');
            $app->map(['PUT', 'OPTIONS'], '/{fund_id}/edit', '\App\Controller\Api\FundsController:update')->setName('api_funds_update');
            $app->map(['DELETE', 'OPTIONS'], '/{fund_id}/delete', '\App\Controller\Api\FundsController:delete')->setName('api_funds_delete');
        });
    })
    ->add(new Middleware\RequireApiToken($container));
})
->add(new Middleware\PrepareApiResponse($container));
