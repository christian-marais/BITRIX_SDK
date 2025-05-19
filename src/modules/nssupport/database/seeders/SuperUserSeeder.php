<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrateur',
            'email' => 'christian.marais@ns2b.fr',
            'password' => Hash::make('Wz33VDFuWucCcJr'),
            'role' => 'superadmin',
            'company_id' => '1',
            'secret_code' => '1',
        ]);
    }
}
