<?php
namespace App\Model;

class Book extends Base
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
    );

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }
}
