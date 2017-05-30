<?php
namespace App\Model;

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

    /**
     * Will attach a "tag1,tag2,tag3" string of tags to the transactions. If the
     * tag doesn't exist, it will be created
     * @param string $tagsString Tag string eg. "tag1,tag2,tag3"
     */
    public function setTagsByTagsString($tagsString) {

        $currentUser = $this->user;

        // just clear existing tags as we'll create new pivot links
        $this->tags()->detach();

        // create tags (if any)
        $tagsArray = array_map('trim', explode(',', $tagsString));
        foreach ($tagsArray as $name) {

            if (empty($name)) continue;

            // first try to find an existing tag, if none exist, create a
            // new one
            if (!$tag = $currentUser->tags()->where('name', $name)->first()) {
                $tag = $currentUser->tags()->create([
                    'name' => $name,
                ]);
            }

            $this->tags()->attach($tag);
        }
    }
}
