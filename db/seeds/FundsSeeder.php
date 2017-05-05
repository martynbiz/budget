<?php

use Phinx\Seed\AbstractSeed;

class FundsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Nationwide',
                'amount' => '100',
                'currency_id' => 1,
                'user_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $oauth_clients = $this->table('funds');
        $oauth_clients->insert($data)
            ->save();
    }
}
