<?php
namespace App\Controller;

use App\Utils;

class HomeController extends BaseController
{
    /**
     * @var array of WidgetInterface
     */
    protected $widgets = [
        'recent_transactions' => '\\App\\Widget\\RecentTransactions',
        'monthly_stats' => '\\App\\Widget\\MonthlyStats',
        'yearly_stats' => '\\App\\Widget\\YearlyStats',
        // 'tag_stats' => '\\App\\Widget\\TagStats',
        // 'category_stats' => '\\App\\Widget\\CategoryStats',
    ];

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

        // initiate all widgets for this user
        $widgets = $this->widgets;
        array_walk($widgets, function(&$widget, $key) use ($container) {
            $widget = new $widget($container);
        });

        // filters
        $this->includeFundFilter();
        // $this->includeMonthFilter();
        // $this->includeCategoriesFilter();

        return $this->render('home/dashboard', [
            'widgets' => $widgets,
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
