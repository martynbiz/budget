<?php
namespace App\View\Helper;

class BaseHelper
{
    /**
     * Slim\Container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
}
