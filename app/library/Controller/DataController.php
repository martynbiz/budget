<?php
namespace App\Controller;

class DataController extends BaseController
{
    /**
     * Get categories for the autocomplete
     */
    public function categories($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();
        $categories = $currentUser->categories()->pluck('name');
        return $this->renderJSON($categories->toArray());
    }

    /**
     * Get groups for the autocomplete
     */
    public function groups($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();
        $categories = $currentUser->groups()->pluck('name');
        return $this->renderJSON($categories->toArray());
    }

    /**
     * Provide the datasets for expenses pie chart
     */
    public function expenses($request, $response, $args)
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        $currentFund = $this->currentFund;
        $currency = $currentFund->currency;

        $title = [
            'text' => $container->get('i18n')->translate('expenses_header'),
        ];

        $plotOptions = [
            'pie' => [
                'allowPointSelect' => true,
                'cursor' => 'pointer',
                'dataLabels' => [
                    'enabled' => false
                ],
                'showInLegend' => true
            ],
        ];

        $tooltip = [
            'headerFormat' => '<span style="font-size:11px">{series.name}</span><br>',
            'pointFormat' => '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:' . $currency->format . '}</b> of total<br/>',
        ];

        $series = [
            [
                'name' => 'Groups',
                'colorByPoint' => true,
                'data' => []
            ]
        ];
        $drilldown = [
            'series' => [],
        ];

        $groups = $currentUser->groups()->get();

        $startDate = $container->get('session')->get('transactions__start_date');
        $endDate = $container->get('session')->get('transactions__end_date');
        $transactions = $currentFund->transactions()
            ->with(array('category' => function($query) {
                $query->with('group');
            })) // lazy load categories/groups
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->get();

        // first gonna build up an array of only the groups that have trans
        $groupsSeriesData = [];
        $groupsDrilldown = [];
        foreach($transactions as $transaction) {
            $category = $transaction->category;
            $group = $category->group;

            // some categories might not belong to a group
            $groupId = $group ? $group->id : -1;
            $groupName = $group ? $group->name : 'Uncategorized';

            // same for category
            $categoryName = $category ? $category->name : 'Uncategorized';

            isset($groupsSeriesData[$groupName]) || $groupsSeriesData[$groupName] = [
                'name' => $groupName,
                'y' => 0,
                'drilldown' => $groupId,
            ];
            isset($groupsDrilldown[$groupName]) || $groupsDrilldown[$groupName] = [
                'name' => $groupName,
                'id' => $groupId,
                'data' => []
            ];

            // here is categories
            isset($groupDrilldownData[$groupName]) || $groupDrilldownData[$groupName] = [];
            isset($groupDrilldownData[$groupName][$category->name]) || $groupDrilldownData[$groupName][$category->name] = 0;

            // add to category total
            $groupDrilldownData[$groupName][$category->name]+= $transaction->amount;

            // add to y of series too (group total)
            $groupsSeriesData[$groupName]['y'] += $transaction->amount;
        }

        // first, populate the drill down data with categories
        foreach ($groupsDrilldown as $groupName => $value) {
            foreach ($groupDrilldownData[$groupName] as $categoryName => $amount) {
                $groupsDrilldown[$groupName]['data'][] = [$categoryName, $amount];
            }
        }

        $series[0]['data'] = array_values($groupsSeriesData);
        $drilldown['series'] = array_values($groupsDrilldown);

        return $this->renderJSON([
            'title' => $title,
            'plotOptions' => $plotOptions,
            'tooltip' => $tooltip,
            'series' => $series,
            'drilldown' => $drilldown,
        ]);
    }
}
