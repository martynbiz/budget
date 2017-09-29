<?php
namespace App\Controller\Api;

use App\Model\Funds;
use App\Validator;

class FundsController extends ApiController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $page = (int)$request->getQueryParam('page', 1);
        $limit = (int)$request->getQueryParam('limit', 20);
        $start = ($page-1) * $limit;

        // get paginated rows
        $funds = $currentUser->funds()
            ->with('currency')
            ->skip($start)
            ->take($limit)
            ->get();

        return $this->renderJSON( $funds->toArray() );
    }

    public function post($request, $response, $args)
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        // get the POST json
        $params = json_decode(file_get_contents('php://input'), true);

        // validate form data
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        $validator->check('amount')
            ->isNotEmpty( $i18n->translate('amount_missing') );

        $validator->check('currency_id')
            ->isNotEmpty( $i18n->translate('currency_missing') );

        if ($validator->isValid() && $fund = $currentUser->funds()->create($params)) {
            return $this->renderJSON( $fund->toArray() );
        } else {
            return $this->handleError( $validator->getErrors() );
        }
    }

    public function update($request, $response, $args)
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        // get the POST json
        $params = json_decode(file_get_contents('php://input'), true);

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
            ->isNotEmpty( $i18n->translate('currency_missing') );

        if ($validator->isValid()) {

            try {

                $fund = $currentUser->funds()->findOrFail((int)$args['fund_id']);

                if ($fund->update($params)) {
                    return $this->renderJSON( $fund->toArray() );
                }

            } catch (Exception $e) {
                $validator->addError( $e->getMessage() );
            }

        }

        return $this->handleError( $validator->getErrors() );
    }

    public function delete($request, $response, $args)
    {
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        try {

            $fund = $currentUser->funds()->findOrFail((int)$args['fund_id']);
            $fundId = $fund->id;

            if ($fund->delete()) {

                // remove all transactions
                $transactions = $fund->transactions()
                    ->where('fund_id', $fundId)
                    ->delete();

                return $this->renderJSON( json_decode("{}") );

            } else {

                return $this->handleError( $fund->errors() );

            }

        } catch (Exception $e) {

            $message = $e->getMessage();

        }

        return $this->handleError( $message );
    }
}
