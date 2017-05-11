<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
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

    public function categories()
    {
        return $this->hasMany('App\\Model\\Category');
    }

    public function getAmountAttribute()
    {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $categories = $this->categories()->with('transactions')->get();

        $amount = 0;
        foreach($categories as $category) $amount += $category->amount;

        return $amount; 
    }

    public function getBalanceAttribute()
    {
        return $this->budget + $this->amount; //, 'user_id');
    }
}
