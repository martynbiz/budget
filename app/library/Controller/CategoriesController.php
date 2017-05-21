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

        // categories is actually a combined list of categories and groups
        // so we need to build that combined array here and use it to paginate
        $categoriesAndGroups = [];

        // first get the categories
        $categories = $currentUser->categories()
            ->with('group')
            ->with('transactions')
            ->orderBy('group_id')
            // ->skip($start)
            // ->take($limit)
            ->get();

        $currentGroupId = null; // so we know when to insert a group
        foreach ($categories as $category) {

            // first, insert the group if changed or first
            $group = $category->group;
            if ($group && $currentGroupId !== $group->id) {
                array_push($categoriesAndGroups, $group);
                $currentGroupId = $group->id;
            }

            // insert the category under it's group
            array_push($categoriesAndGroups, $category);
        }

        // set totals based on full categoriesAndGroups array
        $totalCategories = count($categoriesAndGroups); //$currentUser->categories()->count();
        $totalPages = ($totalCategories > 0) ? (int)ceil($totalCategories/$limit) : 1;

        // now, slice the array based on pagination
        $categoriesAndGroups = array_slice($categoriesAndGroups, $start, $limit);

        // $parents = $currentUser->categories()->get();

        return $this->render('categories/index', [
            'categories' => $categoriesAndGroups,
            // 'parent' => $parents,

            'current_fund' => $this->currentFund,

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
            ->isNotEmpty( $i18n->translate('name_missing') )
            ->isUniqueCategory( $i18n->translate('category_name_not_unique'), $currentUser->categories());

        // if valid, create category
        if ($validator->isValid()) {

            // get group
            if (!empty($params['group'])) {
                $group = $this->findOrCreateGroupByName($params['group']);
                $params['group_id'] = $group->id;
            }

            if ($category = $currentUser->categories()->create($params)) {

                // redirect
                return $response->withRedirect('/categories');

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

        $params = array_merge($category->toArray(), [
            'group' => $category->group->name,
        ], $request->getParams());

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

        $category = $container->get('model.category')->findOrFail((int)$args['category_id']);

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // name
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') )
            ->isUniqueCategory( $i18n->translate('category_name_not_unique'), $currentUser->categories(), $category);

        // if valid, create category
        if ($validator->isValid()) {

            // get category
            $group = $this->findOrCreateGroupByName($params['group']);
            $params['group_id'] = $group->id;

            if ($category->update($params)) {

                // redirect
                return $response->withRedirect('/categories');

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
        $categoryId = $category->id;

        if ($category->delete()) {

            // update transactions assigned to this category
            $uncatogorizedCategory = $this->findOrCreateCategoryByName('');
            $category->transactions()
                ->where('category_id', $categoryId)
                ->update([
                    'category_id' => $uncatogorizedCategory->id,
                ]);

            // redirect
            return $response->withRedirect('/categories');

        } else {
            $errors = $category->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->forward('create', func_get_args());
    }
}
