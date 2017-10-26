<?php

use Phinx\Seed\AbstractSeed;

class CurrenciesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'GBP',
                'format' => '&pound;%01.2f',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'JPY',
                'format' => '&yen;%01.0f',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'USB',
                'format' => '&dollar;%01.0f',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'EUR',
                'format' => '&euro;%01.0f',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $oauth_clients = $this->table('currencies');
        $oauth_clients->insert($data)
              ->save();
    }
}
