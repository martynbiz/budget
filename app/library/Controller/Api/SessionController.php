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

        // get the POST json
        $json = json_decode(file_get_contents('php://input'), true);

        $email = @$json['email'];
        $pw = @$json['password'];

        // authentice with the email (might even be username, which is fine) and pw
        if ($container->get('auth')->authenticate($email, $pw)) {

            // as authentication has passed, get the user by email OR username
            $user = $container->get('model.user')
                ->where('email', $email)
                ->orWhere('username', $email)
                ->first();

            // // set current user here, so we don't have to query again
            // $this->currentUser = $user;

            // if no token exists, create one
            if (!$token = $user->api_token) {

                $hash = md5(date('YmdHis') . rand(1,1000000));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour', time()));

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
        // remove token from db
        $currentUser = $this->getCurrentUser();
        if ($currentUser && $token = $currentUser->api_token) {
            $token->delete();
        }

        return $this->renderJSON( new stdClass() ); // empty array
    }

}
