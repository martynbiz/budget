<?php
namespace App\Model;

class Tag extends Base
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
        return $this->belongsToMany('App\\Model\\Transaction')->withTimestamps(); //, 'user_id');
    }
}
