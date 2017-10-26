<?php
namespace App\Model;

class Currency extends Base
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'format',
    );
}
