<?php
namespace App\Widget;

class PreviousMonthStats extends AbstractMonthStats
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/previous_month_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        // fund has already been confirmed by setFilter mw
        $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
        $currentFund = $container->get('model.fund')->find($fundId);

        $endDate = date('Y-m-t', strtotime('-1 Months')); // last day of last month
        $transactions = $currentFund->transactions()
            ->where('purchased_at', '<=', $endDate)
            ->orderBy('purchased_at', 'desc')
            ->get();

        $monthlyStatsData = $this->buildMonthStatsArray($transactions);

        $this->data = $monthlyStatsData;
    }
}
