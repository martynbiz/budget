<?php
// Application middleware

$app->add(new \App\Middleware\RememberMe($container));
$app->add(new \App\Middleware\Csrf($container));
