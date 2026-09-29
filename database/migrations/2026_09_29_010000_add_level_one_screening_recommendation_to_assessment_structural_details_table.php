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
        Schema::table('assessment_structural_details', function (Blueprint $table): void {
            $table->decimal('level_one_screening_cutoff_snapshot', 6, 2)->nullable()->after('level_one_calculated_at');
            $table->string('level_one_recommendation_code')->nullable()->after('level_one_screening_cutoff_snapshot');
            $table->string('level_one_recommendation_label')->nullable()->after('level_one_recommendation_code');
            $table->text('level_one_recommendation_explanation')->nullable()->after('level_one_recommendation_label');
            $table->timestamp('level_one_recommendation_generated_at')->nullable()->after('level_one_recommendation_explanation');

            $table->index('level_one_recommendation_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_structural_details', function (Blueprint $table): void {
            $table->dropIndex(['level_one_recommendation_code']);
            $table->dropColumn([
                'level_one_screening_cutoff_snapshot',
                'level_one_recommendation_code',
                'level_one_recommendation_label',
                'level_one_recommendation_explanation',
                'level_one_recommendation_generated_at',
            ]);
        });
    }
};