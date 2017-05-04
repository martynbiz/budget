<?php

use Phinx\Seed\AbstractSeed;

class CategoriesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Food',
                'parent_id' => 0,
                'user_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Groceries',
                'parent_id' => 1,
                'user_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $oauth_clients = $this->table('categories');
        $oauth_clients->insert($data)
              ->save();
    }
}
