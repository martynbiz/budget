<?php
namespace App\Model;

class UserSetting extends Base
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'value',
    );
}
