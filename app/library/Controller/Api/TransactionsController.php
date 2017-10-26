<?php
namespace App\Controller\Api;

use App\Validator;

class TransactionsController extends ApiController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser($request);

        $page = (int)$request->getQueryParam('page', 1);
        $limit = (int)$request->getQueryParam('limit', 20);
        $start = ($page-1) * $limit;

        // check that this fund belongs to user
        $fundId = (int)$request->getQueryParam('fund');
        if (!$fund = $currentUser->funds()->find($fundId)) {
            return $this->handleError( 'Fund not found', HTTP_BAD_REQUEST );
        }

        // get paginated rows
        $transactions = $currentUser->transactions()
            ->where('fund_id', $fundId)
            ->with('fund')
            ->with('tags')
            ->with('category')
            ->skip($start)
            ->take($limit)
            ->get();

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
