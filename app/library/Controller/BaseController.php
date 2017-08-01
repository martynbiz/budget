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

        // // get start and end date from the month filter
        // $monthFilter = $container->get('session')->get(SESSION_FILTER_MONTH);
        // $data['month_filter'] = $monthFilter;

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
        $request = $container->get('request');

        $categories = $currentUser->categories()->orderBy('name')->get();

        $this->data = array_merge($this->data, [
            'select_category_options' => $categories,
            'selected_category' => $request->getQueryParam('category'),
        ]);
    }

    /**
     * Include filter in template, attach required arrays/selected to data
     */
    protected function includeTagsFilter()
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();
        $request = $container->get('request');

        $tags = $currentUser->tags()->orderBy('name')->get();

        $this->data = array_merge($this->data, [
            'select_tag_options' => $tags,
            'selected_tag' => $request->getQueryParam('tag'),
        ]);
    }

    /**
     * Include filter in template, attach required arrays/selected to data
     */
    protected function includeMonthFilter()
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();
        $request = $container->get('request');

        $firstTransaction = $currentUser->transactions()
            ->orderBy('purchased_at')
            ->first();
        $firstMonth = ($firstTransaction) ?
            date("Y-m", strtotime($firstTransaction->purchased_at)) :
            date("Y-m");

        $months = [];
        $month = date('Y-m');
        while($month >= $firstMonth) {
            $months[$month] = date("M Y", strtotime($month . '-01'));
            $month = date('Y-m', strtotime('-1 month', strtotime($month)));
        }

        $this->data = array_merge($this->data, [
            'select_month_options' => $months,
            'selected_month' => $request->getQueryParam('month'),
        ]);
    }

    /**
     * Include filter in template, attach required arrays/selected to data
     */
    protected function includeFundFilter()
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();
        $request = $container->get('request');

        $funds = $currentUser->funds()->orderBy('name')->get();

        $this->data = array_merge($this->data, [
            'select_fund_options' => $funds,
            'selected_fund' => $this->currentFund->id,
        ]);
    }
}
