<?php
namespace App\Widget;

use App\Utils;

class RecentTransactions extends Base
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/recent_transactions';

    public function __construct($container)
    {
        parent::__construct($container);

        $userId = $container->get('auth')->getAttributes()['id'];
        $currentUser = $container->get('model.user')->find($userId);

        $transactions = $currentUser->transactions()
            ->orderBy('purchased_at', 'desc')
            ->limit(5)
            ->get();

        $this->data = $transactions;
    }
}
