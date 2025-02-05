<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Empresas
        $company = Company::create(['code' => '1' ,'name' => 'GRS', 'user_id' => 1]);

        $user = User::create([
            'name' => 'Crhistian',
            'email' => 'crhistian.ppj@gmail.com',
            'password' => Hash::make('123456'),
            'role_id' => 1,
        ]);

        $user->companies()->attach([$company->id]);
    }
}
