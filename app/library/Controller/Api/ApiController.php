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
use App\Utils;

class ApiController extends BaseController
{
    /**
     * Get the current user that is linked to the token in the request
     * @param Request $request Used to get the authorization header for the api token
     * @return User|null
     */
    protected function getCurrentUser($request = null)
    {
    	$container = $this->getContainer();

        $apiToken = Utils::getApiTokenFromRequest($request);

        // will check if currentUser is empty, will also check if apiToken is set
    	if (!$this->currentUser && !empty($apiToken)) {
            $token = $container->get('model.api_token')
    			->where('value', $apiToken)
    			->first();

    		$this->currentUser = $token->user;
    	}

    	return $this->currentUser;
    }

    /**
     * Will return JSON as this gives us control over which status code etc, or
     * additional data to return with the error
     */
    protected function handleError($errors, $statusCode=500)
    {
        // convert error string to array
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        return $this->renderJSON([
            'errors' => $errors
        ])->withStatus($statusCode);
    }
}
