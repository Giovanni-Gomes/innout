<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'funcionario@cod3r.com.br'],
            [
                'name' => 'Funcionário Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'start_date' => '2020-01-01',
                'end_date' => null,
                'is_admin' => false,
                'remember_token' => Str::random(10),
            ]
        );
    }
}
