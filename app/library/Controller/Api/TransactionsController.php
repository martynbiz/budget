<?php
namespace App\Controller\Api;

use App\Validator;
use App\Utils;

class TransactionsController extends ApiController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser($request);

        $page = (int)$request->getQueryParam('page', 1);
        $limit = (int)$request->getQueryParam('limit', 20);
        $orderBy = $request->getQueryParam('order_by', "purchased_at");
        $orderDir = $request->getQueryParam('order_dir', "desc");

        // query columns
        $month = $request->getQueryParam('month');
        $categoryId = $request->getQueryParam('category');
        $tagId = $request->getQueryParam('tag');

        $start = ($page-1) * $limit;

        // check that this fund belongs to user
        $fundId = (int)$request->getQueryParam('fund');
        if (!$fund = $currentUser->funds()->find($fundId)) {
            return $this->handleError( 'Fund not found', HTTP_BAD_REQUEST );
        }

        // apply filter queries

        // If tag id is present, then we need to build the query a little differently
        // coz it's a many to many relationship,
        if ($tagId) {
            $tag = $currentUser->tags()->find((int)$tagId);
            $query = $tag->transactions();
        } else {
            $query = $currentUser->transactions();
        }

        $query = $query
            ->where('fund_id', $fundId)
            ->with('fund')
            ->with('tags')
            ->with('category')
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($limit);

        if ($month) {
            $query->whereBetween('purchased_at', Utils::getStartEndDateByMonth($month) );
        }

        if ($categoryId) {
            $query->where('category_id', (int)$categoryId);
        }

        // get paginated rows
        $transactions = $query->get();

        return $this->renderJSON( $transactions->toArray() );
    }

    public function post($request, $response, $args)
    {
        $container = $this->getContainer();
        $params = $request->getParams();
        $currentUser = $this->getCurrentUser($request);

        // find category by name if specified (or create a new one)
        // in the end, we will ensure that category_id is set ..
        if (isset($params['category'])) {
            if (!$category = $currentUser->categories()->where('name', $params['category'])->first()) {

                $category = $currentUser->categories()->create([
                    'name' => $params['category'],
                    'budget' => 0,
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = $category->id;
        }

        // validate form data
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

        // fund
        $validator->check('fund_id')
            ->isNotEmpty( $i18n->translate('fund_id_missing') );

        // category
        $validator->check('category_id')
            ->isNotEmpty( $i18n->translate('category_id_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            if ($transaction = $currentUser->transactions()->create($params)) {

                $transaction->attachTagsByArray($params['tags']);

                return $this->renderJSON( $transaction->toArray() );

            } else {
                return $this->handleError( $transaction->errors(), HTTP_BAD_REQUEST );
            }

        } else {
            return $this->handleError( $validator->getErrors(), HTTP_BAD_REQUEST );
        }
    }

    public function update($request, $response, $args)
    {
        $container = $this->getContainer();
        $params = $request->getParams();
        $currentUser = $this->getCurrentUser($request);

        // find category by name if specified (or create a new one)
        // in the end, we will ensure that category_id is set ..
        if (isset($params['category'])) {
            if (!$category = $currentUser->categories()->where('name', $params['category'])->first()) {

                $category = $currentUser->categories()->create([
                    'name' => $params['category'],
                    'budget' => 0,
                    'group_id' => 0,
                ]);
            }
            $params['category_id'] = $category->id;
        }

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

        // fund
        $validator->check('fund_id')
            ->isNotEmpty( $i18n->translate('fund_id_missing') );

        // category
        $validator->check('category_id')
            ->isNotEmpty( $i18n->translate('category_id_missing') );

        if ($validator->isValid()) {

            try {

                $transaction = $currentUser->transactions()->findOrFail((int)$args['transaction_id']);

                if ($transaction->update($params)) {
                    return $this->renderJSON( $transaction->toArray() );
                }

            } catch (Exception $e) {
                $validator->addError( $e->getMessage() );
            }

        }

        return $this->handleError( $validator->getErrors(), HTTP_BAD_REQUEST );
    }

    public function delete($request, $response, $args)
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser($request);

        try {

            $transaction = $currentUser->transactions()->findOrFail((int)$args['transaction_id']);
            $transactionId = $transaction->id;

            if ($transaction->delete()) {

                return $this->renderJSON( json_decode("{}") );

            } else {

                return $this->handleError( $transaction->errors() );

            }

        } catch (Exception $e) {

            $message = $e->getMessage();

        }

        return $this->handleError( $message );
    }
}
