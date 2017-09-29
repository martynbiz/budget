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

            // check token is in the request

            $tokenValue = Utils::getApiTokenFromRequest($request); //ltrim(@current($request->getHeader('Authorization')), 'Bearer ');

            if (!$tokenValue) {
                return self::prepareErrorResponse(
                    $response,
                    'Missing required token from request',
                    401);
            }

            // check token is valid (in db, not expired)

            $token = $this->container->get('model.api_token')
                ->where('value', $tokenValue)
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->first();

            if (!$token) {
                return self::prepareErrorResponse(
                    $response,
                    'Invalid token sent with request',
                    401);
            }
        }

        $response = $next($request, $response);

        return $response;
    }

    protected static function prepareErrorResponse($response, $message='', $statusCode=400)
    {
        if (!empty($message)) {
            $response->getBody()->write(json_encode([
                'errors' => $message,
            ]));
        }

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'authorization')
            ->withStatus(401);
    }
}
