<?php
namespace App\Model;

class ApiToken extends Base
{
    /**
    * @var array
    */
    protected $fillable = array(
        'user_id',
        'value',
        'expires_at',
    );

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    /**
     * Helper function to check the expired_at date to see if it has passed
     * @return boolean
     */
    public function hasExpired()
    {
        return $this->expired_at > date('Y-m-d H:i:s');
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
