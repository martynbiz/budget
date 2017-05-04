<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'currency',
    );

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    public function categories()
    {
        return $this->hasMany('App\\Model\\Category'); //, 'user_id');
    }
}
