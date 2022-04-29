<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Role::updateOrCreate([
            'slug' => 'can_approve',
            'name' => 'Approver'
        ]);

        Role::updateOrCreate([
            'slug' => 'can_create',
            'name' => 'Creator'
        ]);
    }
}