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
     * @var string|null
     */
    protected $apiToken;

    /**
     * This overrides the BaseController method
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        // if token is present, we'll set that here so that it is available to
        // all getCurrentUser method calls in API
        $this->apiToken = Utils::getBearerToken();
    }

    /**
     * Get the current user that is linked to the token in the request
     * @return User|null
     */
    protected function getCurrentUser()
    {
    	$container = $this->getContainer();

        // will check if currentUser is empty, will also check if apiToken is set
    	if (!$this->currentUser && !empty($this->apiToken)) {
            $token = $container->get('model.api_token')
    			->where('value', $this->apiToken)
    			->first();

    		$this->currentUser = $token->user;
    	}

    	return $this->currentUser;
    }

    /**
     * Will return JSON as this gives us control over which status code etc, or
     * additional data to return with the error
     */
    protected function handleError($errors, $statusCode=400)
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
