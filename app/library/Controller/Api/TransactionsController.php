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

        // get paginated rows
        $transactions = $currentUser->transactions()
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

        if ($validator->isValid() && $transaction = $currentUser->transactions()->create($params)) {
            return $this->renderJSON( $transaction->toArray() );
        } else {
            return $this->handleError( $validator->getErrors(), HTTP_BAD_REQUEST );
        }
    }

    public function update($request, $response, $args)
    {
        $container = $this->getContainer();
        $params = $request->getParams();
        $currentUser = $this->getCurrentUser($request);

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

                // remove all transactions
                $transactions = $transaction->transactions()
                    ->where('transaction_id', $transactionId)
                    ->delete();

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
