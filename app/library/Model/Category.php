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

    public function getAmountAttribute()
    {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $transactionsAmount = $this->transactions()
            ->where('purchased_at', '>=', $startDate)
            ->where('purchased_at', '<=', $endDate)
            ->pluck('amount')
            ->sum();

        return $transactionsAmount; //, 'user_id');
    }

    public function getBalanceAttribute()
    {
        return $this->budget - $this->amount; //, 'user_id');
    }
}
