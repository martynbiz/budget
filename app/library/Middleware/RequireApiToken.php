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
        // return $response->withStatus(401);

        $response = $next($request, $response);

        return $response;
    }


    // // TODO this is used in ApiController too, we might wanna put this in a trait to share
    //
    // /**
    //  * get access token from header
    //  * */
    // function getBearerToken()
    // {
    //     $headers = $this->getAuthorizationHeader();
    //     // HEADER: Get the access token from the header
    //     if (!empty($headers)) {
    //         if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
    //             return $matches[1];
    //         }
    //     }
    //     return null;
    // }
    //
    // /**
    //  * Get hearder Authorization
    //  * */
    // function getAuthorizationHeader()
    // {
    //     $headers = null;
    //     if (isset($_SERVER['Authorization'])) {
    //         $headers = trim($_SERVER["Authorization"]);
    //     }
    //     else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
    //         $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    //     } elseif (function_exists('apache_request_headers')) {
    //         $requestHeaders = apache_request_headers();
    //         // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
    //         $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
    //         //print_r($requestHeaders);
    //         if (isset($requestHeaders['Authorization'])) {
    //             $headers = trim($requestHeaders['Authorization']);
    //         }
    //     }
    //     return $headers;
    // }
}
