<?php
namespace App\Controller;

use App\Model\Transactions;
use App\Validator;

class TransactionsController extends BaseController
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
        $transactions = $currentUser->transactions()
            ->skip($start)
            ->take($limit)
            ->get();

        // TODO needs to be set against date range
        $totalTransactions = $currentUser->transactions()->count();
        $totalPages = ($totalTransactions > 0) ? ceil($totalTransactions/$limit) : 1;

        $amounts = $currentUser->transactions()->pluck('amount');
        $totalAmount = $amounts->sum();

        return $this->render('transactions/index', [
            'transactions' => $transactions,
            'total_amount' => $totalAmount,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();
        $currentUser = $this->getCurrentUser();

        $categories = $currentUser->categories()
            ->orderBy('parent_id', 'asc')
            ->get();

        return $this->render('transactions/create', [
            'params' => $params,
            'categories' => $categories,
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

        // if valid, create transaction
        if ($validator->isValid()) {

            if ($transaction = $currentUser->transactions()->create($params)) {

                // redirect
                isset($params['returnTo']) or $params['returnTo'] = '/';
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
        $currentUser = $this->getCurrentUser();
        $container = $this->getContainer();
        $transaction = $this->getCurrentUser()->transactions()->findOrFail((int)$args['transaction_id']);

        // if errors found from post, this will contain data
        $params = array_merge($transaction->toArray(), $request->getParams());

        $categories = $currentUser->categories()
            ->orderBy('parent_id', 'asc')
            ->get();

        return $this->render('transactions/edit', [
            'params' => $params,
            'transaction' => $transaction,
            'categories' => $categories,
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

        // if valid, create transaction
        if ($validator->isValid()) {

            $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

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
        $params = $request->getParams();
        $container = $this->getContainer();

        $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

        if ($transaction->delete()) {

            // redirect
            isset($params['returnTo']) or $params['returnTo'] = '/transactions';
            return $this->returnTo($params['returnTo']);

        } else {
            $errors = $transaction->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('index', func_get_args());
    }
}
