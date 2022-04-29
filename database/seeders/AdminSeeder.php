<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Admin::updateOrCreate([
            'name' => 'Richard Hendricks',
            'email' => 'create@example.com',
            'password' => Hash::make('password'),
            'role_id' => Role::whereSlug(Admin::ROLE_CAN_CREATE)->first()->id
        ]);

        Admin::updateOrCreate([
            'name' => 'Gavin belson',
            'email' => 'approve@example.com',
            'password' => Hash::make('password'),
            'role_id' => Role::whereSlug(Admin::ROLE_CAN_APPROVE)->first()->id
        ]);
    }
}
