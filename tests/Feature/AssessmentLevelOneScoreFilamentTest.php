<?php

namespace Tests\Feature;

use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBasicScore;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use App\Models\User;
use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AssessmentLevelOneScoreFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_summary_renders_when_persisted_score_exists(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
            'has_post_benchmark_condition' => true,
        ]);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $assessment->refresh();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('FEMA Level 1 Score Summary')
            ->assertSee('Basic Score')
            ->assertSee('Applied Level 1 Modifiers')
            ->assertSee('VERTICAL_MODERATE')
            ->assertSee('Moderate Vertical Irregularity')
            ->assertSee('POST_BENCHMARK')
            ->assertSee('Final Level 1 Score');
    }

    public function test_empty_state_renders_when_no_score_exists(): void
    {
        [$assessment] = $this->createScoringAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('No Level 1 score has been calculated yet.');
    }

    public function test_calculate_action_calls_score_snapshotter(): void
    {
        [$assessment] = $this->createScoringAssessment();

        $this->mock(FemaLevelOneScoreSnapshotter::class, function ($mock): void {
            $mock->shouldReceive('calculateAndPersist')
                ->once()
                ->andReturn([
                    'persisted' => true,
                    'lookup' => [],
                    'calculation' => [],
                ]);
        });

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();
    }

    public function test_score_values_refresh_after_calculation_action(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful()
            ->assertSee('Final Level 1 Score');

        $this->assertNotNull($assessment->fresh()->structuralDetail->final_level_one_score);
    }

    public function test_applied_modifiers_are_displayed_from_persisted_snapshots(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'plan_irregularity_type' => 'irregular',
        ]);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);

        $modifierSnapshot = $assessment->fresh()->structuralDetail->applied_level_one_modifiers_snapshot[0];

        FemaBasicScore::query()->update(['basic_score' => '9.99']);

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee($modifierSnapshot['code'])
            ->assertSee($modifierSnapshot['name'])
            ->assertSee(number_format((float) $modifierSnapshot['value'], 2, '.', ''));
    }

    public function test_missing_required_inputs_show_no_persisted_score(): void
    {
        $building = Building::create([
            'building_name' => 'Missing Inputs Building',
            'barangay' => 'Barangay Missing',
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-28',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'status' => 'Draft',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();

        $this->assertNull($assessment->fresh()->structuralDetail);
    }

    public function test_lookup_errors_show_no_persisted_score(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();

        FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->delete();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();

        $this->assertNull($assessment->fresh()->structuralDetail->final_level_one_score);
    }

    public function test_recalculation_updates_persisted_score(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $originalFinalScore = $assessment->fresh()->structuralDetail->final_level_one_score;

        FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->update(['basic_score' => '8.88']);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();

        $updatedFinalScore = $assessment->fresh()->structuralDetail->final_level_one_score;

        $this->assertNotSame($originalFinalScore, $updatedFinalScore);
        $this->assertSame('8.88', $updatedFinalScore);
    }

    public function test_score_fields_remain_read_only_on_assessment_form_save(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
        ]);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $structuralDetail->refresh();

        $originalFinalScore = $structuralDetail->final_level_one_score;

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->fillForm([
                'structuralDetail' => [
                    'fema_building_type_id' => $structuralDetail->fema_building_type_id,
                    'seismicity_level' => $structuralDetail->seismicity_level,
                    'final_level_one_score' => '9.99',
                    'basic_score_snapshot' => '9.99',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $structuralDetail->refresh();

        $this->assertSame($originalFinalScore, $structuralDetail->final_level_one_score);
        $this->assertNotSame('9.99', $structuralDetail->basic_score_snapshot);
    }

    /**
     * @param array<string, mixed> $structuralOverrides
     * @return array{0: Assessment, 1: AssessmentStructuralDetail, 2: Building}
     */
    private function createScoringAssessment(array $structuralOverrides = [], int $storeys = 5): array
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
            'building_name' => 'Level 1 Filament Test Building',
            'barangay' => 'Barangay Score',
            'number_of_storeys' => $storeys,
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-28',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'fema_version_id' => $femaVersion->id,
            'status' => 'Draft',
        ]);
        $structuralDetail = AssessmentStructuralDetail::create(array_merge([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
            'soil_type' => null,
            'vertical_irregularity_type' => 'none',
            'plan_irregularity_type' => 'none',
            'has_pre_code_condition' => false,
            'has_post_benchmark_condition' => false,
        ], $structuralOverrides));

        return [$assessment, $structuralDetail, $building];
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
