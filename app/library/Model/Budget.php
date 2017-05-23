<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
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
