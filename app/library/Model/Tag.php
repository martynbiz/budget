<?php
namespace App\Model;

class Tag extends Base
{
    use Traits\HasTransactions;

    /**
    * @var array
    */
    protected $fillable = array(
        'name',
        'budget',
        'currency_id',
    );

    public function transactions()
    {
        return $this->belongsToMany('App\\Model\\Transaction'); //, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    // public function transactions()
    // {
    //     return $this->belongsToMany('App\\Model\\Transaction')->withTimestamps(); //, 'user_id');
    // }

    // public function budgets()
    // {
    //     return $this->hasMany('App\\Model\\TagBudget'); //, 'user_id');
    // }
    //
    // /**
    //  * Get the budget object. If no $month is specified, get the latest one
    //  */
    // public function getBudgetByMonth($currency, $month=null)
    // {
    //     $baseQuery = $this->budgets()->where('currency_id', $currency->id);
    //
    //     // we want to get the latest budget until the end of $month
    //     if (!is_null($month)) {
    //         list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);
    //         $baseQuery->where('created_at', '<=', $endDate . ' 23:59:59');
    //     }
    //
    //     return $baseQuery->orderBy('created_at', 'desc')->first();
    // }
}
