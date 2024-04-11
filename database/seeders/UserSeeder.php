<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::create([
            'first_name' => 'Pranay',
            'last_name' => 'Baddam',
            'email' => 'pranay.baddam@gmail.com',
            'password' => 'Baddam@£6',
            'type' => UserType::INTERNAL->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $superAdmin->assignRole('Super Admin');
    }
}
