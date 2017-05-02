<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
    * @var array
    */
    protected $fillable = array(
        // 'name',
        'description',
        'amount',
        'purchased_at',
        'category',
    );
}
