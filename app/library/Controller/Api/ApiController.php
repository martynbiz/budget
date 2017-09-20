<?php
/**
 * This will include functionality that serves data reliant upon a token
 * This will also change the functionality of getCurrentUser based on the
 * user tied to that token
 * Checks will be made for the token prior to data
 */

namespace App\Controller\Api;

use Slim\Container;

use App\Controller\BaseController;
use App\Model\Transaction;

class ApiController extends BaseController
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the current user that is linked to the token in the request
     */
    protected function getCurrentUser()
    {
        // TODO this
        if (! $this->currentUser) {
            $container = $this->getContainer();
            $this->currentUser =  $container->get('model.user')->first();
        }

        return $this->currentUser;
    }
}
