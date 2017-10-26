<?php

use Phinx\Seed\AbstractSeed;

class GroupsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Food',
                'user_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $oauth_clients = $this->table('groups');
        $oauth_clients->insert($data)
              ->save();
    }
}
