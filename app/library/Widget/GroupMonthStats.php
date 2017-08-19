<?php
namespace App\Widget;

use App\Utils;

class GroupMonthStats extends Base
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/group_month_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        // category stats widget

        $month = date('Y-m'); //$container->get('session')->get(SESSION_FILTER_MONTH);
        list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);

        $userId = $container->get('auth')->getAttributes()['id'];
        $currentUser = $container->get('model.user')->find($userId);

        // if we select categories instead of groups, we can eager load trans
        $categories = $currentUser->categories()
            ->with('transactions')
            ->with('group')
            ->get();

        $data = [];
        $months = [
            date("Y-m") => 0,
            date("Y-m",strtotime("-1 month")) => 0,
            date("Y-m",strtotime("-2 month")) => 0,
            date("Y-m",strtotime("-3 month")) => 0,
        ];
        foreach ($categories as $category) {

            // $transactionsAmount = $category->getTransactionsAmount([
            //     'start_date' => $startDate,
            //     'end_date' => $endDate,
            //     'fund_id' => $container->get('session')->get(SESSION_FILTER_FUND)
            // ]);

            $groupName = $category->group->name;
            if (empty($groupName)) $groupName = 'Uncategorized';

            // ensure that group is set
            if (!isset($data[$groupName])) {
                $data[$groupName] = $months;
            }

            foreach (array_keys($months) as $month) {
                list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);

                $data[$groupName][$month]+= $category->getTransactionsAmount([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'fund' => $container->get('session')->get(SESSION_FILTER_FUND)
                ]);
            }
        }

        // sort by group name
        ksort($data);

        $this->data = $data;
    }
}
