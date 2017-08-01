<?php
namespace App\Widget;

class CurrentMonthStats extends Base
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/current_month_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
        $currentFund = $container->get('model.fund')->find($fundId);

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $transactions = $currentFund->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->orderBy('purchased_at', 'desc')
            ->get();

        // get averate
        $totalEarnings = 0;
        $totalExpenses = 0;
        $monthlyStatsData = [];
        foreach ($transactions as $transaction) {

            $month = date('Y-m', strtotime($transaction->purchased_at));
            if (!isset($monthlyStatsData[$month])) {
                $monthlyStatsData[$month] = [
                    'earnings' => [
                        'amount' => 0,
                    ],
                    'expenses' => [
                        'amount' => 0,
                    ],
                    'balance' => [
                        'amount' => 0,
                    ],
                ];

                $data = &$monthlyStatsData[$month];
            }

            if ($transaction->amount > 0) { // is earning
                $data['earnings']['amount']+= $transaction->amount;
                $totalEarnings+= $transaction->amount;
            } else {
                $data['expenses']['amount']+= abs($transaction->amount);
                $totalExpenses+= abs($transaction->amount);
            }

            $data['balance']['amount']+= $transaction->amount;
        }

        $this->data = $monthlyStatsData;
    }
}
