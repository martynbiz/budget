<?php
namespace App;

use App\Model\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Extension of MartynBiz\Validator so we can define custom validation classes
 */
class Validator extends \MartynBiz\Validator
{
    /**
     * Check with our user model that the email is valid
     * @param string $message Custom message when validation fails
     * @param User $model This will be used to query the db
     * @param mixed $updateItem The item that is being updated, can be the same as itself
     * @return Validator
     */
    public function isUniqueEmail($message, User $model, $updateItem=null)
    {
        //check whether this email exists in the db
        $query = $model->where('email', '=', $this->value);

        // if updatedItem
        if (!is_null($updateItem)) {
            $query = $model->where('id', '!=', $updateItem->id);
        }

        $user = $query->first();

        // log error
        if ($user) {
            $this->logError($this->key, $message);
        }

        // return instance
        return $this;
    }

    /**
     * Check that the category is valid
     * @param string $message Custom message when validation fails
     * @param User $model This will be used to query the db
     * @param mixed $updateItem The item that is being updated, can be the same as itself
     * @return Validator
     */
    public function isUniqueCategory($message, HasMany $model, $updateItem=null)
    {
        //check whether this email exists in the db
        $query = $model->where('name', '=', $this->value);

        // if updatedItem
        if (!is_null($updateItem)) {
            $query = $model->where('id', '!=', $updateItem->id);
        }

        $category = $query->first();

        // log error
        if ($category) {
            $this->logError($this->key, $message);
        }

        // return instance
        return $this;
    }

    /**
     * Check that the tag is valid
     * @param string $message Custom message when validation fails
     * @param User $model This will be used to query the db
     * @param mixed $updateItem The item that is being updated, can be the same as itself
     * @return Validator
     */
    public function isUniqueTag($message, HasMany $model, $updateItem=null)
    {
        //check whether this email exists in the db
        $query = $model->where('name', '=', $this->value);

        // if updatedItem
        if (!is_null($updateItem)) {
            $query = $model->where('id', '!=', $updateItem->id);
        }

        $tag = $query->first();

        // log error
        if ($tag) {
            $this->logError($this->key, $message);
        }

        // return instance
        return $this;
    }

    /**
     * Check that the group is valid
     * @param string $message Custom message when validation fails
     * @param User $model This will be used to query the db
     * @return Validator
     */
    public function isUniqueGroup($message, HasMany $model, $updateItem=null)
    {
        //check whether this email exists in the db
        $query = $model->where('name', '=', $this->value);

        // if updatedItem
        if (!is_null($updateItem)) {
            $query = $model->where('id', '!=', $updateItem->id);
        }

        $group = $query->first();

        // log error
        if ($group) {
            $this->logError($this->key, $message);
        }

        // return instance
        return $this;
    }

    // /**
    //  * Used when we change a users email address, it must be unique in the database
    //  * other than the current email address (which the )
    //  * @param string $currentEmail This is the users current email before update
    //  * @param string $message Custom message when validation fails
    //  * @param User $model This will be used to query the db
    //  * @return Validator
    //  */
    // public function isUpdateUniqueEmail($currentEmail, $message, User $model)
    // {
    //     // check if the email being changed is the same as the users email anyway
    //     // in which case we won't throw an error
    //     // user can change the email to their current one - bit dumb, and slightly
    //     // wasteful perhaps, but don't think it should create an error as other
    //     // update params would also not be saved.
    //     if ($this->value == $currentEmail) {
    //         return true;
    //     }
    //
    //     //check whether this email exists in the db
    //     $user = $model->where('email', '=', $this->value)
    //         ->first();
    //
    //     // log error
    //     if ($user) {
    //         $this->logError($this->key, $message);
    //     }
    //
    //     // return instance
    //     return $this;
    // }

    /**
     * This is just a re-usable method for this module, so we can use it again (register and lost/ change pw)
     * @param string $message Custom message when validation fails
     * @return Validator
     */
    public function isValidPassword($message)
    {
        return $this->isNotEmpty($message)
            ->isMinimumLength($message, 8)
            ->hasUpperCase($message)
            ->hasLowerCase($message)
            ->hasNumber($message);
    }
}
