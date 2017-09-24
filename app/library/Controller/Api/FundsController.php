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
error_log(json_encode(@$currentUser->email));
$errorM = '';
try {
    // get paginated rows
    $funds = $currentUser->funds()
        ->with('currency')
        ->skip($start)
        ->take($limit)
        ->get();
} catch (Exception $e) {
    $errorM = $e->getMessage();
}


error_log($errorM);
        return $this->renderJSON( $funds->toArray() );
    }

    // public function post($request, $response, $args)
    // {
    //     $container = $this->getContainer();
    //     $currentUser = $this->getCurrentUser();
    //
    //     // get the POST json
    //     $params = json_decode(file_get_contents('php://input'), true);
    //
    //     // validate form data
    //     $validator = new Validator();
    //     $validator->setData($params);
    //     $i18n = $container->get('i18n');
    //
    //     // $validator->check('name')
    //     //     ->isNotEmpty( $i18n->translate('name_missing') );
    //     //
    //     // $validator->check('amount')
    //     //     ->isNotEmpty( $i18n->translate('amount_missing') );
    //     //
    //     // $validator->check('currency_code')
    //     //     ->isNotEmpty( $i18n->translate('currency_missing') );
    //
    //     if ($validator->isValid() && $fund = $currentUser->funds()->create($params)) {
    //         return $this->renderJSON( $fund->toArray() );
    //     } else {
    //         return $this->handleError( $validator->getErrors() );
    //     }
    // }

    // public function create($request, $response, $args)
    // {
    //     // if errors found from post, this will contain data
    //     $params = $request->getParams();
    //     $container = $this->getContainer();
    //
    //     $currencies = $container->get('model.currency')->orderBy('name', 'asc')->get();
    //
    //     return $this->render('funds/create', [
    //         'params' => $params,
    //         'currencies' => $currencies,
    //     ]);
    // }
    //
    // public function post($request, $response, $args)
    // {
    //     $params = $request->getParams();
    //     $container = $this->getContainer();
    //     $currentUser = $this->getCurrentUser();
    //
    //     // validate form data
    //
    //     // our simple custom validator for the form
    //     $validator = new Validator();
    //     $validator->setData($params);
    //     $i18n = $container->get('i18n');
    //
    //     // description
    //     $validator->check('name')
    //         ->isNotEmpty( $i18n->translate('name_missing') );
    //
    //     // amount
    //     $validator->check('amount')
    //         ->isNotEmpty( $i18n->translate('amount_missing') );
    //
    //     // category
    //     $validator->check('currency_id')
    //         ->isNotEmpty( $i18n->translate('category_missing') );
    //
    //     // if valid, create fund
    //     if ($validator->isValid()) {
    //
    //         if ($fund = $currentUser->funds()->create($params)) {
    //
    //             // set new fund as current
    //             $container->get('session')->set(SESSION_FILTER_FUND, $fund->id);
    //             $this->currentFund = $fund;
    //
    //             // redirect
    //             return $response->withRedirect('/');
    //
    //         } else {
    //             $errors = $fund->errors();
    //         }
    //
    //     } else {
    //         $errors = $validator->getErrors();
    //     }
    //
    //     $container->get('flash')->addMessage('errors', $errors);
    //     return $this->create($request, $response, $args);
    // }
    //
    // public function edit($request, $response, $args)
    // {
    //     $container = $this->getContainer();
    //     $fund = $this->getCurrentUser()->funds()->findOrFail((int)$args['fund_id']);
    //
    //     // if errors found from post, this will contain data
    //     $params = array_merge($fund->toArray(), $request->getParams());
    //
    //     $currencies = $container->get('model.currency')->orderBy('name', 'asc')->get();
    //
    //     return $this->render('funds/edit', [
    //         'params' => $params,
    //         'fund' => $fund,
    //         'currencies' => $currencies,
    //     ]);
    // }
    //
    // public function update($request, $response, $args)
    // {
    //     $params = $request->getParams();
    //     $container = $this->getContainer();
    //
    //     // validate form data
    //
    //     // our simple custom validator for the form
    //     $validator = new Validator();
    //     $validator->setData($params);
    //     $i18n = $container->get('i18n');
    //
    //     // description
    //     $validator->check('name')
    //         ->isNotEmpty( $i18n->translate('name_missing') );
    //
    //     // amount
    //     $validator->check('amount')
    //         ->isNotEmpty( $i18n->translate('amount_missing') );
    //
    //     // category
    //     $validator->check('currency_id')
    //         ->isNotEmpty( $i18n->translate('category_missing') );
    //
    //     // if valid, create fund
    //     if ($validator->isValid()) {
    //
    //         $fund = $container->get('model.fund')->findOrFail((int)$args['fund_id']);
    //
    //         if ($fund->update($params)) {
    //
    //             // redirect
    //             return $response->withRedirect('/funds');
    //
    //         } else {
    //             $errors = $fund->errors();
    //         }
    //
    //     } else {
    //         $errors = $validator->getErrors();
    //     }
    //
    //     $container->get('flash')->addMessage('errors', $errors);
    //     return $this->edit($request, $response, $args);
    // }
    //
    // public function delete($request, $response, $args)
    // {
    //     $params = $request->getParams();
    //     $container = $this->getContainer();
    //
    //     $fund = $container->get('model.fund')->findOrFail((int)$args['fund_id']);
    //     $fundId = $fund->id;
    //
    //     if ($fund->delete()) {
    //
    //         // remove all transactions
    //         $transactions = $fund->transactions()
    //             ->where('fund_id', $fundId)
    //             ->delete();
    //
    //         // redirect
    //         return $response->withRedirect('/funds');
    //
    //     } else {
    //         $errors = $fund->errors();
    //     }
    //
    //     $container->get('flash')->addMessage('errors', $errors);
    //     return $this->edit($request, $response, $args);
    // }
    //
    // /**
    //  * Switch the current fund to another
    //  */
    // public function switch($request, $response, $args)
    // {
    //     $params = $request->getParams();
    //     $container = $this->getContainer();
    //     $currentUser = $this->getCurrentUser();
    //
    //     // change fund in session
    //     $newFund = $currentUser->funds()->findOrFail((int)$params['fund_id']);
    //     $container->get('session')->set('current_fund_id', $newFund->id);
    //
    //     // redirect back to transactions
    //     return $response->withRedirect('/transactions');
    // }

    // /**
    //  * Render the json and attach to the response
    //  * @param string $file Name of the template/ view to render
    //  * @param array $args Additional variables to pass to the view
    //  * @param Response?
    //  */
    // protected function renderJSON($data=array())
    // {
    //     $data = $data['funds'];
    //
    //     return parent::renderJSON($data);
    // }
}
