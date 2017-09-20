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

        // // authentice with the email (might even be username, which is fine) and pw
        // if ($container->get('auth')->authenticate($params['email'], $params['password'])) {

            // // as authentication has passed, get the user by email OR username
            // $user = $container->get('model.user')
            //     ->where('email', $params['email'])
            //     ->orWhere('username', $params['email'])
            //     ->first();

            // TODO create token and store in db with $user id
            $token = 'qwertyuiop1234567890';

            // redirect
            return $this->renderJSON([
                'token' => $token,
            ]);

        // } else {
        //
        //     // TODO handle invalid login attempt
        //
        // }
    }

    public function delete($request, $response, $args)
    {
        // combine GET and POST params
        $params = $request->getParams();
        $container = $this->getContainer();

        // also, delete any auth_token we have for the user and cookie
        if ($currentUser = $this->getCurrentUser()) {
            $container->get('auth')->forget($currentUser);
        } else { // just delete cookie then - if exists
            $container->get('auth')->deleteAuthTokenCookie();
        }

        // this will effective end the "session" by clearning out the session vars
        $container->get('auth')->clearAttributes();

        // redirect back to returnTo, or /session (logout page) if not provided
        return $response->withRedirect('/');
    }

}
