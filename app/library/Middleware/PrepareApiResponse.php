<?php
namespace App\Middleware;

use App\Utils;

class PrepareApiResponse extends Base
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

        // attach CORS stuff onto the request EVERYTIME
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'authorization');

error_log(json_encode($response->hasHeader('Access-Control-Allow-Origin')));
error_log(json_encode($response->getHeader('Access-Control-Allow-Origin')));

        $response = $next($request, $response);

        return $response;
    }
}
