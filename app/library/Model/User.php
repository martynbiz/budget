<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * @var array
     */
    protected $hidden = array(
        'password',
        'salt',
    );

    /**
    * @var array
    */
    protected $fillable = array(
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
    );

    public function transactions()
    {
        return $this->hasMany('App\\Model\\Transaction'); //, 'user_id');
    }

    public function funds()
    {
        return $this->hasMany('App\\Model\\Fund'); //, 'user_id');
    }

    public function categories()
    {
        return $this->hasMany('App\\Model\\Category'); //, 'user_id');
    }

    public function groups()
    {
        return $this->hasMany('App\\Model\\Group'); //, 'user_id');
    }

    public function auth_token()
    {
        return $this->hasOne('App\\Model\\AuthToken'); //, 'user_id');
    }

    public function recovery_token()
    {
        return $this->hasOne('App\\Model\\RecoveryToken'); //, 'user_id');
    }

    /**
     * Instead of using boot()::saving events, which don't seem
     * to work properly in tests, I'm just overriding the save()
     * method here.
     *
     * @param $options array
     * @return
     */
    public function save(array $options = [])
    {
        // set the password to a random string if empty, this is typically the
        // case when a silent user is created during a facebook login
        if (empty($this->password)) {
            $this->password = uniqid();
        }

        // username, if not set, generate from first and last name - ensure it's unique
        if (empty($this->username)) {

            $base = strtolower($this->first_name . '.' . $this->last_name);

            do {
                $username = $base . @$suffix;
                $duplicate = User::where('username', '=', $username)->first();
            } while($duplicate and $suffix = rand(1000, 9999));

            // return the original/ generated username
            $this->username = $username;
        }

        return parent::save($options);
    }

    // public static function boot()
    // {
    //     parent::boot();
    //
    //     static::creating(function ($user) {
    //
    //         // set the password to a random string if empty, this is typically the
    //         // case when a silent user is created during a facebook login
    //         if (empty($user->password)) {
    //             $user->password = uniqid();
    //         }
    //
    //         // username, if not set, generate from first and last name - ensure it's unique
    //         if (empty($user->username)) {
    //
    //             $base = strtolower($user->first_name . '.' . $user->last_name);
    //
    //             do {
    //                 $username = $base . @$suffix;
    //                 $duplicate = User::where('username', '=', $username)->first();
    //             } while($duplicate and $suffix = rand(1000, 9999));
    //
    //             // return the original/ generated username
    //             $user->username = $username;
    //         }
    //     });
    // }

    /**
     * Encrypt password upon setting, set salt too
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash(
            $value,
            PASSWORD_BCRYPT,
            ['cost' => 12]
        );
    }

    /**
     * Scope a query to find a user by email
     * Makes testing easier when we don't have to chain eloquent methods
     * @param Query? $query
     * @param string $email
     * @return User|null
     */
    public function findByAuthTokenSelector($selector)
    {
        $authToken = AuthToken::where('selector', $selector)
            ->first();

        if ($authToken) {
            return $authToken->user;
        } else {
            return null;
        }
    }

    /**
     * Gravatar image url from email
     */
    public function getGravatarImageUrl($size=80)
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?s=' . $size;
    }
}
