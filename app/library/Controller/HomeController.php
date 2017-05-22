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

        $cacheId = 'monthly_stats_data_' . $currentUser->id;
        if (!$monthlyStatsData = $container->get('cache')->get($cacheId)) {

            $monthlyStatsData = [
                date('Y-m') => [],
                date('Y-m', strtotime("-1 month")) => [],
                date('Y-m', strtotime("-2 month")) => [],
            ];

            // we only wanna calculate averages on months that have transactions logged
            // here we'll increment if any for that given month found
            $monthsWithTransactions = 0;

            // get averate
            $totalEarnings = 0;
            $totalExpenses = 0;
            foreach ($monthlyStatsData as $month => &$data) {
                $startDate = date('Y-m-01', strtotime($month . '-01'));
                $endDate = date('Y-m-t', strtotime($startDate));

                $earningsAmount = $this->currentFund->transactions()
                    ->where('purchased_at', '>=', $startDate)
                    ->where('purchased_at', '<=', $endDate)
                    ->where('amount', '>', 0)
                    ->pluck('amount')
                    ->sum();

                $expensesAmount = $this->currentFund->transactions()
                    ->where('purchased_at', '>=', $startDate)
                    ->where('purchased_at', '<=', $endDate)
                    ->where('amount', '<', 0)
                    ->pluck('amount')
                    ->sum();

                if ($earningsAmount || $expensesAmount) {
                    $monthsWithTransactions++;
                }

                $data['earnings'] = [
                    'amount' => $earningsAmount,
                ];

                $data['expenses'] = [
                    'amount' => $expensesAmount,
                ];

                $totalEarnings+= $earningsAmount;
                $totalEarnings+= $expensesAmount;
            }

            $averageMonthlyEarnings = $totalEarnings / $monthsWithTransactions;
            $averageMonthlyExpenses = $totalExpenses / $monthsWithTransactions;

            // set average_diff
            foreach ($monthlyStatsData as $month => &$data) {

                $averageEarningsRatio = abs($data['earnings']['amount'] / $averageMonthlyEarnings);
                $averageExpensesRatio = abs($data['expenses']['amount'] / $averageMonthlyEarnings);

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

            $container->get('cache')->set($cacheId, $monthlyStatsData, 3600);
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
}
