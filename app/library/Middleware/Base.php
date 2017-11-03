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

    /**
     * Get the current sign in user user
     * @param Request $request Not really needed here, api uses it though
     * @return User|null
     */
    protected function getCurrentUser()
    {
        // cache current user as a property
        $container = $this->container;
        $attributes = $container->get('auth')->getAttributes();
        $currentUser =  $container->get('model.user')->where('email', $attributes['email'])->first();

        return $currentUser;
    }
}
