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


        $user = User::create([
            'name' => 'Juan EAGLE',
            'email' => 'juaneagle@gmail.com',
            'password' => Hash::make('123456'),
            'role_id' => 3,
        ]);

        // $user = User::create([
        //     'name' => 'soporte',
        //     'email' => 'soporte@gmail.com',
        //     'password' => Hash::make('123456'),
        //     'role_id' => 1,
        // ]);

        $company = Company::find(2)->get();
        $user->companies()->attach($company);
    }
}
