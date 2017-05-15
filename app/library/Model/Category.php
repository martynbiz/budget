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

    public function getAmount($startDate, $endDate)
    {
        $this->transactionsAmount = $this->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->pluck('amount')
            ->sum();

        return $transactionsAmount; //, 'user_id');
    }

    public function getBalance($startDate, $endDate)
    {
        return $this->budget + $this->getAmount($startDate, $endDate); //, 'user_id');
    }
}
