<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'super admin',
            'description' => 'Administrador full'
        ]);

        Role::create([
            'name' => 'admin grs',
            'description' => 'Administrador GRS'
        ]);

        Role::create([
            'name' => 'admin eagle',
            'description' => 'Administrador EAGLE'
        ]);

        Role::create([
            'name' => 'super grs',
            'description' => 'Supervisor GRS'
        ]);

        Role::create([
            'name' => 'super eagle',
            'description' => 'Supervisor EAGLE'
        ]);
    }
}
