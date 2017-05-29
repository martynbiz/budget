<?php
namespace App\Model;

class Fund extends Base
{
    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'amount',
        'currency_id',
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

    public function currency()
    {
        return $this->belongsTo('App\\Model\\Currency'); //, 'user_id');
    }

    public function getAmount()
    {
        if (is_null($this->transactionsAmount)) {
            $this->transactionsAmount = $this->transactions()
                ->pluck('amount')
                ->sum();
        }

        return $this->transactionsAmount;
    }
}
