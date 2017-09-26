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
        // if this is an OPTIONS request, then it may not contain the token in the
        // header. So, we'll just skip the controller stuff and proceed to returning
        // the response with CORS shit
        if ($request->getMethod() != 'OPTIONS') {
            $response = $next($request, $response);
        }

        // attach CORS stuff onto the request EVERYTIME
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'authorization, content-type');



        return $response;
    }
}
