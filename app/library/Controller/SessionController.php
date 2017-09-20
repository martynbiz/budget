<?php
namespace App\Controller;

use App\Exception\InvalidAuthToken;

class SessionController extends BaseController
{
    public function login($request, $response, $args)
    {
        $container = $this->getContainer();

        // if errors found from post, this will contain data
        $params = $request->getParams();

        // if authenticated, return to the homepage
        if ($currentUser = $this->getCurrentUser()) {
            return $response->withRedirect('/');
        }

        // check for remember me cookie.
        if ($rememberMe = $request->getCookieParam('auth_token')) {

            @list($selector, $token) = explode('_', $rememberMe);

            // check validity of token
            try {

                // test 1. find a valid token by selector
                $authToken = $container->get('model.auth_token')->findValidTokenBySelector($selector);
                if (! $authToken) {

                    // maybe the auth_token cookie has expired, and been cleaned from the
                    // database. in any case, we'll just remove it from the client's machine
                    $container->get('auth')->deleteAuthTokenCookie();

                    // throwing an exception will be caught and an error message displayed
                    throw new InvalidAuthToken('Could not automatically sign in with \'Remember me\' token (0). Please login again.');

                }

                // test 2. ensure that this token matches the hashed token we have stored
                if (! $authToken->verifyToken($token)) {

                    // token string is invalid, this could be an attack at someone's user (or not)
                    // remove the token from the database and the auth token and the client cookie
                    $container->get('auth')->deleteAuthTokenCookie();
                    $authToken->delete();

                    // throwing an exception will be caught and an error message displayed
                    throw new InvalidAuthToken('Could not automatically sign in with \'Remember me\' token (1). Please login again.');

                }

                // test 3. get the user for this auth_token
                $user = $authToken->user;
                if (! $user) {

                    // user not found
                    // remove the token from the database and the auth token and the client cookie
                    $container->get('auth')->deleteAuthTokenCookie();
                    $authToken->delete();

                    // throwing an exception will be caught and an error message displayed
                    throw new InvalidAuthToken('Could not automatically sign in with \'Remember me\' token (2). Please login again.');

                }


                // all good :) sign this person in using their auth_token...

                // update remember me with new token
                $container->get('auth')->remember($user);

                // set attributes. valid_attributes will only set the fields we
                // want to be avialable (e.g. not password)
                $container->get('auth')->setAttributes($user->toArray());

                // redirect back to returnTo, or /session (logout page) if not provided
                return $response->withRedirect( $container->get('router')->pathFor('home') );

            } catch (\Exception $e) {

                // delete any token that is associated with this $selector as it's invalid
                $container->get('model.auth_token')->deleteBySelector($selector);

                // this will set an error message and continue to the login form
                $container->get('flash')->addMessage('errors', array(
                    $e->getMessage(),
                ));
            }

            return $this->render('session/login', [
                'params' => $params,
            ]);
        }

        // if the user is authenticated then we will show the logout page which
        // will serve as a landing page, although most typically apps will send
        // a DELETE request which will be handled by the delete() method
        // if the user is not authenticated, the show the login page
        if ($currentUser = $this->getCurrentUser()) {
            return $this->render('session/logout', compact('params'));
        } else {
            return $this->render('session/login', compact('params'));
        }
    }

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

            // if requested (remember me checkbox), create remember me token cookie
            // else, remove the cookie (if exists)
            if (isset($params['remember_me'])) {
                $container->get('auth')->remember($user);
            } else {
                $container->get('auth')->forget($user);
            }

            // set attributes. valid_attributes will only set the fields we
            // want to be avialable (e.g. not password)
            $container->get('auth')->setAttributes($user->toArray());

            // redirect
            return $response->withRedirect( $container->get('router')->pathFor('home') );

        } else {

            // forward them to the login page with errors to try again
            $container->get('flash')->addMessage('errors', array(
                $container->get('i18n')->translate('invalid_username_password'),
            ));

            return $this->login($request, $response, $args);

        }
    }

    public function logout($request, $response, $args)
    {
        // // if authenticated, return to the homepage
        // $container = $this->getContainer();
        // if (!$container->get('auth')->isAuthenticated()) {
        //     return $response->withRedirect('/');
        // }

        return $this->render('session/logout');
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
