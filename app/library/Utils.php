<?php
namespace App;

use App\Model\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Extension of MartynBiz\Validator so we can define custom validation classes
 */
class Utils
{
    /**
     * Generate a start and end datetime for using in queries from
     * Usage: list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);
     * @param string $message Custom message when validation fails
     * @param User $model This will be used to query the db
     * @return Validator
     */
    public static function getStartEndDateByMonth($month)
    {
        $startDate = date('Y-m-01', strtotime($month . '-01'));
        $endDate = date('Y-m-t', strtotime($startDate));

        return [$startDate, $endDate];
    }
}
