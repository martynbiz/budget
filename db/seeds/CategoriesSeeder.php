<?php

use Phinx\Seed\AbstractSeed;

class CategoriesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Groceries',
                // 'category_group_id' => null,
                'user_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $oauth_clients = $this->table('categories');
        $oauth_clients->insert($data)
              ->save();
    }
}
