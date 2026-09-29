<?php

namespace Tests\Feature;

use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use App\Models\User;
use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use App\Services\Fema\FemaLevelOneScreeningRecommendation;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FemaLevelOneScreeningRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_score_below_default_cutoff_recommends_detailed_evaluation(): void
    {
        $recommendation = $this->recommendationForFinalScore('1.80');

        $this->assertTrue($recommendation['available']);
        $this->assertSame('1.80', $recommendation['final_level_one_score']);
        $this->assertSame('2.00', $recommendation['screening_cutoff']);
        $this->assertSame('detailed_evaluation_recommended', $recommendation['recommendation_code']);
        $this->assertSame('Further Detailed Seismic Evaluation Recommended', $recommendation['recommendation_label']);
    }

    public function test_final_score_exactly_at_default_cutoff_does_not_trigger_recommendation(): void
    {
        $recommendation = $this->recommendationForFinalScore('2.00');

        $this->assertSame('screening_threshold_not_triggered', $recommendation['recommendation_code']);
        $this->assertSame('Detailed Evaluation Not Triggered by Screening Score', $recommendation['recommendation_label']);
    }

    public function test_final_score_above_default_cutoff_does_not_trigger_recommendation(): void
    {
        $recommendation = $this->recommendationForFinalScore('2.10');

        $this->assertSame('screening_threshold_not_triggered', $recommendation['recommendation_code']);
        $this->assertSame('Detailed Evaluation Not Triggered by Screening Score', $recommendation['recommendation_label']);
    }

    public function test_configurable_cutoff_is_respected(): void
    {
        config(['cbears.fema.level_one_screening_cutoff' => '1.00']);

        $recommendation = $this->recommendationForFinalScore('1.80');

        $this->assertSame('1.00', $recommendation['screening_cutoff']);
        $this->assertSame('screening_threshold_not_triggered', $recommendation['recommendation_code']);
    }

    public function test_recommendation_uses_persisted_final_score_only(): void
    {
        [$assessment, $structuralDetail] = $this->createRecommendationAssessment('1.80');

        $structuralDetail->forceFill([
            'calculated_level_one_score' => '9.99',
            'basic_score_snapshot' => '9.99',
        ])->save();

        $recommendation = app(FemaLevelOneScreeningRecommendation::class)->evaluate($assessment);

        $this->assertSame('1.80', $recommendation['final_level_one_score']);
        $this->assertSame('detailed_evaluation_recommended', $recommendation['recommendation_code']);
    }

    public function test_missing_final_score_returns_unavailable_result(): void
    {
        [$assessment] = $this->createRecommendationAssessment(null);

        $recommendation = app(FemaLevelOneScreeningRecommendation::class)->evaluate($assessment);

        $this->assertFalse($recommendation['available']);
        $this->assertSame('unavailable', $recommendation['recommendation_code']);
        $this->assertSame('Screening Recommendation Unavailable', $recommendation['recommendation_label']);
    }

    public function test_recommendation_is_snapshotted_when_assessment_is_completed(): void
    {
        Carbon::setTestNow('2026-09-29 15:00:00');

        [$assessment] = $this->createCompletedScoringAssessment();
        $structuralDetail = $assessment->fresh()->structuralDetail;

        $this->assertSame('Completed', $assessment->fresh()->status);
        $this->assertSame('2.00', $structuralDetail->level_one_screening_cutoff_snapshot);
        $this->assertSame('detailed_evaluation_recommended', $structuralDetail->level_one_recommendation_code);
        $this->assertSame('Further Detailed Seismic Evaluation Recommended', $structuralDetail->level_one_recommendation_label);
        $this->assertSame('2026-09-29 15:00:00', $structuralDetail->level_one_recommendation_generated_at->format('Y-m-d H:i:s'));
    }

    public function test_changing_configuration_later_does_not_change_historical_completed_recommendation_display(): void
    {
        [$assessment] = $this->createCompletedScoringAssessment();

        config(['cbears.fema.level_one_screening_cutoff' => '9.00']);

        $structuralDetail = $assessment->fresh()->structuralDetail;

        $this->assertSame('2.00', $structuralDetail->level_one_screening_cutoff_snapshot);
        $this->assertSame('detailed_evaluation_recommended', $structuralDetail->level_one_recommendation_code);
    }

    public function test_completed_assessment_displays_screening_recommendation_without_pass_fail_or_safe_unsafe_terms(): void
    {
        [$assessment] = $this->createCompletedScoringAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Screening Recommendation')
            ->assertSee('Final Level 1 Score')
            ->assertSee('Screening Cutoff')
            ->assertSee('2.00')
            ->assertSee('Further Detailed Seismic Evaluation Recommended')
            ->assertDontSee('Safe')
            ->assertDontSee('Unsafe')
            ->assertDontSee('Passed')
            ->assertDontSee('Failed');
    }

    /**
     * @return array{0: Assessment, 1: AssessmentStructuralDetail}
     */
    private function createRecommendationAssessment(?string $finalScore): array
    {
        $building = Building::create([
            'building_name' => 'Recommendation Service Test Building',
            'barangay' => 'Barangay Recommendation',
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-29',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'status' => 'Draft',
        ]);
        $structuralDetail = AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
        ]);

        $structuralDetail->forceFill([
            'final_level_one_score' => $finalScore,
        ])->save();

        return [$assessment, $structuralDetail];
    }

    /**
     * @return array{0: Assessment, 1: AssessmentStructuralDetail}
     */
    private function createCompletedScoringAssessment(): array
    {
        $this->seed([
            FemaBuildingTypeSeeder::class,
            FemaBasicScoreSeeder::class,
            FemaMinimumScoreSeeder::class,
            FemaScoreModifierSeeder::class,
        ]);

        $femaVersion = FemaVersion::where('code', 'P154-3E')->firstOrFail();
        $femaBuildingType = FemaBuildingType::where('fema_version_id', $femaVersion->id)
            ->where('code', 'C1')
            ->firstOrFail();
        $building = Building::create([
            'building_name' => 'Completed Recommendation Test Building',
            'barangay' => 'Barangay Recommendation',
            'number_of_storeys' => 5,
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-29',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'fema_version_id' => $femaVersion->id,
            'status' => 'Draft',
        ]);
        $structuralDetail = AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
            'soil_type' => 'SOIL_E_MID_HIGH_RISE',
            'vertical_irregularity_type' => 'none',
            'plan_irregularity_type' => 'none',
            'has_pre_code_condition' => false,
            'has_post_benchmark_condition' => false,
        ]);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $assessment->completeLevelOne();

        return [$assessment->fresh(), $structuralDetail->fresh()];
    }

    private function recommendationForFinalScore(string $finalScore): array
    {
        [$assessment] = $this->createRecommendationAssessment($finalScore);

        return app(FemaLevelOneScreeningRecommendation::class)->evaluate($assessment);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}