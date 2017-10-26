<?php
namespace App\Model;

class User extends Base
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

    public function books()
    {
        return $this->hasMany('App\\Model\\Book'); //, 'user_id');
    }

    public function auth_tokens()
    {
        return $this->hasMany('App\\Model\\AuthToken'); //, 'user_id');
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
