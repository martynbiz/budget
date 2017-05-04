<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'parent_id',
    );

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\\Model\\Transaction'); //, 'user_id');
    }

    public function parent()
    {
        return $this->hasMany('App\\Model\\Category', 'parent_id');
    }
}
