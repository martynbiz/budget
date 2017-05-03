<?php
namespace App\Middleware;

class RequireAuth
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Attach to routes to ensure protected pages
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (!$this->container->get('auth')->isAuthenticated()) {
            $loginUrl = $this->container->get('router')->pathFor('login');
            return $response->withRedirect($loginUrl, 302);
        }

        $response = $next($request, $response);

        return $response;
    }
}
