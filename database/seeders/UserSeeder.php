<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surabaya = Branch::where('code', 'SBY-HQ')->first();
        $jakarta = Branch::where('code', 'JKT')->first();
        $bandung = Branch::where('code', 'BDG')->first();

        $defaultPassword = Hash::make('password');

        $users = [
            [
                'name' => 'Administrator Kencana',
                'email' => 'admin@kencana.id',
                'branch_id' => $surabaya?->id,
                'role' => 'admin',
            ],
            [
                'name' => 'Direksi Kencana',
                'email' => 'direksi@kencana.id',
                'branch_id' => $surabaya?->id,
                'role' => 'direksi',
            ],
            [
                'name' => 'Marketing Pusat',
                'email' => 'marketing@kencana.id',
                'branch_id' => $surabaya?->id,
                'role' => 'marketing',
            ],
            [
                'name' => 'PIC Surabaya',
                'email' => 'pic.surabaya@kencana.id',
                'branch_id' => $surabaya?->id,
                'role' => 'pic',
            ],
            [
                'name' => 'PIC Jakarta',
                'email' => 'pic.jakarta@kencana.id',
                'branch_id' => $jakarta?->id,
                'role' => 'pic',
            ],
            [
                'name' => 'PIC Bandung',
                'email' => 'pic.bandung@kencana.id',
                'branch_id' => $bandung?->id,
                'role' => 'pic',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'branch_id' => $data['branch_id'],
                    'password' => $defaultPassword,
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
