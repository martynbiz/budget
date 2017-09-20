<?php
namespace App\Middleware;

class RequireApiToken extends Base
{
    /**
     * Ensures that a (valid?) token has be provided 
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        // TODO this

        $response = $next($request, $response);

        return $response;
    }
}
