<?php
namespace App\Controller;

class HomeController extends BaseController
{
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

        $monthlyStatsData = [];

        $fromMonth = date('Y-m-01', strtotime('-3 Months'));
        $transactions = $this->currentFund->transactions()
            ->where('purchased_at', '>=', $fromMonth)
            ->orderBy('purchased_at', 'desc')
            ->get();

        // get averate
        $totalEarnings = 0;
        $totalExpenses = 0;
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
        }

        // calculate the average
        $averageMonthlyEarnings = $totalEarnings / count($monthlyStatsData);
        $averageMonthlyExpenses = $totalExpenses / count($monthlyStatsData);


        // set average_diff
        foreach ($monthlyStatsData as $month => &$data) {

            $averageEarningsRatio = abs($data['earnings']['amount'] / $averageMonthlyEarnings);
            $averageExpensesRatio = abs($data['expenses']['amount'] / $averageMonthlyExpenses);

            // convert ration to percentage
            if ($averageEarningsRatio > 1) {
                $averageEarningsPercent = '+' . round(($averageEarningsRatio-1) * 100) . '%';
            } elseif ($averageEarningsRatio <= 0) {
                $averageEarningsPercent = '';
            } elseif ($averageEarningsRatio < 1) {
                $averageEarningsPercent = '-' . round((1 - $averageEarningsRatio) * 100) . '%';
            }

            if ($averageExpensesRatio > 1) {
                $averageExpensesPercent = '+' . round(($averageExpensesRatio-1) * 100) . '%';
            } elseif ($averageExpensesRatio <= 0) {
                $averageExpensesPercent = '';
            } elseif ($averageExpensesRatio < 1) {
                $averageExpensesPercent = '-' . round((1 - $averageExpensesRatio) * 100) . '%';
            }

            $data['earnings']['average_diff'] = $averageEarningsPercent;
            $data['earnings']['average_ratio'] = $averageEarningsRatio;

            $data['expenses']['average_diff'] = $averageExpensesPercent;
            $data['expenses']['average_ratio'] = $averageExpensesRatio;
        }

        return $this->render('home/dashboard', [
            'monthly_stats_data' => $monthlyStatsData,
        ]);
    }

    /**
     *
     */
    public function switchLanguage($request, $response, $args)
    {
        $params = $request->getParams();

        // set language cookie
        setcookie('language', $params['language']);

        return $response->withRedirect('/');
    }

    /**
     * Show welcome screen
     */
    public function notFound($request, $response)
    {
        return $this->render('404')->withStatus(404);
    }
}
