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


        // monthly stats widget

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

            $data['balance']['amount']+= $transaction->amount;
        }


        // category stats widget

        // first get the categories
        $categories = $currentUser->categories()
            ->with('transactions')
            ->orderBy('group_id')
            ->get();

        // build array with remaining budget
        $budgetStatsData = [];
        foreach ($categories as $category) {

            $transactionsAmount = $category->getTransactionsAmount(); // TODO pass in start/end
            $budget = $category->getBudgetByMonth($this->currentFund, date('Y-m'));
            if ($budget) {
                $remainingBudget = $budget->amount - abs($transactionsAmount);

                array_push($budgetStatsData, [
                    'name' => $category->name,
                    'remaning_budget' => $remainingBudget,
                ]);
            }
        }

        usort($budgetStatsData, function($a, $b) {
            return $a['remaning_budget'] - $b['remaning_budget'];
        });

        return $this->render('home/dashboard', [
            'monthly_stats_data' => $monthlyStatsData,
            'budget_stats_data' => $budgetStatsData,
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
