<?php
namespace App\Controller;

use Slim\Container;

use App\Model\Transactions;
use App\Validator;

class TransactionsController extends BaseController
{
    /**
     * @var Store the current fund
     */
    protected $currentFund;

    public function __construct(Container $container) {
       parent::__construct($container);

       $currentUser = $this->getCurrentUser();

       // ensure we have a fund before dong any transaction related stuff
       if (!$fundId = $container->get('session')->get('current_fund_id')) {
           $this->currentFund = $currentUser->funds()->first();
           $container->get('session')->set('current_fund_id', $this->currentFund->id);
       } else {
           $this->currentFund = $currentUser->funds()->find($fundId);
       }

       if (!$this->currentFund) {
           $container->get('flash')->addMessage('errors', [
               'Fund not found. Please create one.'
           ]);
           return $this->returnTo('/funds/create');
       }
    }

    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();
        $container = $this->getContainer();

        $options = array_merge([
            'page' => 1,
        ], $request->getQueryParams());

        $page = (int)$options['page'];
        $limit = 10;
        $start = ($page-1) * $limit;

        // set date range if not set
        // if (!$startDate = $container->get('session')->get('transactions__start_date')) {
        //     $container->get('session')->set('transactions__start_date', date("y-m-d", strtotime("-1 month")));
        //     $startDate = $container->get('session')->get('transactions__start_date');
        // }
        // if (!$endDate = $container->get('session')->get('transactions__end_date')) {
        //     $container->get('session')->set('transactions__end_date', date("y-m-d"));
        //     $endDate = $container->get('session')->get('transactions__end_date')
        // }

        if (isset($options['start_date'])) {
            $container->get('session')->set('transactions__start_date', $options['start_date']);
        }
        if (!$startDate = $container->get('session')->get('transactions__start_date')) {
            $startDate = date("Y-m-d", strtotime("-1 month"));
        }

        if (isset($options['end_date'])) {
            $container->get('session')->set('transactions__end_date', $options['end_date']);
        }
        if (!$endDate = $container->get('session')->get('transactions__end_date')) {
            $endDate = date("Y-m-d");
        }

        // base query will be used for both transactions and totalTransactions
        $baseQuery = $currentUser->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->where('fund_id', $this->currentFund->id);

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

            'start_date' => $startDate,
            'end_date' => $endDate,

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

        return $this->render('transactions/create', [
            'params' => $params,
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
        $validator->check('category')
            ->isNotEmpty( $i18n->translate('category_missing') );

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            // find or create category
            if (!$category = $currentUser->categories()->where('name', $params['category'])->first()) {
                $category = $currentUser->categories()->create([
                    'name' => $params['category'],
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = $category->id;

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
        $currentUser = $this->getCurrentUser();
        $container = $this->getContainer();

        $transaction = $this->getCurrentUser()->transactions()
            ->with('fund')
            ->findOrFail((int)$args['transaction_id']);

        // if errors found from post, this will contain data
        $params = array_merge($transaction->toArray(), $request->getParams());

        return $this->render('transactions/edit', [
            'params' => $params,
            'transaction' => $transaction,
        ]);
    }

    public function update($request, $response, $args)
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
        $validator->check('category')
            ->isNotEmpty( $i18n->translate('category_missing') );

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

            // find or create category
            if (!$category = $currentUser->categories()->where('name', $params['category'])->first()) {
                $category = $currentUser->categories()->create([
                    'name' => $params['category'],
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = $category->id;

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
