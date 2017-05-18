<?php
namespace App\Middleware;

class Base
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
}
