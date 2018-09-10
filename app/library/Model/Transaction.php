<?php
namespace App\Model;

use App\Utils;

class Transaction extends Base
{
    // const SESSION_FILTER_START_DATE = 'transactions-filter__start-date';
    // const SESSION_FILTER_END_DATE = 'transactions-filter__end-date';


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

    public function tags()
    {
        return $this->belongsToMany('App\\Model\\Tag')->withTimestamps(); //, 'user_id');
    }

    public function scopeOrderByCategoryName($query, $dir)
    {
        return $query->leftJoin('categories', 'categories.id', '=', 'transactions.category_id')
            ->orderBy('categories.name', $dir)
            ->orderBy('transactions.id', $dir);
    }

    public function getPurchasedStringAttribute()
    {
        if (strtotime($this->purchased_at) >= strtotime("tomorrow")) {
            $purchasedString = date('j M', strtotime($this->purchased_at));
        } else if (strtotime($this->purchased_at) >= strtotime("today")) {
            $purchasedString = 'Today';
        } else if (strtotime($this->purchased_at) >= strtotime("yesterday")) {
            $purchasedString = 'Yesterday';
        } else if (strtotime($this->purchased_at) <= strtotime('Y-01-01 00:00:00')) {
            $purchasedString = date('j M, Y', strtotime($this->purchased_at));
        } else {
            $purchasedString = date('j M', strtotime($this->purchased_at));
        }

        return $purchasedString;
    }

    public function attachTagsByArray($tagsArray) {

        if (!is_array($tagsArray)) return;

        $currentUser = $this->user;

        // just clear existing tags as we'll create new pivot links
        $this->tags()->detach();

        foreach ($tagsArray as $name) {

            if (empty($name)) continue;

            // first try to find an existing tag, if none exist, create a
            // new one
            if (!$tag = $currentUser->tags()->where('name', $name)->first()) {
                $tag = $currentUser->tags()->create([
                    'name' => $name,
                    'budget' => 0,
                ]);
            }

            $this->tags()->attach($tag);
        }
    }

    /**
     * Will attach a "tag1,tag2,tag3" string of tags to the transactions. If the
     * tag doesn't exist, it will be created
     * @param string $tagsString Tag string eg. "tag1,tag2,tag3"
     */
    public function attachTagsByStringList($tagsString) {
        $tagsArray = array_map('trim', explode(',', $tagsString));
        $this->attachTagsByArray($tagsArray);
    }

    public function scopeWhereQuery($baseQuery, $query=[])
    {
        // set date range from start_date/end_date, otherwise from month if given
        if (!is_null(@$query['start_date']) or !is_null(@$query['end_date'])) {

            if ($startDate = @$query['start_date']) {
                $baseQuery->where('purchased_at', '>=', $startDate);
            }

            if ($endDate = @$query['end_date']) {
                $baseQuery->where('purchased_at', '<=', $endDate);
            }

        } elseif (!empty(@$query['month'])) {
            $startEndDates = Utils::getStartEndDateByMonth($query['month']);
            $baseQuery->whereBetween('purchased_at', $startEndDates);
        }

        if (!empty(@$query['fund'])) {
            $baseQuery->where('fund_id', $query['fund']);
        }

        if (!empty(@$query['category'])) {
            $baseQuery->where('category_id', $query['category']);
        }

        if (!empty(@$query['tag'])) {
            $baseQuery
                ->join('tag_transaction', 'transactions.id', '=', 'tag_transaction.transaction_id')
                ->join('tags', 'tag_transaction.tag_id', '=', 'tags.id')
                ->where('tag_transaction.tag_id', (int)$query['tag']);
        }

        return $baseQuery;
    }
}
