<?php
namespace App;

use Slim\Http\Request;

use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Model\User;

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

    /**
     * get access token from header
     */
    public static function getTokenFromRequest(Request $request)
    {
        return ltrim( @current($request->getHeader('Authorization')), 'Bearer ' );
    }

    // /**
    //  * get access token from header
    //  */
    // public static function getBearerToken()
    // {
    //     $headers = self::getAuthorizationHeader();
    //     if (!empty($headers)) {
    //         if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
    //             return $matches[1];
    //         }
    //     }
    //     return null;
    // }
    //
    // /**
    //  * Get hearder Authorization
    //  */
    // public static function getAuthorizationHeader()
    // {
    //     $headers = null;
    //     if (isset($_SERVER['Authorization'])) {
    //         $headers = trim($_SERVER["Authorization"]);
    //     }
    //     else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
    //         $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    //     } elseif (function_exists('apache_request_headers')) {
    //         $requestHeaders = apache_request_headers();
    //         // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
    //         $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
    //         //print_r($requestHeaders);
    //         if (isset($requestHeaders['Authorization'])) {
    //             $headers = trim($requestHeaders['Authorization']);
    //         }
    //     }
    //     return $headers;
    // }
}
