<?php
namespace App\View\Helper;

class BalanceClass extends BaseHelper
{
    function __invoke($balance)
    {
        switch ($balance <=> 0) {
            case 1:
                return 'balance-in';
            case -1:
                return 'balance-out';
            case 0;
                return 'balance-even';
        }
    }
}
