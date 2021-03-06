<?php
namespace App\Controller;

use App\Validator;

class UsersController extends BaseController
{
    public function register($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('users/register', [
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

        // first_name
        $validator->check('first_name')
            ->isNotEmpty( $i18n->translate('first_name_missing') );

        // last_name
        $validator->check('last_name')
            ->isNotEmpty( $i18n->translate('last_name_missing') );

        // email
        $validator->check('email')
            ->isNotEmpty( $i18n->translate('email_missing') )
            ->isEmail( $i18n->translate('email_invalid') )
            ->isUniqueEmail( $i18n->translate('email_not_unique'), $container->get('model.user') );

        // password
        $message = $i18n->translate('password_must_contain');
        $validator->check('password')
            ->isNotEmpty($message)
            ->hasLowerCase($message)
            ->hasNumber($message)
            ->isMinimumLength($message, 8);

        // agreement
        $validator->check('agreement');

        // more_info
        // more info is a invisible field (not type=hidden, use css)
        // that humans won't see however, when bots turn up they
        // don't know that and fill it in. so, if it's filled in,
        // we know this is a bot
        if ($validator->has('more_info')) {
            $validator->check('more_info')
                ->isEmpty( $i18n->translate('email_not_unique') ); // misleading msg ;)
        }

        // if valid, create user

        if ($validator->isValid()) {

            if ($user = $container->get('model.user')->create($params)) {

                // set meta entries (if given)
                if (isset($params['source'])) $user->setMeta('source', $params['source']);

                // set session attributes w/ backend (method of signin)
                $container->get('auth')->setAttributes( $user->toArray() );

                // send welcome email
                $container->get('mail_manager')->sendWelcomeEmail($user);

                // redirect
                return $response->withRedirect('/');

            } else {
                $errors = $user->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->register($request, $response, $args);
    }

    /**
     * edit transaction form
     */
    public function edit($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $params = array_merge($currentUser->toArray(), $currentUser->getSettings(), $request->getParams());

        return $this->render('users/edit', [
            'params' => $params,
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

        // language
        $validator->check('language')
            ->isNotEmpty( $i18n->translate('language_missing') );

        // // amount
        // $validator->check('amount')
        //     ->isNotEmpty( $i18n->translate('amount_missing') );
        //
        // // purchased at
        // $validator->check('purchased_at')
        //     ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create transaction
        if ($validator->isValid()) {

            if ($currentUser->update($params)) {

                $currentUser->setSettings($params);

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor('home') );

            } else {
                $errors = $currentUser->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }

    public function delete($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

        $user = $container->get('model.user')->findOrFail((int)$args['user_id']);

        // remove all transactions
        $transactions = $user->transactions;
        $transactions->remove();

        // remove all funds
        $transactions = $user->transactions;
        $transactions->remove();

        // remove all categories
        $categories = $user->categories;
        $categories->remove();

        // remove all groups
        $groups = $user->groups;
        $groups->remove();

        // remove all recoverTokens
        $recoverTokens = $user->recover_tokens;
        $recoverTokens->remove();

        // remove all authTokens
        $authTokens = $user->auth_tokens;
        $authTokens->remove();

        if ($user->delete()) {

            // redirect
            return $response->withRedirect('users');

        } else {
            $errors = $user->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }

    /**
     *
     */
    public function switchLanguage($request, $response, $args)
    {
        $container = $this->getContainer();
        setcookie('language', $request->getParam('language'));
        return $response->withRedirect( $container->get('router')->pathFor('users_switch_language') );
    }
}
