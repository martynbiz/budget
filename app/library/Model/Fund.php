<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'currency',
    );
}
