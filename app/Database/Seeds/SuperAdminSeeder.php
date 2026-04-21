<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $users = auth()->getProvider();

        // Check if user already exists
        $user = $users->findByCredentials(['email' => 'admin@verra.id']);

        if ($user === null) {
            $user = new User([
                'username' => 'superadmin',
                'email' => 'admin@verra.id',
                'password' => 'password123',
            ]);

            $users->save($user);

            // Get the user again to ensure we have the ID for group assignment
            $user = $users->findById($users->getInsertID());

            // Assign to superadmin group
            $user->addGroup('superadmin');

            echo "Super Admin user created successfully.\n";
        } else {
            echo "Super Admin user already exists. Skipping.\n";
        }
    }
}
