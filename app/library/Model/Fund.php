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
        'amount',
        'currency_id',
    );

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\\Model\\Transaction'); //, 'user_id');
    }
}
