<?php

use Phinx\Seed\AbstractSeed;

class UsersSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'first_name' => 'Martyn',
                'last_name' => 'Bissett',
                'username' => 'martyn',
                'email' => 'martynbissett@yahoo.co.uk',
                'password' => '$2y$12$9fOpMoRiqJH8qVqqqwmK..n2cQuxfTIsqdYtHTV3jlwKXBKWcxkla',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $oauth_clients = $this->table('users');
        $oauth_clients->insert($data)
            ->save();
    }
}
