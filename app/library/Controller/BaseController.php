<?php
namespace App\Controller;

use Slim\Container;
use App\Model\Transaction;

class BaseController
{
    /**
     * @var Slim\Container
     */
    protected $container;

    /**
     * @var App\Model\User
     */
    protected $currentUser;

    /**
     * @var Store the current fund
     */
    protected $currentFund;

    //
    public function __construct(Container $container) {

        $this->container = $container;

        $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
        $this->currentFund = $container->get('model.fund')->find($fundId);



        // $request = $container->get('request');
        //
        // // // do some stuff if authenticated
        // // if ($currentUser = $this->getCurrentUser()) {
        // //
        // //     $params = $request->getQueryParams();
        // //
        // //     // fund filter
        // //     if ($fundId = $request->getQueryParams('filter__fund_id')) {
        // //         $container->get('session')->set(SESSION_FILTER_MONTH, $fundId)
        // //     }
        // //
        // //     // set (default) fund
        // //     // fund must exist so we attempt to fetch it from the db
        // //     $fund = $container->get('model.fund')->find($fundId) ||
        // //         $fund = $container->get('model.fund')->first();
        // //     $container->get('session')->set(SESSION_FILTER_MONTH, @$fund->id);
        // //
        // //     // month filter
        // //     if ($month = $request->getQueryParams('filter__month')) {
        // //         $container->get('session')->set(SESSION_FILTER_MONTH, $month)
        // //     }
        // //
        // //     // set (default) month in session
        // //     $container->get('session')->get(SESSION_FILTER_MONTH) ||
        // //         $container->get('session')->set(SESSION_FILTER_MONTH, date('Y-m'));
        // //
        // // }
    }

    /**
     * Shorthand method to get dependency from container
     * @param $name
     * @return mixed
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Render the html and attach to the response
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    public function render($file, $data=array())
    {
        $container = $this->getContainer();
        $request = $container->get('request');
        $response = $container->get('response');
        $currentUser = $this->getCurrentUser();

        $data['currentUser'] = $this->getCurrentUser();

        if ($container->has('flash')) {
            $data['messages'] = $container->get('flash')->flushMessages();
        }

        if ($container->has('router')) {
            $data['router'] = $container->get('router');
        }

        if ($container->has('debugbar')) {
            $data['debugbar'] = $container->get('debugbar');
        }

        if ($container->has('csrf')) {
            $request = $container->get('request');
            $data['csrfName'] = $request->getAttribute('csrf_name');
            $data['csrfValue'] = $request->getAttribute('csrf_value');
        }

        // get start and end date from the month filter
        $monthFilter = $container->get('session')->get(SESSION_FILTER_MONTH);
        // $data['transactions_start_date'] = date('Y-m-01', strtotime($monthFilter . '-01'));
        // $data['transactions_end_date'] = date('Y-m-t', strtotime($data['transactions_start_date']));
        $data['month_filter'] = $monthFilter;

        // get the first ever transaction will allow us to set the first month
        // default is this month
        $firstTransaction = $container->get('model.transaction')
            ->orderBy('purchased_at')
            ->first();
        $data['first_month'] = ($firstTransaction) ?
            date("Y-m", strtotime($firstTransaction->purchased_at)) :
            date("Y-m");

        // funds for the fund switcher
        $funds = $currentUser->funds()->orderBy('name', 'asc')->get();
        $data['funds'] = $funds;

        // generate the html
        $html = $container->get('renderer')->render($file, $data);
        $response->getBody()->write($html);

        return $response;
    }

    /**
     * Render the html and attach to the response
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    public function renderJSON($data=array())
    {
        $container = $this->getContainer();

        // put the json in the response object
        $response = $container->get('response');
        $response->getBody()->write(json_encode($data));

        return $response;
    }

    /**
     * Get the current sign in user user
     */
    protected function getCurrentUser()
    {
        // cache current user as a property
        if (! $this->currentUser) {
            $container = $this->getContainer();
            $attributes = $container->get('auth')->getAttributes();
            $this->currentUser =  $container->get('model.user')->where('email', $attributes['email'])->first();
        }

        return $this->currentUser;
    }

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int                 $status The redirect HTTP status code.
     * @return self
     */
    protected function redirect($url, $status = 302)
    {
        $container = $this->getContainer();
        $response = $container->get('response');
        return $response->withRedirect($url, $status);
    }

    /**
     * Pass on the control to another action. Of the same class (for now)
     *
     * @param  string $actionName The redirect destination.
     * @param array $data
     * @return Controller
     * @internal param string $status The redirect HTTP status code.
     */
    public function forward($actionName, $data=array())
    {
        return call_user_func_array(array($this, $actionName), $data);
    }

    /**
     * Will ensure that returnTo url is valid before doing redirect. Otherwise mean
     * people could use out login then redirect to a phishing site
     * @param string $returnTo The returnTo url that we want to check against our white list
     */
    protected function returnTo($returnTo)
    {
        $container = $this->getContainer();
        $settings = $container->get('settings');

        // check returnTo if it's a full domain  (e.g. http://...)
        $host = parse_url($returnTo, PHP_URL_HOST);
        if(strpos($returnTo, '/') !== 0) {
            $found = false;
            $validReturnToArray = (is_array($settings['valid_return_to'])) ? $settings['valid_return_to'] : array($settings['valid_return_to']);
            foreach($validReturnToArray as $validReturnTo) {
                if ($host and preg_match($validReturnTo, $host)) {
                    $found = true;
                }
            }
            if (! $found) {
                throw new InvalidReturnToUrl( $this->get('i18n')->translate('invalid_return_to') );
            }
        }

        return $this->redirect($returnTo);
    }

    protected function findOrCreateCategoryByName($categoryName)
    {
        // if category is empty, we'll return
        if (empty($categoryName)) return;

        $currentUser = $this->getCurrentUser();

        if (!$category = $currentUser->categories()->where('name', $categoryName)->first()) {
            $category = $currentUser->categories()->create([
                'name' => $categoryName,
                'budget' => 0,
                'group_id' => 0,
            ]);
        }

        return $category;
    }

    protected function findOrCreateGroupByName($groupName)
    {
        // if category is empty, we'll return
        if (empty($groupName)) return;

        $currentUser = $this->getCurrentUser();

        if (!$group = $currentUser->groups()->where('name', $groupName)->first()) {
            $group = $currentUser->groups()->create([
                'name' => $groupName,
                'group_id' => 0,
            ]);
        }

        return $group;
    }
}
