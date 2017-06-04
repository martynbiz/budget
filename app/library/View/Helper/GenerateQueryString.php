<?php
namespace App\View\Helper;

class GenerateQueryString extends BaseHelper
{
    function __invoke($query)
    {
        $query = array_merge($_GET, $query);

        return http_build_query($query);
    }
}
