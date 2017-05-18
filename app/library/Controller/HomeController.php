<?php
namespace App\Controller;

class HomeController extends BaseController
{
    /**
     * Get categories for the autocomplete
     */
    public function index($request, $response, $args)
    {
        $container = $this->getContainer();
        if($currentUser = $container->get('auth')->getAttributes()) {
            return $this->returnTo( $container->get('router')->pathFor('categories') );
        }

        return $this->render('home/index');
    }

    /**
     *
     */
    public function switchLanguage($request, $response, $args)
    {
        $params = $request->getParams();

        // set language cookie
        setcookie('language', $params['language']);

        return $this->returnTo('/');
    }
}
