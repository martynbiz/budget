<?php
namespace App\Controller;

use App\Model\Categories;
use App\Validator;
use App\Utils;

class TagsController extends BaseController
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

        // get paginated rows
        $tags = $currentUser->tags()
            ->with('transactions')
            ->skip($start)
            ->take($limit)
            ->get();

        $totalTags = $currentUser->tags()->count();
        $totalPages = ($totalTags > 0) ? ceil($totalTags/$limit) : 1;

        return $this->render('tags/index', [
            'tags' => $tags,

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

        $groups = $currentUser->tags()
            ->orderBy('name', 'asc')
            ->get();

        return $this->render('tags/create', [
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
            ->isUniqueCategory( $i18n->translate('tag_name_not_unique'), $currentUser->tags());

        // if valid, create tag
        if ($validator->isValid()) {

            if ($tag = $currentUser->tags()->create($params)) {

                // // create budget in budgets
                // // TODO this ought to be an event
                // if ($budgetAmount = (int)$params['budget']) {
                //     $tag->budgets()->create([
                //         'amount' => $budgetAmount,
                //         'fund_id' => $this->currentFund->id,
                //     ]);
                // }

                // redirect
                return $response->withRedirect('/tags');

            } else {
                $errors = $tag->errors();
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
        $tag = $this->getCurrentUser()->tags()
            ->with('group')
            ->findOrFail((int)$args['tag_id']);

        $params = array_merge([
            'budget' => (int)@$tag->getBudgetByMonth($this->currentFund)->amount,
        ], $tag->toArray(), [
            'group' => $tag->group->name,
        ], $request->getParams());

        $groups = $currentUser->tags()
            ->orderBy('name', 'asc')
            ->get();

        return $this->render('tags/edit', [
            'params' => $params,
            'tag' => $tag,
        ]);
    }

    public function update($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        $tag = $container->get('model.tag')->findOrFail((int)$args['tag_id']);

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // name
        $validator->check('name')
            ->isNotEmpty( $i18n->translate('name_missing') )
            ->isUniqueCategory( $i18n->translate('tag_name_not_unique'), $currentUser->tags(), $tag);

        // if valid, create tag
        if ($validator->isValid()) {

            // // get tag
            // $group = $this->findOrCreateGroupByName($params['group']);
            // $params['group_id'] = $group->id;

            // get group
            if (!empty($params['group'])) {

                $group = $currentUser->groups()->firstOrCreate(['name' => $params['group']], [
                    'budget' => 0,
                    'group_id' => 0,
                ]);
            }
            $params['group_id'] = (int)$group->id;

            if ($tag->update($params)) {

                // update or create budget
                // we only want one budget row per month. only create if one
                // doesn't exist for the month
                $budgetAmount = (int)$params['budget'];// if budget is 0, then delete it
                $budget = $tag->budgets()
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
                    $tag->budgets()->create([
                        'amount' => $params['budget'],
                        'fund_id' => $this->currentFund->id,
                    ]);
                } elseif ($budget) {
                    $budget->delete();
                }

                // redirect
                return $response->withRedirect('/tags');

            } else {
                $errors = $tag->errors();
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

        $tag = $container->get('model.tag')->findOrFail((int)$args['tag_id']);
        $tagId = $tag->id;

        if ($tag->delete()) {

            // update transactions assigned to this tag
            $tag->transactions()
                ->where('tag_id', $tagId)
                ->update([
                    'tag_id' => null,
                ]);

            // redirect
            return $response->withRedirect('/tags');

        } else {
            $errors = $tag->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }
}
