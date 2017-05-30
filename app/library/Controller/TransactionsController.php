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
        $params = $request->getQueryParams();

        // set param defaults
        $params = array_merge([
            'page' => 1,
        ], $params);

        $page = (int)$params['page'];
        $limit = 20;
        $start = ($page-1) * $limit;

        $currentUser = $this->getCurrentUser();

        // get start and end date from the month filter
        $monthFilter = $container->get('session')->get(SESSION_FILTER_MONTH);
        $startDate = date('Y-m-01', strtotime($monthFilter . '-01'));
        $endDate = date('Y-m-t', strtotime($startDate));

        // get total transactions for calculating pagination
        $totalTransactions = $this->currentFund->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->count();
        $totalPages = ($totalTransactions > 0) ? ceil($totalTransactions / $limit) : 1;

        // get paginated transactions for dispaying in the table
        $transactions = $this->currentFund->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->with('fund')
            ->with('category')
            ->with('tags')
            ->skip($start)
            ->take($limit)
            ->orderBy('purchased_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // get total amounts
        $totalAmount = $this->currentFund->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
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

    public function create($request, $response, $args)
    {
        $container = $this->getContainer();

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
            // $category = $this->findOrCreateCategoryByName($params['category']);

            // get category
            if (!empty($params['category'])) {

                // // TODO move this to $transaction->findOrCreateCategoryByName
                // $category = $this->findOrCreateCategoryByName($params['category']);
                // $params['category_id'] = $category->id;

                $category = $currentUser->categories()->firstOrCreate(['name' => $params['category']], [
                    // 'name' => $categoryName,
                    'budget' => 0,
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = (int)$category->id;

            // get current fund
            $params['fund_id'] = $this->currentFund->id;

            if ($transaction = $currentUser->transactions()->create($params)) {

                $transaction->setTagsByTagsString($params['tags']);

                // // create tags (if any)
                // // TODO move to Transaction model ::setTagsByTagsString
                // if (!empty(trim($params['tags']))) {
                //     $tagNames = array_map('trim', explode(',', $params['tags']));
                //     foreach ($tagNames as $name) {
                //
                //         // first try to find an existing tag, if none exist, create a
                //         // new one
                //         if (!$tag = $currentUser->tags()->where('name', $name)->first()) {
                //             $tag = $currentUser->tags()->create([
                //                 'name' => $name,
                //             ]);
                //         }
                //
                //         $transaction->tags()->attach($tag);
                //     }
                // }

                // redirect
                return $response->withRedirect('/transactions');

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    public function edit($request, $response, $args)
    {
        $container = $this->getContainer();

        // // TODO move this to middleware
        // if (!$this->currentFund) {
        //     $container->get('flash')->addMessage('errors', ['Please create a fund']);
        //     return $response->withRedirect('/funds');
        // }

        $currentUser = $this->getCurrentUser();

        $transaction = $this->getCurrentUser()->transactions()
            ->with('category')
            // ->with('fund') // TODO is this required?
            ->with('tags')
            ->findOrFail((int)$args['transaction_id']);

        $params = array_merge($transaction->toArray(), $request->getParams(), [
            'category' => $transaction->category->name,
            'tags' => implode(',', $transaction->tags()->pluck('name')->toArray())
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

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            // get category
            if (!empty($params['category'])) {

                // // TODO move this to $transaction->findOrCreateCategoryByName
                // $category = $this->findOrCreateCategoryByName($params['category']);
                // $params['category_id'] = $category->id;

                $category = $currentUser->categories()->firstOrCreate(['name' => $params['category']], [
                    // 'name' => $params['category'],
                    'budget' => 0,
                    'group_id' => 0,
                ]);

                $params['category_id'] = $category->id;
            }

            $params['category_id'] = @$category->id;

            $transaction = $container->get('model.transaction')->findOrFail((int)$args['transaction_id']);

            if ($transaction->update($params)) {

                $transaction->setTagsByTagsString($params['tags']);

                // // just clear existing tags as we'll create new pivot links
                // $transaction->tags()->detach();
                //
                // // create tags (if any)
                // if (!empty(trim($params['tags']))) {
                //     $tagNames = array_map('trim', explode(',', $params['tags']));
                //     foreach ($tagNames as $name) {
                //
                //         // first try to find an existing tag, if none exist, create a
                //         // new one
                //         if (!$tag = $currentUser->tags()->where('name', $name)->first()) {
                //             $tag = $currentUser->tags()->create([
                //                 'name' => $name,
                //             ]);
                //         }
                //
                //         $transaction->tags()->attach($tag);
                //     }
                // }

                // redirect
                return $response->withRedirect('/transactions');

            } else {
                $errors = $transaction->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
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
        return $this->index($request, $response, $args);
    }
}
