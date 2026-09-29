<?php

namespace Tests\Feature;

use App\Filament\Resources\Assessments\Pages\CreateAssessment;
use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Filament\Resources\Buildings\Pages\CreateBuilding;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBasicScore;
use App\Models\FemaBuildingType;
use App\Models\FemaMinimumScore;
use App\Models\FemaScoreModifier;
use App\Models\FemaVersion;
use App\Models\User;
use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AssessmentLevelOneScoreFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_level_one_workflow_runs_end_to_end_with_persisted_score_display(): void
    {
        $this->seed([
            FemaBuildingTypeSeeder::class,
            FemaBasicScoreSeeder::class,
            FemaMinimumScoreSeeder::class,
            FemaScoreModifierSeeder::class,
        ]);

        $user = User::factory()->create();
        Carbon::setTestNow('2026-09-29 10:30:00');
        $femaVersion = FemaVersion::where('code', 'P154-3E')->firstOrFail();
        $c1Type = FemaBuildingType::where('fema_version_id', $femaVersion->id)
            ->where('code', 'C1')
            ->firstOrFail();
        $w1Type = FemaBuildingType::where('fema_version_id', $femaVersion->id)
            ->where('code', 'W1')
            ->firstOrFail();

        Livewire::actingAs($user)
            ->test(CreateBuilding::class)
            ->fillForm([
                'building_name' => 'Draft Level 1 End-to-End Building',
                'owner_or_responsible_office' => 'OCITO QA',
                'barangay' => 'Barangay Verification',
                'primary_occupancy' => 'Office',
                'number_of_storeys' => 5,
                'year_built' => 1995,
                'record_status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $building = Building::where('building_name', 'Draft Level 1 End-to-End Building')->firstOrFail();

        Livewire::actingAs($user)
            ->test(CreateAssessment::class)
            ->fillForm([
                'building_id' => $building->id,
                'assessment_date' => '2026-09-29',
                'assessment_type' => 'Initial',
                'assessment_level' => 'Level 1',
                'fema_version_id' => $femaVersion->id,
                'status' => 'Draft',
                'remarks' => 'Draft Level 1 verification scenario.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $assessment = Assessment::where('building_id', $building->id)->latest('id')->firstOrFail();
        $snapshotBeforeScoring = $assessment->buildingSnapshot()->firstOrFail()->toArray();

        $this->assertSame('Draft', $assessment->status);
        $this->assertNull($assessment->structuralDetail);
        $this->assertSame(5, $assessment->buildingSnapshot->number_of_storeys_snapshot);

        Livewire::actingAs($user)
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();

        $this->assertNull($assessment->fresh()->structuralDetail);

        Livewire::actingAs($user)
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->fillForm([
                'structuralDetail' => [
                    'fema_building_type_id' => $c1Type->id,
                    'seismicity_level' => 'High',
                    'soil_type' => 'SOIL_E_MID_HIGH_RISE',
                    'vertical_irregularity_type' => 'moderate',
                    'plan_irregularity_type' => 'irregular',
                    'has_pre_code_condition' => null,
                    'has_post_benchmark_condition' => false,
                    'site_condition_notes' => 'Soil E selected; snapshot storeys should resolve mid/high rise modifier.',
                    'structural_observation_notes' => 'Moderate vertical and plan irregularities observed.',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $assessment->refresh();
        $structuralDetail = $assessment->structuralDetail()->firstOrFail();

        $this->assertNull($structuralDetail->has_pre_code_condition);
        $this->assertFalse($structuralDetail->has_post_benchmark_condition);

        Livewire::actingAs($user)
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful()
            ->assertSee('Final Level 1 Score');

        $this->assertPersistedScoreMatchesReferences($assessment->fresh(), [
            'VERTICAL_MODERATE',
            'PLAN_IRREGULARITY',
            'SOIL_E_MID_HIGH_RISE',
        ], '0.30');

        Livewire::actingAs($user)
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Level 1 score calculated')
            ->assertSee('VERTICAL_MODERATE')
            ->assertSee('PLAN_IRREGULARITY')
            ->assertSee('SOIL_E_MID_HIGH_RISE')
            ->assertSee('0.30');

        Livewire::actingAs($user)
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->fillForm([
                'structuralDetail' => [
                    'fema_building_type_id' => $c1Type->id,
                    'seismicity_level' => 'High',
                    'soil_type' => 'SOIL_E_MID_HIGH_RISE',
                    'vertical_irregularity_type' => 'moderate',
                    'plan_irregularity_type' => 'irregular',
                    'has_pre_code_condition' => null,
                    'has_post_benchmark_condition' => true,
                    'site_condition_notes' => 'Soil E selected; snapshot storeys should resolve mid/high rise modifier.',
                    'structural_observation_notes' => 'Post-benchmark condition added before score refresh.',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();

        $this->assertPersistedScoreMatchesReferences($assessment->fresh(), [
            'VERTICAL_MODERATE',
            'PLAN_IRREGULARITY',
            'POST_BENCHMARK',
            'SOIL_E_MID_HIGH_RISE',
        ], '1.80');

        Livewire::actingAs($user)
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->fillForm([
                'structuralDetail' => [
                    'fema_building_type_id' => $w1Type->id,
                    'seismicity_level' => 'High',
                    'soil_type' => 'SOIL_E_MID_HIGH_RISE',
                    'vertical_irregularity_type' => 'moderate',
                    'plan_irregularity_type' => 'irregular',
                    'has_pre_code_condition' => null,
                    'has_post_benchmark_condition' => true,
                    'site_condition_notes' => 'Building type changed to W1 for refresh verification.',
                    'structural_observation_notes' => 'Score should use W1 seeded references after refresh.',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->callAction('calculateLevelOneScore')
            ->assertSuccessful();

        $assessment->refresh();
        $this->assertPersistedScoreMatchesReferences($assessment, [
            'VERTICAL_MODERATE',
            'PLAN_IRREGULARITY',
            'POST_BENCHMARK',
            'SOIL_E_MID_HIGH_RISE',
        ], '3.10');

        $structuralDetail = $assessment->structuralDetail()->firstOrFail();

        $this->assertSame($w1Type->id, $structuralDetail->fema_building_type_id);
        $this->assertSame('W1', $structuralDetail->level_one_calculation_trace['inputs']['fema_building_type_code']);
        $this->assertSame(5, $structuralDetail->level_one_calculation_trace['inputs']['number_of_storeys_snapshot']);
        $this->assertSame('2026-09-29 10:30:00', $structuralDetail->level_one_calculated_at->format('Y-m-d H:i:s'));
        $this->assertSame($snapshotBeforeScoring, $assessment->buildingSnapshot()->firstOrFail()->toArray());

        Livewire::actingAs($user)
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('W1')
            ->assertSee('3.10')
            ->assertSee('Basic Score 3.60 + Level 1 Modifier Total -0.50 = Calculated Score 3.10');
    }

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

    /**
     * @param array<int, string> $expectedModifierCodes
     */
    private function assertPersistedScoreMatchesReferences(Assessment $assessment, array $expectedModifierCodes, string $expectedFinalScore): void
    {
        $assessment->loadMissing(['structuralDetail', 'buildingSnapshot']);
        $structuralDetail = $assessment->structuralDetail;

        $basicScore = FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();
        $minimumScore = FemaMinimumScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();
        $modifiers = FemaScoreModifier::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->where('assessment_level', 'Level 1')
            ->whereIn('modifier_code', $expectedModifierCodes)
            ->get()
            ->keyBy('modifier_code');

        $expectedModifierTotal = $modifiers
            ->sum(fn (FemaScoreModifier $modifier): float => (float) $modifier->modifier_value);
        $expectedCalculatedScore = round((float) $basicScore->basic_score + $expectedModifierTotal, 2);
        $expectedFinalScoreFromReferences = round(max($expectedCalculatedScore, (float) $minimumScore->minimum_score), 2);
        $modifierSnapshots = collect($structuralDetail->applied_level_one_modifiers_snapshot);

        $this->assertSame(number_format($expectedFinalScoreFromReferences, 2, '.', ''), $expectedFinalScore);
        $this->assertSame($basicScore->id, $structuralDetail->fema_basic_score_id);
        $this->assertSame(number_format((float) $basicScore->basic_score, 2, '.', ''), $structuralDetail->basic_score_snapshot);
        $this->assertSame($minimumScore->id, $structuralDetail->fema_minimum_score_id);
        $this->assertSame(number_format((float) $minimumScore->minimum_score, 2, '.', ''), $structuralDetail->minimum_score_snapshot);
        $this->assertSame(number_format($expectedModifierTotal, 2, '.', ''), $structuralDetail->level_one_modifier_total_snapshot);
        $this->assertSame(number_format($expectedCalculatedScore, 2, '.', ''), $structuralDetail->calculated_level_one_score);
        $this->assertSame($expectedFinalScore, $structuralDetail->final_level_one_score);
        $actualModifierCodes = $modifierSnapshots->pluck('code')->all();
        sort($expectedModifierCodes);
        sort($actualModifierCodes);
        $this->assertSame($expectedModifierCodes, $actualModifierCodes);
        $this->assertSame($assessment->buildingSnapshot->number_of_storeys_snapshot, $structuralDetail->level_one_calculation_trace['inputs']['number_of_storeys_snapshot']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }
}
