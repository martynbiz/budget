<?php
namespace App\Middleware;

use App\Exception\InvalidCsrfToken;

class RememberMe extends Base
{
    /**
     * Checks the user's remember me token and auto signs in if needed
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $container = $this->container;
        $request = $container->get('request');
        $response = $container->get('response');

        // if they are already on the login page, don't try to redirect them again
        if ($_SERVER['REQUEST_URI'] != $container->get('router')->pathFor('login')) {

            // if user is not logged in, attempt to log them in by "remember me"
            // cookie (if exists)
            if (!$container->get('auth')->isAuthenticated() && $request->getCookieParam('auth_token')) {
                $loginUrl = $container->get('router')->pathFor('login');
                return $response->withRedirect($loginUrl);
            }
        }

        $response = $next($request, $response);

        return $response;
    }
}
