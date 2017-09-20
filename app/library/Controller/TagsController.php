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
        $limit = (int)$request->getQueryParam('limit', 20);
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

    /**
     * create tag form
     */
    public function create($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('tags/create', [
            'params' => $params,
        ]);
    }

    /**
     * create tag form action
     */
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
            ->isUniqueTag( $i18n->translate('tag_name_not_unique'), $currentUser->tags());

        // if valid, create tag
        if ($validator->isValid()) {

            if ($tag = $currentUser->tags()->create($params)) {

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

    /**
     * edit tag form
     */
    public function edit($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();

        $container = $this->getContainer();
        $tag = $this->getCurrentUser()->tags()->findOrFail((int)$args['tag_id']);

        $params = array_merge($tag->toArray(), $request->getParams());

        return $this->render('tags/edit', [
            'params' => $params,
            'tag' => $tag,
        ]);
    }

    /**
     * edit tag form action
     */
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
            ->isUniqueTag( $i18n->translate('tag_name_not_unique'), $currentUser->tags(), $tag);

        // if valid, create tag
        if ($validator->isValid()) {

            if ($tag->update($params)) {

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

    /**
     * delete tag form action
     */
    public function delete($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();

        $tag = $container->get('model.tag')->findOrFail((int)$args['tag_id']);
        $tagId = $tag->id;

        if ($tag->delete()) {

            // redirect
            return $response->withRedirect('/tags');

        } else {
            $errors = $tag->errors();
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
        $data = $data['tags'];

        return parent::renderJSON($data);
    }
}
