<?php
namespace App\Model;

class Group extends Base
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

    public function getTransactionsAmount($query=[])
    {
        $categories = $this->categories()->with('transactions')->get();

        $amount = 0;
        foreach($categories as $category) $amount += $category->getTransactionsAmount($query);

        return $amount;
    }
}
