<?php
namespace App\Widget;

class YearlyStats extends Base
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/yearly_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        // fund has already been confirmed by setFilter mw
        $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
        $currentFund = $container->get('model.fund')->find($fundId);

        // get all transactions
        $transactions = $currentFund->transactions()
            ->orderBy('purchased_at', 'desc')
            ->get();

        // get averate
        $totalEarnings = 0;
        $totalExpenses = 0;
        $yearlyStatsData = [];
        foreach ($transactions as $transaction) {

            $year = date('Y', strtotime($transaction->purchased_at));
            if (!isset($yearlyStatsData[$year])) {
                $yearlyStatsData[$year] = [
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

                $data = &$yearlyStatsData[$year];
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

        $this->data = $yearlyStatsData;
    }
}
