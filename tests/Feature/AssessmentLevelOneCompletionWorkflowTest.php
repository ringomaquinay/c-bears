<?php

namespace Tests\Feature;

use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use App\Models\User;
use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AssessmentLevelOneCompletionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_without_structural_detail_cannot_be_completed(): void
    {
        [$assessment] = $this->createAssessmentWithoutStructuralDetail();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('completeAssessment')
            ->assertSuccessful();

        $assessment->refresh();

        $this->assertSame('Draft', $assessment->status);
        $this->assertNull($assessment->completed_at);
    }

    public function test_draft_without_calculated_level_one_score_cannot_be_completed(): void
    {
        [$assessment] = $this->createScoringAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('completeAssessment')
            ->assertSuccessful();

        $assessment->refresh();

        $this->assertSame('Draft', $assessment->status);
        $this->assertNull($assessment->completed_at);
    }

    public function test_stale_level_one_score_prevents_completion(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);

        $structuralDetail->update([
            'plan_irregularity_type' => 'irregular',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('completeAssessment')
            ->assertSuccessful();

        $assessment->refresh();

        $this->assertTrue($assessment->hasStaleLevelOneScore());
        $this->assertSame('Draft', $assessment->status);
        $this->assertNull($assessment->completed_at);
    }

    public function test_valid_draft_level_one_assessment_can_be_completed_and_locked(): void
    {
        Carbon::setTestNow('2026-09-29 14:00:00');

        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
            'plan_irregularity_type' => 'irregular',
            'soil_type' => 'SOIL_E_MID_HIGH_RISE',
            'has_pre_code_condition' => false,
            'has_post_benchmark_condition' => true,
        ]);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);

        $assessment->refresh();
        $structuralDetail = $assessment->structuralDetail()->firstOrFail();
        $buildingSnapshot = $assessment->buildingSnapshot()->firstOrFail();
        $scoreSnapshot = [
            'basic_score_snapshot' => $structuralDetail->basic_score_snapshot,
            'final_level_one_score' => $structuralDetail->final_level_one_score,
            'trace' => $structuralDetail->level_one_calculation_trace,
        ];
        $buildingSnapshotState = $buildingSnapshot->toArray();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('completeAssessment')
            ->assertSuccessful();

        $assessment->refresh();
        $structuralDetail->refresh();

        $this->assertSame('Completed', $assessment->status);
        $this->assertSame('2026-09-29 14:00:00', $assessment->completed_at->format('Y-m-d H:i:s'));
        $this->assertSame($scoreSnapshot['basic_score_snapshot'], $structuralDetail->basic_score_snapshot);
        $this->assertSame($scoreSnapshot['final_level_one_score'], $structuralDetail->final_level_one_score);
        $this->assertSame($scoreSnapshot['trace'], $structuralDetail->level_one_calculation_trace);
        $this->assertSame($buildingSnapshotState, $assessment->buildingSnapshot()->firstOrFail()->toArray());

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Completed')
            ->assertSee('Completed At')
            ->assertActionDisabled('calculateLevelOneScore')
            ->assertActionDisabled('completeAssessment')
            ->assertActionHidden('edit');
    }

    public function test_completed_assessment_score_inputs_are_locked(): void
    {
        [$assessment, $structuralDetail] = $this->createCompletedAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertFormFieldIsDisabled('building_id')
            ->assertFormFieldIsDisabled('fema_version_id')
            ->assertFormFieldIsDisabled('structuralDetail.fema_building_type_id')
            ->assertFormFieldIsDisabled('structuralDetail.seismicity_level')
            ->assertFormFieldIsDisabled('structuralDetail.soil_type')
            ->assertFormFieldIsDisabled('structuralDetail.has_pre_code_condition')
            ->assertActionDisabled('calculateLevelOneScore')
            ->assertActionDisabled('completeAssessment');

        $this->expectException(ValidationException::class);

        $structuralDetail->update([
            'seismicity_level' => 'Very High',
        ]);
    }

    public function test_completed_assessment_cannot_recalculate_and_retains_persisted_score(): void
    {
        [$assessment, $structuralDetail] = $this->createCompletedAssessment();

        $originalFinalScore = $structuralDetail->final_level_one_score;
        $result = app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment->fresh());

        $this->assertFalse($result['persisted']);
        $this->assertContains('assessment_completed', $result['lookup']['errors']);
        $this->assertSame($originalFinalScore, $structuralDetail->fresh()->final_level_one_score);
    }

    public function test_direct_status_editing_cannot_bypass_completion_validation(): void
    {
        [$assessment] = $this->createAssessmentWithoutStructuralDetail();

        try {
            $assessment->update(['status' => 'Completed']);
            $this->fail('Expected validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Assessment Structural Detail is required.', collect($exception->errors())->flatten()->implode(' '));
        }

        $assessment->refresh();

        $this->assertSame('Draft', $assessment->status);
        $this->assertNull($assessment->completed_at);
    }

    public function test_completed_assessment_building_snapshot_is_locked(): void
    {
        [$assessment] = $this->createCompletedAssessment();

        $this->expectException(ValidationException::class);

        $assessment->buildingSnapshot()->firstOrFail()->update([
            'number_of_storeys_snapshot' => 99,
        ]);
    }

    /**
     * @return array{0: Assessment, 1: AssessmentStructuralDetail, 2: Building}
     */
    private function createCompletedAssessment(): array
    {
        [$assessment, $structuralDetail, $building] = $this->createScoringAssessment();

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $assessment->completeLevelOne();

        return [$assessment->fresh(), $structuralDetail->fresh(), $building];
    }

    /**
     * @return array{0: Assessment, 1: Building}
     */
    private function createAssessmentWithoutStructuralDetail(): array
    {
        $this->seedReferenceData();

        $femaVersion = FemaVersion::where('code', 'P154-3E')->firstOrFail();
        $building = Building::create([
            'building_name' => 'Level 1 Completion Building',
            'barangay' => 'Barangay Completion',
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

        return [$assessment, $building];
    }

    /**
     * @param array<string, mixed> $structuralOverrides
     * @return array{0: Assessment, 1: AssessmentStructuralDetail, 2: Building}
     */
    private function createScoringAssessment(array $structuralOverrides = []): array
    {
        [$assessment, $building] = $this->createAssessmentWithoutStructuralDetail();
        $femaBuildingType = FemaBuildingType::where('fema_version_id', $assessment->fema_version_id)
            ->where('code', 'C1')
            ->firstOrFail();
        $structuralDetail = AssessmentStructuralDetail::create(array_merge([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
            'soil_type' => 'SOIL_E_MID_HIGH_RISE',
            'vertical_irregularity_type' => 'none',
            'plan_irregularity_type' => 'none',
            'has_pre_code_condition' => false,
            'has_post_benchmark_condition' => false,
            'site_condition_notes' => 'Completion workflow test site notes.',
            'structural_observation_notes' => 'Completion workflow test structural notes.',
        ], $structuralOverrides));

        return [$assessment, $structuralDetail, $building];
    }

    private function seedReferenceData(): void
    {
        $this->seed([
            FemaBuildingTypeSeeder::class,
            FemaBasicScoreSeeder::class,
            FemaMinimumScoreSeeder::class,
            FemaScoreModifierSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}