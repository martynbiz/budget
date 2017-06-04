<?php
namespace App\View\Helper;

class Translate extends BaseHelper
{
    function __invoke($str)
    {
        return $this->container['i18n']->translate($str);
    }
}
