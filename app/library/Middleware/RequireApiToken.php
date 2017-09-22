<?php
namespace App\Middleware;

use App\Utils;

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

        // seems options request doesn't send auth header
        if ($request->getMethod() !== 'OPTIONS') {
            $token = Utils::getBearerToken();

            if (!$token) {
                return $response->withStatus(401);
            }
        }

        $response = $next($request, $response);

        return $response;
    }
}
