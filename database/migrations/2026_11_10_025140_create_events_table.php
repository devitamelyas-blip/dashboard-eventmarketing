<?php

use App\Enums\VerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name');
            $table->string('type');
            $table->date('event_date');
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate();
            $table->string('location');
            $table->string('pic_name');
            $table->foreignId('pic_user_id')->constrained('users')->cascadeOnUpdate();
            $table->string('goal');
            $table->string('theme_product');
            $table->unsignedInteger('target_participants')->default(0);
            $table->unsignedInteger('target_leads')->default(0);
            $table->decimal('estimated_relevance_pct', 5, 2)->default(0);
            $table->text('estimated_relevance_basis')->nullable();
            $table->decimal('proposal_cost', 15, 2);
            $table->decimal('team_budget', 15, 2);
            $table->string('verification_status')->default(VerificationStatus::BELUM_DIPERIKSA->value)->index();
            $table->string('director_decision')->nullable();
            $table->text('director_note')->nullable();
            $table->boolean('decision_stale_flag')->default(false);
            $table->string('survey_token', 64)->unique()->index();
            $table->string('submitter_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
