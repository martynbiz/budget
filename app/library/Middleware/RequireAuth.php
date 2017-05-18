<?php
namespace App\Middleware;

class RequireAuth extends Base
{
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
        $attributes = $this->container->get('auth')->getAttributes();
        $currentUser =  $this->container->get('model.user')->where('id', $attributes['id'])->first();

        if (!$currentUser) {
            $loginUrl = $this->container->get('router')->pathFor('login');
            return $response->withRedirect($loginUrl, 302);
        }

        $response = $next($request, $response);

        return $response;
    }
}
