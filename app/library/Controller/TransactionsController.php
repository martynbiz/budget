<?php
namespace App\Controller;

use Slim\Container;

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
        $limit = (int)$request->getQueryParam('limit', 20);
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

        // this get(...) argument is required when joing tags, we could maybe override get so this always gets included?
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
        $currentUser = $this->getCurrentUser();
        $container = $this->getContainer();

        $params = [];

        // if split_transaction_id is present in the query params, we'll populate
        // the params with values from that transaction
        if ($splitTransactionId = $request->getQueryParam('split_transaction_id')) {

            $splitTransaction = $container->get('model.transaction')
                ->with('tags')
                ->findOrFail($splitTransactionId);

            // add form field values
            $params['description'] = $splitTransaction->description;
            $params['category'] = $splitTransaction->category->name;
            $params['purchased_at'] = $splitTransaction->purchased_at;
            $params['split_transaction_id'] = $splitTransaction->id;
            $params['tags'] = $splitTransaction->tags()->pluck('name')->toArray();
        }

        // insert defaults
        $params = array_merge([
            'purchased_at' => date('Y-m-d'),
        ], $params, $request->getParams());

        // this needs to be applied seperately encase param tags is NULL (array_merge
        // will return NULL too)
        $tags = $currentUser->tags()->pluck('name')->toArray();
        if (is_array(@$request->getParams()['tags'])) {
            $tags = array_unique(array_merge($tags, $request->getParams()['tags']));
        }

        return $this->render('transactions/create', [
            'params' => $params,
            'tags' => $tags,
        ]);
    }

    /**
     * create transaction form action
     */
    public function post($request, $response, $args)
    {
        $params = array_map(function($value) {
            switch (gettype($value)) {
                case 'string':
                    return trim($value);
                    break;
                default:
                    return $value;
            }
        }, $request->getParams());
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
            $params['category_id'] = $category->id;

            // get current fund
            $params['fund_id'] = $this->currentFund->id;

            if ($transaction = $currentUser->transactions()->create($params)) {

                // attach the tags for this transaction
                $transaction->attachTagsByArray($params['tags']);

                // if this is a split transaction request, we'll deduct the amount
                // of this new one from the original
                if ($splitTransactionId = $request->getParam('split_transaction_id')) {

                    $splitTransaction = $container->get('model.transaction')
                        ->findOrFail($splitTransactionId);

                    $splitTransaction->amount -= $transaction->amount;

                    $splitTransaction->save();
                }

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
            'tags' => $transaction->tags->pluck('name')->toArray(),
        ]);

        // this needs to be applied seperately encase param tags is NULL (array_merge
        // will return NULL too)
        if (is_array(@$request->getParams()['tags'])) {
            $params['tags'] = array_merge($params['tags'], $request->getParams()['tags']);
        }

        // TODO do pluck in template, i didn't realise you could do it to the >tags propery
        // try a speed test on both tags->pluck() and tags()->pluck()

        // this needs to be applied seperately encase param tags is NULL (array_merge
        // will return NULL too)
        $tags = $currentUser->tags()->pluck('name')->toArray();
        if (is_array(@$request->getParams()['tags'])) {
            $tags = array_unique(array_merge($tags, $request->getParams()['tags']));
        }

        return $this->render('transactions/edit', [
            'params' => $params,
            'transaction' => $transaction,
            'tags' => $tags,
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

            $transaction = $currentUser->transactions()->findOrFail((int)$args['transaction_id']);

            if ($transaction->update($params)) {

                $transaction->attachTagsByArray($params['tags']);

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor('transactions') );

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }

    /**
     * delete transaction form action
     */
    public function delete($request, $response, $args)
    {
        $container = $this->getContainer();
        $params = $request->getParams();
        $currentUser = $this->getCurrentUser();

        $transaction = $currentUser->transactions()->findOrFail((int)$args['transaction_id']);

        if ($transaction->delete()) {

            // redirect
            return $response->withRedirect( $container->get('router')->pathFor('transactions') );

        } else {
            $errors = $transaction->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }

    /**
     * Render the json and attach to the response
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    protected function renderJSON($data=array())
    {
        $data = $data['transactions'];

        return parent::renderJSON($data);
    }
}
