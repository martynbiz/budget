<?php
namespace App\View\Helper;

class GenerateQueryString
{
    /**
     * Slim\Container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    function __invoke($query)
    {
        $query = array_merge($_GET, $query);

        return http_build_query($query);
    }
}
