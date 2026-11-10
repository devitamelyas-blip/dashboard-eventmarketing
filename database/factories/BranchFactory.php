<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $city = fake()->unique()->city();
        return [
            'name' => $city,
            'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $city), 0, 3)) . '-' . Str::random(3),
            'is_headquarters' => false,
        ];
    }
}
