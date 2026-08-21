<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['WorkHub Admin', 'admin@workhub.test', 'admin', 'active'],
            ['Arif Pratama', 'arif@workhub.test', 'user', 'active'],
            ['Nadia Putri', 'nadia@workhub.test', 'user', 'active'],
            ['Bima Saputra', 'bima@workhub.test', 'user', 'active'],
            ['Sari Wulandari', 'sari@workhub.test', 'user', 'active'],
            ['Dewa Mahendra', 'dewa@workhub.test', 'user', 'active'],
            ['Maya Lestari', 'maya@workhub.test', 'user', 'active'],
            ['Rizky Ananda', 'rizky@workhub.test', 'user', 'active'],
            ['Tania Kusuma', 'tania@workhub.test', 'user', 'active'],
            ['Fajar Nugroho', 'fajar@workhub.test', 'user', 'active'],
            ['Citra Dewi', 'citra@workhub.test', 'user', 'active'],
            ['Yoga Permana', 'yoga@workhub.test', 'user', 'active'],
            ['Lina Hartati', 'lina@workhub.test', 'user', 'active'],
            ['Suspended User', 'suspended@workhub.test', 'user', 'suspended'],
        ];
        foreach ($users as [$name, $email, $role, $status]) {
            User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => 'password', 'email_verified_at' => now(), 'system_role' => $role, 'status' => $status]);
        }
    }
}
