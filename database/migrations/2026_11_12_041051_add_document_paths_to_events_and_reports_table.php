<?php

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
        Schema::table('events', function (Blueprint $table) {
            $table->string('proposal_doc_path')->nullable()->after('team_budget');
        });

        Schema::table('event_reports', function (Blueprint $table) {
            $table->string('attendance_doc_path')->nullable()->after('actual_team_cost');
            $table->string('documentation_doc_path')->nullable()->after('attendance_doc_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('proposal_doc_path');
        });

        Schema::table('event_reports', function (Blueprint $table) {
            $table->dropColumn(['attendance_doc_path', 'documentation_doc_path']);
        });
    }
};
