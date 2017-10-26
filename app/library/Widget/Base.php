<?php
namespace App\Widget;

class Base
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $templateFile = null;

    /**
     * @var mixed
     */
    protected $data = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Get the widget data for the template
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the widget data for the template
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }
}
