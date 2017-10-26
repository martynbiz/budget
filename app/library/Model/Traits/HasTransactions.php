<?php
namespace App\Model\Traits;

trait HasTransactions
{
    /**
     * @var
     */
    protected $transactionsAmount;

    public function getTransactionsAmount($query=[])
    {
        $baseQuery = $this->transactions();

        if (isset($query['start_date'])) {
            $baseQuery->where('purchased_at', '>=', $query['start_date']);
        }

        if (isset($query['end_date'])) {
            $baseQuery->where('purchased_at', '<=', $query['end_date']);
        }

        if (isset($query['fund'])) {
            $baseQuery->where('fund_id', $query['fund']->id);
        }

        if (is_null($this->transactionsAmount)) {
            $this->transactionsAmount = $baseQuery
                ->pluck('amount')
                ->sum();
        }

        return $this->transactionsAmount; //, 'user_id');
    }
}
