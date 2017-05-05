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
        $limit = 10;
        $start = ($page-1) * $limit;

        $categories = $currentUser->categories()
            ->with('group')
            ->orderBy('name')
            ->skip($start)
            ->take($limit)
            ->get();

        // TODO needs to be set against date range
        $totalCategories = $currentUser->categories()->count();
        $totalPages = ($totalCategories > 0) ? ceil($totalCategories/$limit) : 1;

        $parents = $currentUser->categories()
            ->get();

        return $this->render('categories/index', [
            'categories' => $categories,
            'parent' => $parents,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        // if errors found from post, this will contain data
        $params = $request->getParams();

        $groups = $currentUser->categories()
            ->orderBy('name', 'asc')
            ->get();

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

        // name
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        // group
        $validator->check('group')
            ->isNotEmpty( $i18n->translate('group_missing') );

        // if valid, create category
        if ($validator->isValid()) {

            // find or create category
            if (!$group = $currentUser->groups()->where('name', $params['group'])->first()) {
                $group = $currentUser->groups()->create([
                    'name' => $params['group'],
                    'group_id' => 0,
                ]);
            }
            $params['group_id'] = $group->id;

            if ($category = $currentUser->categories()->create($params)) {

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

    public function edit($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $container = $this->getContainer();
        $category = $this->getCurrentUser()->categories()
            ->with('group')
            ->findOrFail((int)$args['category_id']);

        // if errors found from post, this will contain data
        $params = array_merge($category->toArray(), $request->getParams());

        $groups = $currentUser->categories()
            ->orderBy('name', 'asc')
            ->get();

        return $this->render('categories/edit', [
            'params' => $params,
            'category' => $category,
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

        // name
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') );

        // group
        $validator->check('group')
            ->isNotEmpty( $i18n->translate('group_missing') );

        // if valid, create category
        if ($validator->isValid()) {

            $category = $container->get('model.category')->findOrFail((int)$args['category_id']);

            // find or create category
            if (!$group = $currentUser->groups()->where('name', $params['group'])->first()) {
                $group = $currentUser->groups()->create([
                    'name' => $params['group'],
                    'group_id' => 0,
                ]);
            }
            $params['group_id'] = $group->id;

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
