<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBasicScore;
use App\Models\FemaBuildingType;
use App\Models\FemaMinimumScore;
use App\Models\FemaScoreModifier;
use App\Models\FemaVersion;
use App\Services\Fema\FemaLevelOneScoreLookup;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FemaLevelOneScoreLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_basic_and_minimum_scores(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();

        $result = $this->lookup()->lookup($assessment);

        $expectedBasicScore = FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();
        $expectedMinimumScore = FemaMinimumScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();

        $this->assertTrue($result['ready']);
        $this->assertTrue($expectedBasicScore->is($result['basic_score']['reference']));
        $this->assertSame((float) $expectedBasicScore->basic_score, $result['basic_score']['value']);
        $this->assertTrue($expectedMinimumScore->is($result['minimum_score']['reference']));
        $this->assertSame((float) $expectedMinimumScore->minimum_score, $result['minimum_score']['value']);
    }

    public function test_no_irregularity_modifiers_are_selected_for_none_or_false_inputs(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'none',
            'plan_irregularity_type' => 'none',
            'has_pre_code_condition' => false,
            'has_post_benchmark_condition' => false,
            'soil_type' => null,
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertTrue($result['ready']);
        $this->assertSame([], $result['modifier_codes']);
        $this->assertSame(0.0, $result['level_1_modifier_total']);
    }

    public function test_moderate_vertical_irregularity_modifier_is_resolved(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertModifierResolved($result, 'VERTICAL_MODERATE');
    }

    public function test_severe_vertical_irregularity_modifier_is_resolved(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'severe',
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertModifierResolved($result, 'VERTICAL_SEVERE');
    }

    public function test_plan_irregularity_modifier_is_resolved(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'plan_irregularity_type' => 'irregular',
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertModifierResolved($result, 'PLAN_IRREGULARITY');
    }

    public function test_pre_code_condition_modifier_is_resolved_only_when_true(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'has_pre_code_condition' => true,
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertModifierResolved($result, 'PRE_CODE');
    }

    public function test_post_benchmark_condition_modifier_is_resolved_only_when_true(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'has_post_benchmark_condition' => true,
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertModifierResolved($result, 'POST_BENCHMARK');
    }

    public function test_combined_level_one_modifiers_and_total_are_returned(): void
    {
        [$assessment] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
            'plan_irregularity_type' => 'irregular',
            'has_pre_code_condition' => true,
            'has_post_benchmark_condition' => true,
            'soil_type' => 'SOIL_AB',
        ]);

        $result = $this->lookup()->lookup($assessment);
        $expectedCodes = [
            'VERTICAL_MODERATE',
            'PLAN_IRREGULARITY',
            'PRE_CODE',
            'POST_BENCHMARK',
            'SOIL_AB',
        ];
        $expectedTotal = collect($result['level_1_modifiers'])->sum(fn (array $modifier): float => (float) ($modifier['value'] ?? 0));

        $this->assertTrue($result['ready']);
        $this->assertEqualsCanonicalizing($expectedCodes, $result['modifier_codes']);
        $this->assertSame(round($expectedTotal, 2), $result['level_1_modifier_total']);
    }

    public function test_soil_modifier_lookup_uses_historical_snapshot_storeys(): void
    {
        [$assessment, , $building] = $this->createScoringAssessment([
            'soil_type' => 'SOIL_E_MID_HIGH_RISE',
        ], storeys: 2);

        $building->update(['number_of_storeys' => 10]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertTrue($result['ready']);
        $this->assertContains('SOIL_E_LOW_RISE', $result['modifier_codes']);
        $this->assertNotContains('SOIL_E_MID_HIGH_RISE', $result['modifier_codes']);
        $this->assertSame(2, $assessment->buildingSnapshot()->firstOrFail()->number_of_storeys_snapshot);
    }

    public function test_missing_required_structural_inputs_return_incomplete_result(): void
    {
        $building = Building::create([
            'building_name' => 'Incomplete Lookup Building',
            'barangay' => 'Barangay Incomplete',
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-20',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'status' => 'Draft',
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertFalse($result['ready']);
        $this->assertContains('assessment.fema_version_id', $result['missing_inputs']);
        $this->assertContains('assessment.structuralDetail', $result['missing_inputs']);
        $this->assertSame([], $result['errors']);
    }

    public function test_missing_fema_reference_data_is_reported(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();

        FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->delete();

        $result = $this->lookup()->lookup($assessment);

        $this->assertFalse($result['ready']);
        $this->assertContains('missing_basic_score', $result['errors']);
    }

    public function test_duplicate_reference_rows_are_reported_as_ambiguous(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();
        $existingBasicScore = FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();

        Schema::table('fema_basic_scores', function (Blueprint $table): void {
            $table->dropUnique(['fema_version_id', 'fema_building_type_id', 'seismicity_level']);
        });

        FemaBasicScore::create([
            'fema_version_id' => $existingBasicScore->fema_version_id,
            'fema_building_type_id' => $existingBasicScore->fema_building_type_id,
            'seismicity_level' => $existingBasicScore->seismicity_level,
            'basic_score' => $existingBasicScore->basic_score,
            'is_active' => true,
        ]);

        $result = $this->lookup()->lookup($assessment);

        $this->assertFalse($result['ready']);
        $this->assertContains('ambiguous_basic_score', $result['errors']);
    }

    private function lookup(): FemaLevelOneScoreLookup
    {
        return app(FemaLevelOneScoreLookup::class);
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
            'building_name' => 'FEMA Lookup Test Building',
            'barangay' => 'Barangay Lookup',
            'number_of_storeys' => $storeys,
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-20',
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

    private function assertModifierResolved(array $result, string $modifierCode): void
    {
        $this->assertTrue($result['ready']);
        $this->assertContains($modifierCode, $result['modifier_codes']);

        $modifier = collect($result['level_1_modifiers'])
            ->firstWhere('code', $modifierCode);

        $this->assertNotNull($modifier);
        $this->assertInstanceOf(FemaScoreModifier::class, $modifier['reference']);
        $this->assertSame((float) $modifier['reference']->modifier_value, $modifier['value']);
    }
}
