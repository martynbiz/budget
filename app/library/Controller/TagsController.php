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
}
