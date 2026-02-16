<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@isata.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'organization_id' => null,
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
