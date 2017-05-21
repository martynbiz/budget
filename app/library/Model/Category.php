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
        $baseQuery = $this->transactions();

        if ($query['start_date']) {
            $baseQuery->where('purchased_at', '>=', $query['start_date']);
        }

        if ($query['end_date']) {
            $baseQuery->where('purchased_at', '<=', $query['end_date']);
        }

        if ($query['fund']) {
            $baseQuery->where('fund_id', $query['fund']->id);
        }

        if (is_null($this->transactionsAmount)) {
            $this->transactionsAmount = $baseQuery
                ->pluck('amount')
                ->sum();
        }

        return $this->transactionsAmount; //, 'user_id');
    }
}
