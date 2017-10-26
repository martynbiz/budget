<?php
namespace App\Widget;

use Illuminate\Database\Eloquent\Collection;

abstract class AbstractMonthStats extends Base
{
    /**
     * Build the array from $transactions
     * @param Transaction $transactions
     * @return array
     */
    public function buildMonthStatsArray(Collection $transactions)
    {
        $totalEarnings = 0;
        $totalExpenses = 0;
        $monthlyStatsData = [];
        foreach ($transactions as $transaction) {

            $month = date('Y-m', strtotime($transaction->purchased_at));
            if (!isset($monthlyStatsData[$month])) {
                $monthlyStatsData[$month] = $this->getEmptyMonthStat();
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

        return $monthlyStatsData;
    }

    /**
     * Gets a empty month
     * @return array
     */
    protected function getEmptyMonthStat()
    {
        return [
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
    }
}
