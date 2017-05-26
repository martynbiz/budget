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

    public function budgets()
    {
        return $this->hasMany('App\\Model\\Budget'); //, 'user_id');
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

    /**
     * Get the budget amount
     * DECREPITATED, use getBudgetByMonth(..)->amount
     */
    public function getBudget($fund)
    {
        $budget = $this->budgets()
            ->where('fund_id', $fund->id)
            ->first();

        return $budget->amount;
    }

    /**
     * Get the budget object. If no $month is specified, get the latest one
     */
    public function getBudgetByMonth($fund, $month=null)
    {
        $baseQuery = $this->budgets()->where('fund_id', $fund->id);

        if (!is_null($month)) {
            list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);
            $baseQuery->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        return $baseQuery->orderBy('created_at', 'desc')->first();
    }
}
