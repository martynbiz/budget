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
            return $this->returnTo( $container->get('router')->pathFor('transactions') );
        }

        return $this->render('home/index');
    }
}
