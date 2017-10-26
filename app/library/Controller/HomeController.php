<?php
namespace App\Controller;

use App\Utils;

class HomeController extends BaseController
{
    /**
     * Homepage
     */
    public function index($request, $response, $args)
    {
        // if user is logged in, show different page
        if ($currentUser = $this->getCurrentUser()) {
            return $this->dashboard($request, $response, $args);
        } else {
            return $this->welcome($request, $response, $args);
        }
    }

    /**
     * Show welcome screen
     */
    protected function welcome($request, $response, $args)
    {
        return $this->render('home/welcome');
    }

    /**
     * Show dashboard
     */
    protected function dashboard($request, $response, $args)
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        return $this->render('home/dashboard', [

        ]);
    }

    /**
     * Show welcome screen
     */
    public function notFound($request, $response)
    {
        return $this->render('404')->withStatus(404);
    }
}
