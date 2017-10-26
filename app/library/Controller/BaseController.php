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


    //
    public function __construct(Container $container)
    {
        $this->container = $container;
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
    protected function render($file, $data=array())
    {
        $container = $this->getContainer();
        $request = $container->get('request');
        $response = $container->get('response');
        $currentUser = $this->getCurrentUser();

        if ($currentUser = $this->getCurrentUser()) {
            $data['currentUser'] = $currentUser;
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

        // depending on the requested format, we'll return data that way
        $format = $request->getParam('format');

        if ($format == 'json') {

            // put the json in the response object
            // this may not be sufficient for the Eloquent models as we may require
            // to add data from belongsTo or hasMany relationships. In that case,
            // define another
            return $this->renderJSON($data);

        } else {

            // generate the html
            return $this->renderHTML($file, $data);

        }
    }

    /**
     * Render the html and attach to the response
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    protected function renderHTML($file, $data=array())
    {
        $container = $this->getContainer();

        // put the json in the response object
        $response = $container->get('response');
        $html = $container->get('renderer')->render($file, $data);
        $response->getBody()->write($html);

        return $response;
    }

    /**
     * Render the json and attach to the response
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    protected function renderJSON($data=array())
    {
        $container = $this->getContainer();

        // put the json in the response object
        $response = $container->get('response');
        $response->getBody()->write(json_encode($data));

        return $response;
    }

    /**
     * Get the current sign in user user
     * @param Request $request Not really needed here, api uses it though
     * @return User|null
     */
    protected function getCurrentUser($request = null)
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
