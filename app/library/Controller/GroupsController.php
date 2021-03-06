<?php
namespace App\Controller;

use App\Validator;

class GroupsController extends BaseController
{
    public function index($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $options = array_merge([
            'page' => 1,
        ], $request->getQueryParams());

        $page = (int)$options['page'];
        $limit = (int)$request->getQueryParam('limit', 20);
        $start = ($page-1) * $limit;

        // get paginated rows
        $groups = $currentUser->groups()
            ->orderBy('name', 'asc')
            ->skip($start)
            ->take($limit)
            ->get();

        // TODO needs to be set against date range
        $totalGroups = $currentUser->groups()->count();
        $totalPages = ($totalGroups > 0) ? ceil($totalGroups/$limit) : 1;

        return $this->render('groups/index', [
            'groups' => $groups,

            // pagination
            'total_pages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function create($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('groups/create', [
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
            ->isUniqueGroup( $i18n->translate('group_name_not_unique'), $currentUser->groups() );

        // if valid, create group
        if ($validator->isValid()) {

            if ($group = $currentUser->groups()->create($params)) {

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor('categories') );

            } else {
                $errors = $group->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->create($request, $response, $args);
    }

    public function edit($request, $response, $args)
    {
        $container = $this->getContainer();
        $group = $this->getCurrentUser()->groups()->findOrFail((int)$args['group_id']);

        // if errors found from post, this will contain data
        $params = array_merge($group->toArray(), $request->getParams());

        return $this->render('groups/edit', [
            'params' => $params,
            'group' => $group,
        ]);
    }

    public function update($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $currentUser = $this->getCurrentUser();

        $group = $currentUser->groups()->findOrFail((int)$args['group_id']);

        // validate form data

        // our simple custom validator for the form
        $validator = new Validator();
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // name
        $validator->check('name')
            ->isNotEmpty($i18n->translate('name_missing'))
            ->isUniqueGroup($i18n->translate('group_name_not_unique'), $currentUser->groups(), $group);

        // if valid, create group
        if ($validator->isValid()) {

            if ($group->update($params)) {

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor('categories') );

            } else {
                $errors = $group->errors();
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
        $currentUser = $this->getCurrentUser();

        $group = $currentUser->groups()->findOrFail((int)$args['group_id']);
        $groupId = $group->id;

        if ($group->delete()) {

            // update transactions assigned to this category
            $group->categories()
                ->where('group_id', $groupId)
                ->update([
                    'group_id' => null,
                ]);

            // redirect
            return $response->withRedirect( $container->get('router')->pathFor('categories') );

        } else {
            $errors = $group->errors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->edit($request, $response, $args);
    }

    /**
     * Render the json and attach to the response
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    protected function renderJSON($data=array())
    {
        $data = $data['groups'];

        return parent::renderJSON($data);
    }
}
