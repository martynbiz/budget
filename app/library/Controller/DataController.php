<?php
namespace App\Controller;

class DataController extends BaseController
{
    /**
     * Get categories for the autocomplete
     */
    public function categories($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();
        $categories = $currentUser->categories()->pluck('name');
        return $this->renderJSON($categories->toArray());
    }

    /**
     * Get groups for the autocomplete
     */
    public function groups($request, $response, $args)
    {
        $currentUser = $this->getCurrentUser();
        $categories = $currentUser->groups()->pluck('name');
        return $this->renderJSON($categories->toArray());
    }
}
