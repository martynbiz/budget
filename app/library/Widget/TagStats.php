<?php
namespace App\Widget;

use App\Utils;

class TagStats extends Base
{
    /**
     * @var string
     */
    protected $templateFile = 'widgets/tag_stats';

    public function __construct($container)
    {
        parent::__construct($container);

        // tag stats widget

        $month = $container->get('session')->get(SESSION_FILTER_MONTH);
        list($startDate, $endDate) = Utils::getStartEndDateByMonth($month);

        $userId = $container->get('auth')->getAttributes()['id'];
        $currentUser = $container->get('model.user')->find($userId);

        // first get the tags
        $tags = $currentUser->tags()
            ->with('transactions')
            ->orderBy('name')
            ->get();

        // build array with remaining budget
        $data = [
            'tags' => [],
            'total_budgets' => 0,
            'total_remaining_budgets' => 0,

        ];
        foreach ($tags as $tag) {

            $transactionsAmount = $tag->getTransactionsAmount([
                // 'start_date' => $startDate,
                // 'end_date' => $endDate,
            ]);

            if ($tag->budget > 0) {
                $remainingBudget = $tag->budget - abs($transactionsAmount);

                array_push($data['tags'], [
                    'name' => $tag->name,
                    'remaning_budget' => $remainingBudget,
                ]);

                $data['total_budgets']+= $tag->budget;
                $data['total_remaining_budgets']+= $remainingBudget;
            }
        }

        usort($data['tags'], function($a, $b) {
            return $a['remaning_budget'] - $b['remaning_budget'];
        });

        $this->data = $data;
    }
}
