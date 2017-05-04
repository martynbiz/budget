<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'format',
    );
}
