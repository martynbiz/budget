<?php
namespace App\Controller;

use App\Model\Categories;
use App\Validator;
use App\Utils;

class CategoriesController extends BaseController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $options = array_merge([
            'page' => 1,
        ], $request->getQueryParams());

        $page = (int)$options['page'];
        $limit = 20;
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
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();
        $params = $request->getParams();

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

                // create budget in budgets
                // TODO this ought to be an event
                if ($budgetAmount = (int)$params['budget']) {
                    $category->budgets()->create([
                        'amount' => $budgetAmount,
                        'fund_id' => $this->currentFund->id,
                    ]);
                }

                // redirect
                return $response->withRedirect('/categories');

            } else {
                $errors = $category->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    public function edit($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $container = $this->getContainer();
        $category = $this->getCurrentUser()->categories()
            ->with('group')
            ->findOrFail((int)$args['category_id']);

        $params = array_merge([
            'budget' => (int)@$category->getBudgetByMonth($this->currentFund)->amount,
        ], $category->toArray(), [
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

                // update or create budget
                // we only want one budget row per month. only create if one
                // doesn't exist for the month
                $budgetAmount = (int)$params['budget'];// if budget is 0, then delete it
                $budget = $category->budgets()
                    ->where('fund_id', $this->currentFund->id)
                    ->whereBetween('created_at', Utils::getStartEndDateByMonth( date('Y-m') )) // this month
                    ->first();
                if ($budgetAmount > 0 && $budget) {
                    if ($budgetAmount != $budget->amount) {
                        $budget->update([
                            'amount' => $params['budget'],
                        ]);
                    }
                } elseif ($budgetAmount > 0) {
                    $category->budgets()->create([
                        'amount' => $params['budget'],
                        'fund_id' => $this->currentFund->id,
                    ]);
                } elseif ($budget) {
                    $budget->delete();
                }

                // redirect
                return $response->withRedirect('/categories');

            } else {
                $errors = $category->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    public function delete($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

        $category = $container->get('model.category')->findOrFail((int)$args['category_id']);
        $categoryId = $category->id;

        if ($category->delete()) {

            // update transactions assigned to this category
            $category->transactions()
                ->where('category_id', $categoryId)
                ->update([
                    'category_id' => null,
                ]);

            // redirect
            return $response->withRedirect('/categories');

        } else {
            $errors = $category->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }
}
