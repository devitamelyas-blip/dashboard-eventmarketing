<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            ['name' => 'Surabaya (PUSAT)', 'code' => 'SBY-HQ', 'is_headquarters' => true],
            ['name' => 'Jakarta', 'code' => 'JKT', 'is_headquarters' => false],
            ['name' => 'Bandung', 'code' => 'BDG', 'is_headquarters' => false],
            ['name' => 'Semarang', 'code' => 'SMG', 'is_headquarters' => false],
            ['name' => 'Yogyakarta', 'code' => 'YOG', 'is_headquarters' => false],
            ['name' => 'Denpasar', 'code' => 'DPS', 'is_headquarters' => false],
            ['name' => 'Medan', 'code' => 'MDN', 'is_headquarters' => false],
            ['name' => 'Makassar', 'code' => 'MKS', 'is_headquarters' => false],
            ['name' => 'Balikpapan', 'code' => 'BPN', 'is_headquarters' => false],
            ['name' => 'Palembang', 'code' => 'PLM', 'is_headquarters' => false],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                ['name' => $branch['name'], 'is_headquarters' => $branch['is_headquarters']]
            );
        }
    }
}
