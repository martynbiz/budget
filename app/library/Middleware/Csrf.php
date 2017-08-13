<?php
namespace App\Middleware;

use App\Exception\InvalidCsrfToken;

class Csrf extends Base
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
        // $container = $this->container;
        //
        // // ensure csrf name is written
        // if (!$csrfName = $container->get('session')->get('csrf_name')) {
        //     $container->get('session')->set('csrf_name', md5(rand()));
        // }
        // $csrfName = $container->get('session')->get('csrf_name');
        //
        // // ensure csrf value is written
        // if (!$csrfValue = $container->get('session')->get('csrf_value')) {
        //     $container->get('session')->set('csrf_value', md5(rand()));
        // }
        // $csrfValue = $container->get('session')->get('csrf_value');
        //
        // // check csrf token for non-GET requests
        // if ($request->getMethod() != 'GET') {
        //     if ($csrfValue != $request->getParam($csrfName)) {
        //         throw new InvalidCsrfToken;
        //     } else {
        //         // update the name/value
        //         $container->get('session')->set('csrf_name', md5(rand()));
        //         $container->get('session')->set('csrf_value', md5(rand()));
        //     }
        // }

        $response = $next($request, $response);

        return $response;
    }
}
