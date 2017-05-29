<?php
namespace App\Model;

class Budget extends Base
{
    /**
     * @var array
     */
    protected $fillable = array(
        'amount',
        'category_id',
        'fund_id',
    );

    public function category()
    {
        return $this->belongsTo('App\\Model\\Category'); //, 'user_id');
    }

    public function fund()
    {
        return $this->belongsTo('App\\Model\\Fund'); //, 'user_id');
    }
}
