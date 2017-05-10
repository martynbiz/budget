<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const SESSION_FILTER_MONTH = 'transaction-filter__month';

    /**
    * @var array
    */
    protected $fillable = array(
        'description',
        'amount',
        'purchased_at',
        'category_id',
        'user_id',
        'fund_id',
    );

    public function user()
    {
        return $this->belongsTo('App\\Model\\User'); //, 'user_id');
    }

    public function fund()
    {
        return $this->belongsTo('App\\Model\\Fund'); //, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo('App\\Model\\Category'); //, 'user_id');
    }
}
