<?php
namespace App\Widget;

use App\Utils;

class CategoryStats extends Base
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/category_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        // category stats widget

        $month = date('Y-m'); //$container->get('session')->get(SESSION_FILTER_MONTH);
        list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);

        $userId = $container->get('auth')->getAttributes()['id'];
        $currentUser = $container->get('model.user')->find($userId);

        // first get the categories
        $categories = $currentUser->categories()
            ->with('transactions')
            ->orderBy('group_id')
            ->get();

        // build array with remaining budget
        $budgetStatsData = [
            'categories' => [],
            'total_budgets' => 0,
            'total_remaining_budgets' => 0,

        ];
        foreach ($categories as $category) {

            $transactionsAmount = $category->getTransactionsAmount([
                'start_date' => $startDate,
                'end_date' => $endDate,
                // 'fund' => ..?
            ]);

            if ($category->budget > 0) {
                $remainingBudget = $category->budget - abs($transactionsAmount);

                array_push($budgetStatsData['categories'], [
                    'name' => $category->name,
                    'remaning_budget' => $remainingBudget,
                ]);

                $budgetStatsData['total_budgets']+= $category->budget;
                $budgetStatsData['total_remaining_budgets']+= $remainingBudget;
            }
        }

        usort($budgetStatsData['categories'], function($a, $b) {
            return $a['remaning_budget'] - $b['remaning_budget'];
        });

        $this->data = $budgetStatsData;
    }
}
