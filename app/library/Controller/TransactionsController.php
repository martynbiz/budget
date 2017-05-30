<?php
namespace App\Controller;

use Slim\Container;

use App\Model\Transaction;
use App\Validator;
use App\Utils;

class TransactionsController extends BaseController
{
    /**
     * List transactions
     */
    public function index($request, $response, $args)
    {
        $container = $this->getContainer();
        $params = $request->getQueryParams();

        // set param defaults
        $params = array_merge([
            'page' => 1,
        ], $params);

        $page = (int)$params['page'];
        $limit = 20;
        $start = ($page-1) * $limit;

        $currentUser = $this->getCurrentUser();

        // // get start and end date from the month filter
        $monthFilter = $container->get('session')->get(SESSION_FILTER_MONTH);
        $startEndDates = Utils::getStartEndDateByMonth($monthFilter);

        // get total transactions for calculating pagination
        $totalTransactions = $this->currentFund->transactions()
            ->whereBetween('created_at', $startEndDates)
            ->count();
        $totalPages = ($totalTransactions > 0) ? ceil($totalTransactions / $limit) : 1;

        // get paginated transactions for dispaying in the table
        $transactions = $this->currentFund->transactions()
            ->with('fund')
            ->with('category')
            ->with('tags')
            ->whereBetween('created_at', $startEndDates)
            ->skip($start)
            ->take($limit)
            ->orderBy('purchased_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // get total amounts
        $totalAmount = $this->currentFund->transactions()
            ->whereBetween('created_at', $startEndDates)
            ->pluck('amount')
            ->sum();

        return $this->render('transactions/index', [
            'transactions' => $transactions,
            'total_amount' => $totalAmount,

            'params' => $params,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    /**
     * create transaction form
     */
    public function create($request, $response, $args)
    {
        $container = $this->getContainer();

        // if errors found from post, this will contain data
        $params = array_merge([
            'purchased_at' => date('Y-m-d'),
            'budget' => 0,
        ], $request->getParams());
        $currentUser = $this->getCurrentUser();

        return $this->render('transactions/create', [
            'params' => $params,
        ]);
    }

    /**
     * create transaction form action
     */
    public function post($request, $response, $args)
    {
        $params = array_map('trim', $request->getParams());
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

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            // find category by name if specified (or create a new one)
            if (!$category = $currentUser->categories()->where('name', $params['category'])->first()) {

                $category = $currentUser->categories()->create([
                    'name' => $params['category'],
                    'budget' => 0,
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = @$category->id;

            // get current fund
            $params['fund_id'] = $this->currentFund->id;

            if ($transaction = $currentUser->transactions()->create($params)) {

                $transaction->setTagsByTagsString($params['tags']);

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor('transactions') );

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    /**
     * edit transaction form
     */
    public function edit($request, $response, $args)
    {
        $container = $this->getContainer();

        $currentUser = $this->getCurrentUser();

        $transaction = $this->getCurrentUser()->transactions()->findOrFail((int)$args['transaction_id']);

        $params = array_merge($transaction->toArray(), $request->getParams(), [
            'category' => $transaction->category->name,
            'tags' => implode(',', $transaction->tags()->pluck('name')->toArray())
        ]);

        return $this->render('transactions/edit', [
            'params' => $params,
            'transaction' => $transaction,
        ]);
    }

    /**
     * edit transaction form action
     */
    public function update($request, $response, $args)
    {
        $container = $this->getContainer();

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

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            // find category by name if specified (or create a new one)
            if (!$category = $currentUser->categories()->where('name', $params['category'])->first()) {

                $category = $currentUser->categories()->create([
                    'name' => $params['category'],
                    'budget' => 0,
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = @$category->id;

            $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

            if ($transaction->update($params)) {

                $transaction->setTagsByTagsString($params['tags']);

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor('transactions') );

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    /**
     * delete transaction form action
     */
    public function delete($request, $response, $args)
    {
        $container = $this->getContainer();
        $params = $request->getParams();

        $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

        if ($transaction->delete()) {

            // redirect
            return $response->withRedirect( $container->get('router')->pathFor('transactions') );

        } else {
            $errors = $transaction->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }
}
