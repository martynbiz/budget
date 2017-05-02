<?php
namespace App\Controller;

use App\Model\Funds;
use App\Validator;

class FundsController extends BaseController
{
    public function index($request, $response, $args)
    {
        return $this->render('funds/index');
    }

    public function create($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('funds/create', [
            'params' => $params,
        ]);
    }

    public function post($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

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

            if ($transaction = $container->get('model.transaction')->create($params)) {

                // redirect
                isset($params['returnTo']) or $params['returnTo'] = '/';
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
        $id = $args['fund_id'];

        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('funds/edit', [
            'params' => $params,
        ]);
    }
}
