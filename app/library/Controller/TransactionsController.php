<?php
namespace App\Controller;

use Slim\Container;

use App\Model\Transaction;
use App\Validator;

class TransactionsController extends BaseController
{
    public function index($request, $response, $args)
    {
        $container = $this->getContainer();

        // get current fund
        if (!$this->currentFund) {
            $container->get('flash')->addMessage('errors', ['Please create a fund']);
            return $response->withRedirect('/funds');
        }

        $params = $request->getQueryParams();

        // set query params to session
        if (isset($params['month_filter'])) {
            $container->get('session')->set(Transaction::SESSION_FILTER_MONTH, $params['month_filter']);
        }

        // set param defaults
        $params = array_merge([
            'page' => 1,
            // 'month_filter' => $container->get('session')->get(Transaction::SESSION_FILTER_MONTH),
        ], $params);

        $page = (int)$params['page'];
        $limit = 10;
        $start = ($page-1) * $limit;

        $currentUser = $this->getCurrentUser();

        // get start and end date from the month filter
        $startDate = $container->get('session')->get(Transaction::SESSION_FILTER_START_DATE);
        $endDate = $container->get('session')->get(Transaction::SESSION_FILTER_END_DATE);

        // base query will be used for both transactions and totalTransactions
        $baseQuery = $this->currentFund->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate);

        // get total transactions for calculating pagination
        $totalTransactions = $baseQuery->get();
        $totalPages = ($totalTransactions > 0) ? ceil($totalTransactions->count() / $limit) : 1;

        // get paginated transactions for dispaying in the table
        $transactions = $baseQuery
            ->with('fund')
            ->skip($start)
            ->take($limit)
            ->get();

        // get total amounts
        $amounts = $baseQuery->pluck('amount');
        $totalAmount = $amounts->sum();

        // funds for the fund switcher
        $funds = $currentUser->funds()->orderBy('name', 'asc')->get();

        return $this->render('transactions/index', [
            'transactions' => $transactions,
            'total_amount' => $totalAmount,

            'funds' => $funds,
            'current_fund' => $this->currentFund,

            'params' => $params,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        if (!$this->currentFund) {
            $container->get('flash')->addMessage('errors', ['Please create a fund']);
            return $response->withRedirect('/funds');
        }

        // if errors found from post, this will contain data
        $params = array_merge([
            'purchased_at' => date('Y-m-d'),
        ], $request->getParams());
        $currentUser = $this->getCurrentUser();

        return $this->render('transactions/create', [
            'params' => $params,
        ]);
    }

    public function post($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

        if (!$this->currentFund) {
            $container->get('flash')->addMessage('errors', ['Please create a fund']);
            return $response->withRedirect('/funds');
        }

        $currentUser = $this->getCurrentUser();

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // description
        $validator->check('description')
            ->isNotEmpty( $i18n->translate('description_missing') );

        // amount
        $validator->check('amount')
            ->isNotEmpty( $i18n->translate('amount_missing') );

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            // get category
            $category = $this->findOrCreateCategoryByName($params['category']);
            $params['category_id'] = (int)$category->id;

            // TODO get current fund
            $params['fund_id'] = $this->currentFund->id;

            if ($transaction = $currentUser->transactions()->create($params)) {

                // redirect
                isset($params['returnTo']) or $params['returnTo'] = '/transactions';
                return $this->returnTo($params['returnTo']);

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('create', func_get_args());
    }

    public function edit($request, $response, $args)
    {
        $container = $this->getContainer();

        if (!$this->currentFund) {
            $container->get('flash')->addMessage('errors', ['Please create a fund']);
            return $response->withRedirect('/funds');
        }

        $currentUser = $this->getCurrentUser();

        $transaction = $this->getCurrentUser()->transactions()
            ->with('category')
            ->with('fund')
            ->findOrFail((int)$args['transaction_id']);

        $params = array_merge($transaction->toArray(), $request->getParams(), [
            'category' => $transaction->category->name,
        ]);

        return $this->render('transactions/edit', [
            'params' => $params,
            'transaction' => $transaction,
        ]);
    }

    public function update($request, $response, $args)
    {
        $container = $this->getContainer();

        if (!$this->currentFund) {
            $container->get('flash')->addMessage('errors', ['Please create a fund']);
            return $response->withRedirect('/funds');
        }

        $params = $request->getParams();
        $currentUser = $this->getCurrentUser();

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // description
        $validator->check('description')
            ->isNotEmpty( $i18n->translate('description_missing') );

        // amount
        $validator->check('amount')
            ->isNotEmpty( $i18n->translate('amount_missing') );

        // // category
        // $validator->check('category')
        //     ->isNotEmpty( $i18n->translate('category_missing') );

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            // get category
            if (!empty($params['category'])) {
                $category = $this->findOrCreateCategoryByName($params['category']);
                $params['category_id'] = $category->id;
            }

            $transaction = $container->get('model.transaction')
                ->findOrFail((int)$args['transaction_id']);

            if ($transaction->update($params)) {

                // redirect
                isset($params['returnTo']) or $params['returnTo'] = '/transactions';
                return $this->returnTo($params['returnTo']);

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('create', func_get_args());
    }

    public function delete($request, $response, $args)
    {
        $container = $this->getContainer();

        if (!$this->currentFund) {
            $container->get('flash')->addMessage('errors', ['Please create a fund']);
            return $response->withRedirect('/funds');
        }

        $params = $request->getParams();

        $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

        if ($transaction->delete()) {

            // redirect
            return $response->withRedirect('/transactions');

        } else {
            $errors = $transaction->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('index', func_get_args());
    }
}
