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
        $params = $request->getParams();
        $container = $this->getContainer();

        // authentice with the email (might even be username, which is fine) and pw
        if ($container->get('auth')->authenticate(@$params['email'], @$params['password'])) {

            // as authentication has passed, get the user by email OR username
            $user = $container->get('model.user')
                ->where('email', $params['email'])
                ->orWhere('username', $params['email'])
                ->first();

            // get api token for this email
            $token = $user->api_token;

            // if no token exists, create one
            if (!$token || $token->hasExpired()) {

                $hash = md5(date('YmdHis') . rand(1,1000000));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour', time()));

                $params = [
                    'value' => $hash,
                    'expires_at' => $expires,
                ];

                if ($token) { // token exists, but has expired

                    $token = $token()->update($params);

                } else { // no token exists, create a new one

                    $token = $user->api_token()->create($params);

                }
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
        // remove token from db
        $currentUser = $this->getCurrentUser($request);
        if ($currentUser && $token = $currentUser->api_token) {
            $token->delete();
        }

        return $this->renderJSON( json_decode('{}') ); // empty array
    }

}
