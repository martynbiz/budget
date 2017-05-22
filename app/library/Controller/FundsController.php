<?php
namespace App\Controller;

use App\Model\Funds;
use App\Validator;

class FundsController extends BaseController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $options = array_merge([
            'page' => 1,
        ], $request->getQueryParams());

        $page = (int)$options['page'];
        $limit = 1;
        $start = ($page-1) * $limit;

        // get paginated rows
        $funds = $currentUser->funds()
            ->with('currency')
            ->with('transactions')
            ->skip($start)
            ->take($limit)
            ->get();

        // TODO needs to be set against date range
        $totalFunds = $currentUser->funds()->count();
        $totalPages = ($totalFunds > 0) ? ceil($totalFunds/$limit) : 1;

        return $this->render('funds/index', [
            'funds' => $funds,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();
        $container = $this->getContainer();

        $currencies = $container->get('model.currency')->orderBy('name', 'asc')->get();

        return $this->render('funds/create', [
            'params' => $params,
            'currencies' => $currencies,
        ]);
    }

    public function post($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // description
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        // amount
        $validator->check('amount')
            ->isNotEmpty( $i18n->translate('amount_missing') );

        // category
        $validator->check('currency_id')
            ->isNotEmpty( $i18n->translate('category_missing') );

        // if valid, create fund
        if ($validator->isValid()) {

            if ($fund = $currentUser->funds()->create($params)) {

                // set new fund as current
                $container->get('session')->set(SESSION_FILTER_FUND, $fund->id);
                $this->currentFund = $fund;

                // redirect
                return $response->withRedirect('/transactions');

            } else {
                $errors = $fund->errors();
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
        $fund = $this->getCurrentUser()->funds()->findOrFail((int)$args['fund_id']);

        // if errors found from post, this will contain data
        $params = array_merge($fund->toArray(), $request->getParams());

        $currencies = $container->get('model.currency')->orderBy('name', 'asc')->get();

        return $this->render('funds/edit', [
            'params' => $params,
            'fund' => $fund,
            'currencies' => $currencies,
        ]);
    }

    public function update($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

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

        // category
        $validator->check('category_id')
            ->isNotEmpty( $i18n->translate('category_missing') );

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create fund
        if ($validator->isValid()) {

            $fund = $container->get('model.fund')->findOrFail((int)$args['fund_id']);

            if ($fund->update($params)) {

                // redirect
                return $response->withRedirect('/funds');

            } else {
                $errors = $fund->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('create', func_get_args());
    }

    public function delete($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

        $fund = $container->get('model.fund')->findOrFail((int)$args['fund_id']);
        $fundId = $fund->id;

        if ($fund->delete()) {

            // remove all transactions
            $transactions = $fund->transactions()
                ->where('fund_id', $fundId)
                ->delete();

            // redirect
            return $response->withRedirect('/funds');

        } else {
            $errors = $fund->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('index', func_get_args());
    }

    /**
     * Switch the current fund to another
     */
    public function switch($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        // change fund in session
        $newFund = $currentUser->funds()->findOrFail((int)$params['fund_id']);
        $container->get('session')->set('current_fund_id', $newFund->id);

        // redirect back to transactions
        return $this->redirect('/transactions');
    }
}
