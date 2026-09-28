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
        Schema::table('assessment_structural_details', function (Blueprint $table) {
            $table->foreignId('fema_basic_score_id')
                ->nullable()
                ->after('fema_building_type_id')
                ->constrained('fema_basic_scores')
                ->restrictOnDelete();

            $table->decimal('basic_score_snapshot', 5, 2)->nullable()->after('fema_basic_score_id');

            $table->foreignId('fema_minimum_score_id')
                ->nullable()
                ->after('basic_score_snapshot')
                ->constrained('fema_minimum_scores')
                ->restrictOnDelete();

            $table->decimal('minimum_score_snapshot', 5, 2)->nullable()->after('fema_minimum_score_id');
            $table->decimal('level_one_modifier_total_snapshot', 6, 2)->nullable()->after('minimum_score_snapshot');
            $table->decimal('calculated_level_one_score', 6, 2)->nullable()->after('level_one_modifier_total_snapshot');
            $table->decimal('final_level_one_score', 6, 2)->nullable()->after('calculated_level_one_score');
            $table->json('applied_level_one_modifiers_snapshot')->nullable()->after('final_level_one_score');
            $table->json('level_one_calculation_trace')->nullable()->after('applied_level_one_modifiers_snapshot');
            $table->timestamp('level_one_calculated_at')->nullable()->after('level_one_calculation_trace');

            $table->index('fema_basic_score_id');
            $table->index('fema_minimum_score_id');
            $table->index('level_one_calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_structural_details', function (Blueprint $table) {
            $table->dropIndex(['fema_basic_score_id']);
            $table->dropIndex(['fema_minimum_score_id']);
            $table->dropIndex(['level_one_calculated_at']);

            $table->dropConstrainedForeignId('fema_basic_score_id');
            $table->dropConstrainedForeignId('fema_minimum_score_id');

            $table->dropColumn([
                'basic_score_snapshot',
                'minimum_score_snapshot',
                'level_one_modifier_total_snapshot',
                'calculated_level_one_score',
                'final_level_one_score',
                'applied_level_one_modifiers_snapshot',
                'level_one_calculation_trace',
                'level_one_calculated_at',
            ]);
        });
    }
};
