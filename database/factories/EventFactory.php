<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'code' => 'EVT-' . strtoupper(Str::random(6)),
            'name' => 'Event Kencana ' . fake()->company(),
            'type' => fake()->randomElement(EventType::cases()),
            'event_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'branch_id' => Branch::factory(),
            'location' => fake()->city(),
            'pic_name' => fake()->name(),
            'pic_user_id' => User::factory(),
            'goal' => fake()->randomElement(Goal::cases()),
            'theme_product' => 'Baja Ringan Kencana Truss',
            'target_participants' => fake()->numberBetween(20, 150),
            'target_leads' => fake()->numberBetween(5, 50),
            'estimated_relevance_pct' => fake()->randomFloat(2, 60, 95),
            'estimated_relevance_basis' => 'Undangan khusus kontraktor dan aplikator baja ringan terdaftar.',
            'proposal_cost' => fake()->randomFloat(2, 10000000, 50000000),
            'team_budget' => fake()->randomFloat(2, 2000000, 10000000),
            'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
            'director_decision' => null,
            'director_note' => null,
            'decision_stale_flag' => false,
            'survey_token' => Str::random(40),
            'submitter_email' => fake()->safeEmail(),
        ];
    }
}
