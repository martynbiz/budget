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

    /**
     * Data so that we can append it from any function before render
     * @var array
     */
    protected $data = [];


    //
    public function __construct(Container $container)
    {
        $this->container = $container;

        $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
        $this->currentFund = $container->get('model.fund')->find($fundId);
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

        // take data as it's is til now
        $data = array_merge($this->data, $data);

        if ($currentUser = $this->getCurrentUser()) {

            $data['currentUser'] = $currentUser;

            // funds for the fund switcher
            $funds = $currentUser->funds()->orderBy('name', 'asc')->get();
            $data['all_funds'] = $funds;

            $data['current_fund'] = $this->currentFund;
        }

        if ($container->has('flash')) {
            $data['messages'] = $container->get('flash')->flushMessages();
        }

        if ($container->has('router')) {
            $data['router'] = $container->get('router');
        }

        if ($container->has('debugbar')) {
            $data['debugbar'] = $container->get('debugbar');
        }

        $data['csrf_name'] = $container->get('session')->get('csrf_name');
        $data['csrf_value'] = $container->get('session')->get('csrf_value');

        // get start and end date from the month filter
        $monthFilter = $container->get('session')->get(SESSION_FILTER_MONTH);
        $data['month_filter'] = $monthFilter;

        // get the first ever transaction will allow us to set the first month
        // default is this month
        $firstTransaction = $container->get('model.transaction')
            ->orderBy('purchased_at')
            ->first();
        $data['first_month'] = ($firstTransaction) ?
            date("Y-m", strtotime($firstTransaction->purchased_at)) :
            date("Y-m");

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
     * Include filter in template, attach required arrays/selected to data
     */
    protected function includeCategoriesFilter()
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();
        $params = $container->get('request')->getQueryParams();

        $categories = $currentUser->categories()->orderBy('name')->get();

        $this->data = array_merge($this->data, [
            'filter_categories' => $categories,
            'filtered_category' => @$params['filter__category'],
            'show_category_filter' => true,
        ]);
    }

    /**
     * Include filter in template, attach required arrays/selected to data
     */
    protected function includeMonthFilter()
    {
        $container = $this->getContainer();

        $monthFilter = $container->get('session')->get(SESSION_FILTER_MONTH);

        $this->data = array_merge($this->data, [
            'filtered_month' => $monthFilter,
            'show_month_filter' => true,
        ]);
    }

    /**
     * Include filter in template, attach required arrays/selected to data
     */
    protected function includeFundFilter()
    {
        $this->data = array_merge($this->data, [
            'filtered_fund' => $this->currentFund->id,
            'show_fund_filter' => true,
        ]);
    }
}
