<?php
// Routes

use App\Middleware;

$container = $app->getContainer();

$app->group('', function() use ($app, $container) { // attach middleware: RememberMe, csrf

    // home will use transactions (for now)
    // we add setFilters coz it's required by dashboard - which we serve under / now
    $app->get('/', '\App\Controller\HomeController:index')->setName('home');

    // session routes
    $app->group('/session', function() use ($app) {
        $app->get('/login', '\App\Controller\SessionController:login')->setName('session_login');
        $app->post('/login', '\App\Controller\SessionController:post')->setName('session_login_post');
    });

    // users routes
    $app->group('/users', function() use ($app) {
        $app->get('/register', '\App\Controller\UsersController:register')->setName('users_register');
        $app->post('/register', '\App\Controller\UsersController:post')->setName('users_register_post');
    });

    $app->group('', function() use ($app) { // attach middleware: requireAuth, setFilters

        // session routes
        $app->group('/session', function() use ($app) {
            $app->get('/logout', '\App\Controller\SessionController:logout')->setName('session_logout');
            $app->delete('/logout', '\App\Controller\SessionController:delete')->setName('session_logout_delete');
        });

        // books routes
        $app->group('/books', function() use ($app) {
            $app->get('', '\App\Controller\FundsController:index')->setName('books');
            $app->get('/create', '\App\Controller\FundsController:create')->setName('books_create');
            $app->post('', '\App\Controller\FundsController:post')->setName('books_post');
            $app->get('/{book_id}/edit', '\App\Controller\FundsController:edit')->setName('books_edit');
            $app->put('/{book_id}', '\App\Controller\FundsController:update')->setName('books_update');
            $app->delete('/{book_id}', '\App\Controller\FundsController:delete')->setName('books_delete');
        });

        // users routes
        $app->group('/users', function() use ($app) {
            $app->get('/settings', '\App\Controller\UsersController:edit')->setName('users_edit');
            $app->put('/settings', '\App\Controller\UsersController:update')->setName('users_update');
            $app->delete('/{user_id}', '\App\Controller\UsersController:delete')->setName('users_delete');
        });
    })
    ->add(new Middleware\RequireAuth($container));
})
->add(new Middleware\RememberMe($container))
->add(new Middleware\Csrf($container));
