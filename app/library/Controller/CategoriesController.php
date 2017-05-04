<?php
namespace App\Controller;

use App\Model\Categories;
use App\Validator;

class CategoriesController extends BaseController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $options = array_merge([
            'page' => 1,
        ], $request->getQueryParams());

        $page = (int)$options['page'];
        $limit = 1;
        $start = ($page-1) * $limit;

        // get paginated rows
        $categories = $currentUser->categories()
            ->with('category_group')
            ->skip($start)
            ->take($limit)
            ->get();

        // TODO needs to be set against date range
        $totalCategories = $currentUser->categories()->count();
        $totalPages = ($totalCategories > 0) ? ceil($totalCategories/$limit) : 1;

        return $this->render('categories/index', [
            'categories' => $categories,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('categories/create', [
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
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        // if valid, create category
        if ($validator->isValid()) {

            if ($category = $currentUser->categories()->create($params)) {

                // redirect
                isset($params['returnTo']) or $params['returnTo'] = '/';
                return $this->returnTo($params['returnTo']);

            } else {
                $errors = $category->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('create', func_get_args());
    }

    public function edit($request, $response, $args)
    {
        $container = $this->getContainer();
        $category = $this->getCurrentUser()->categories()->findOrFail((int)$args['category_id']);

        // if errors found from post, this will contain data
        $params = array_merge($category->toArray(), $request->getParams());

        return $this->render('categories/edit', [
            'params' => $params,
            'category' => $category,
        ]);
    }

    public function update($request, $response, $args)
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
        $validator->check('category_id')
            ->isNotEmpty( $i18n->translate('category_missing') );

        // purchased at
        $validator->check('purchased_at')
            ->isNotEmpty( $i18n->translate('purchased_at_missing') );

        // if valid, create category
        if ($validator->isValid()) {

            $category = $container->get('model.category')->findOrFail((int)$args['category_id']);

            if ($category->update($params)) {

                // redirect
                isset($params['returnTo']) or $params['returnTo'] = '/categories';
                return $this->returnTo($params['returnTo']);

            } else {
                $errors = $category->errors();
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

        $category = $container->get('model.category')->findOrFail((int)$args['category_id']);

        if ($category->delete()) {

            // redirect
            isset($params['returnTo']) or $params['returnTo'] = '/categories';
            return $this->returnTo($params['returnTo']);

        } else {
            $errors = $category->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('create', func_get_args());
    }
}
