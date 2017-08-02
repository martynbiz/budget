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
        $currentUser = $this->getCurrentUser();

        // set param defaults
        $query = array_merge([
            'page' => 1,
            'sort' => 'purchased_at',
            'dir' => -1,
        ], $request->getQueryParams());

        if (!isset($query['month'])) $query['month'] = date('Y-m');

        $page = (int)$query['page'];
        $limit = 20;
        $start = ($page-1) * $limit;

        $baseQuery = $this->currentFund->transactions()->whereQuery($query);

        // get total transactions for calculating pagination
        $totalTransactions = (clone $baseQuery)->count();
        $totalPages = ($totalTransactions > 0) ? ceil($totalTransactions / $limit) : 1;

        // get paginated transactions for dispaying in the table
        $transactionsQuery = (clone $baseQuery)
            ->with('fund')
            ->with('category')
            ->with('tags')
            ->skip($start)
            ->take($limit);

        // we need to seperate sort as categories requires a little more work
        $dir = ($query['dir'] > 0) ? 'asc' : 'desc';
        if ($query['sort'] == 'category') {

            // order by joined categories table's name column
            $transactionsQuery->orderByCategoryName($dir);

        } else {

            // order by "sort" param
            $transactionsQuery
                ->orderBy('transactions.' . $query['sort'], $dir)
                ->orderBy('transactions.id', 'desc'); // just so we can order within days
        }

        $transactions = $transactionsQuery->get(['transactions.id as id', 'transactions.*']);

        // get total amounts
        $totalAmount = (clone $baseQuery)
            ->pluck('amount')
            ->sum();

        // filters
        $this->includeMonthFilter();
        $this->includeCategoriesFilter();
        $this->includeTagsFilter();

        return $this->render('transactions/index', [
            'transactions' => $transactions,
            'total_amount' => $totalAmount,

            'query' => $query,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,

            'selected_column' => $query['sort'],
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
