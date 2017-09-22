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
    /**
     * @var string|null
     */
    protected $apiToken;

    public function __construct(Container $container)
    {
        $this->container = $container;

        // if token is present, we'll set that here so that it is available to
        // all getCurrentUser method calls in API
        $this->apiToken = $this->getBearerToken();
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
     * get access token from header
     * */
    function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Get hearder Authorization
     * */
    function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
}
