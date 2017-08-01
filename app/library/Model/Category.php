<?php
namespace App\Model;

use App\Utils;

class Category extends Base
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'budget',
        'group_id',
    );

    /**
     * @var
     */
    protected $transactionsAmount;

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\\Model\\Transaction'); //, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo('App\\Model\\Group'); //, 'user_id');
    }

    public function getTransactionsAmount($query=[])
    {
        $transactionsAmount = $this->transactions()->whereQuery($query)
            ->pluck('amount')
            ->sum();

        return $transactionsAmount;
    }
}
