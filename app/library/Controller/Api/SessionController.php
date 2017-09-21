<?php
namespace App\Controller\Api;

use App\Exception\InvalidAuthToken;

class SessionController extends ApiController
{
    /**
     * POST /session -- login
     */
    public function post($request, $response, $args)
    {
        // GET and POST
        $params = $request->getParams();
        $container = $this->getContainer();

        // authentice with the email (might even be username, which is fine) and pw
        if ($container->get('auth')->authenticate($params['email'], $params['password'])) {

            // as authentication has passed, get the user by email OR username
            $user = $container->get('model.user')
                ->where('email', $params['email'])
                ->orWhere('username', $params['email'])
                ->first();

            // set current user here, so we don't have to query again
            $this->currentUser = $user;

            // if no token exists, create one
            if (!$token = $user->api_token) {

                $hash = md5(date() . rand(1,1000000));
                $expires = date('Y-m-d H:i:s', strtotime('+1 day', time()));

                $token = $user->api_token()->create([
                    'value' => $hash,
                    'expires_at' => $expires,
                ]);
            }

            // return token
            return $this->renderJSON([
                'token' => $token->value,
            ]);

        } else {

            return $this->handleError('Login failed!', 401);

        }
    }

    public function delete($request, $response, $args)
    {
        // TODO delete token from db, token is passed with request
        //   don't return an error if token not found, just confirm that a given
        //   token no longer exists in the db

        return $this->renderJSON([]); // empty array
    }

    /**
     * Will return JSON as this gives us control over which status code etc, or
     * additional data to return with the error
     */
    protected function handleError($message, $statusCode=400)
    {
        return $this->renderJSON([
            'error' => $message
        ])->withStatus($statusCode);
    }

}
