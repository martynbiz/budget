<?php
namespace App\Widget;

class CurrentMonthStats extends AbstractMonthStats
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/current_month_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        // fund has already been confirmed by setFilter mw
        $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
        $currentFund = $container->get('model.fund')->find($fundId);

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $transactions = $currentFund->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->orderBy('purchased_at', 'desc')
            ->get();

        $monthlyStatsData = $this->buildMonthStatsArray($transactions);

        // if months are empty from transactions, create an empty one for thsi month
        if (count($monthlyStatsData) === 0) {
            $monthlyStatsData[ date('Y-m') ] = $this->getEmptyMonthStat();
        }

        $this->data = $monthlyStatsData;
    }
}
