<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'IT Department',
                'employee_id' => '121221',
                'mobile_number' => '9841423969',
                'email' => 'itd@nea.org.np',
                'email_verified_at' => null,
                'password' => bcrypt('nea@dmin!@#'),
                'last_login_at' => null,
                'last_login_ip' => null,
                'remember_token' => null,
                'created_at' => '2019-09-13 19:21:30',
                'updated_at' => '2019-09-13 19:21:30',
            ],
            [
                'name' => 'Rajan Dhungel',
                'employee_id' => '123456',
                'mobile_number' => '9851202309',
                'email' => 'rajdhungel73@gmail.com',
                'email_verified_at' => null,
                'password' => bcrypt('password@123'),
                'last_login_at' => null,
                'last_login_ip' => null,
                'remember_token' => null,
                'created_at' => '2019-09-13 19:21:30',
                'updated_at' => '2019-09-13 19:21:30',
            ],
            [
                'name' => 'Anup Gautam',
                'employee_id' => '123457',
                'mobile_number' => '9851202001',
                'email' => 'gautam.anup070@nea.org.np',
                'email_verified_at' => null,
                'password' => bcrypt('password@123'),
                'last_login_at' => null,
                'last_login_ip' => null,
                'remember_token' => null,
                'created_at' => '2019-09-13 19:21:30',
                'updated_at' => '2019-09-13 19:21:30',
            ],
        ];

        User::insert($users);
    }
}
