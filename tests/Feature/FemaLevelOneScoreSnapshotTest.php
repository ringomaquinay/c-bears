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
use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FemaLevelOneScoreSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_one_calculation_is_persisted_to_structural_detail(): void
    {
        Carbon::setTestNow('2026-09-21 10:15:00');
        [$assessment, $structuralDetail] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
            'plan_irregularity_type' => 'irregular',
            'has_pre_code_condition' => true,
            'has_post_benchmark_condition' => true,
            'soil_type' => 'SOIL_AB',
        ]);

        $lookup = app(FemaLevelOneScoreLookup::class)->lookup($assessment);
        $result = app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $structuralDetail->refresh();

        $expectedCalculatedScore = round($lookup['basic_score']['value'] + $lookup['level_1_modifier_total'], 2);
        $expectedFinalScore = round(max($expectedCalculatedScore, $lookup['minimum_score']['value']), 2);

        $this->assertTrue($result['persisted']);
        $this->assertSame($lookup['basic_score']['reference']->id, $structuralDetail->fema_basic_score_id);
        $this->assertSame(number_format($lookup['basic_score']['value'], 2, '.', ''), $structuralDetail->basic_score_snapshot);
        $this->assertSame($lookup['minimum_score']['reference']->id, $structuralDetail->fema_minimum_score_id);
        $this->assertSame(number_format($lookup['minimum_score']['value'], 2, '.', ''), $structuralDetail->minimum_score_snapshot);
        $this->assertSame(number_format($lookup['level_1_modifier_total'], 2, '.', ''), $structuralDetail->level_one_modifier_total_snapshot);
        $this->assertSame(number_format($expectedCalculatedScore, 2, '.', ''), $structuralDetail->calculated_level_one_score);
        $this->assertSame(number_format($expectedFinalScore, 2, '.', ''), $structuralDetail->final_level_one_score);
        $this->assertSame('2026-09-21 10:15:00', $structuralDetail->level_one_calculated_at->format('Y-m-d H:i:s'));
        $this->assertEqualsCanonicalizing(
            ['VERTICAL_MODERATE', 'PLAN_IRREGULARITY', 'PRE_CODE', 'POST_BENCHMARK', 'SOIL_AB'],
            collect($structuralDetail->applied_level_one_modifiers_snapshot)->pluck('code')->all(),
        );
        $this->assertSame(
            'final_level_one_score = max(basic_score + level_one_modifier_total, minimum_score)',
            $structuralDetail->level_one_calculation_trace['formula'],
        );
        $this->assertSame($assessment->assessment_number, $structuralDetail->level_one_calculation_trace['assessment_number']);
    }

    public function test_persisted_snapshots_remain_stable_after_reference_values_change(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment([
            'vertical_irregularity_type' => 'moderate',
        ]);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $structuralDetail->refresh();

        $originalBasicScoreSnapshot = $structuralDetail->basic_score_snapshot;
        $originalModifierSnapshot = $structuralDetail->applied_level_one_modifiers_snapshot[0];
        $originalTrace = $structuralDetail->level_one_calculation_trace;

        FemaBasicScore::findOrFail($structuralDetail->fema_basic_score_id)->update(['basic_score' => '9.99']);
        FemaScoreModifier::findOrFail($originalModifierSnapshot['reference_id'])->update([
            'modifier_name' => 'Changed Modifier Name',
            'modifier_value' => '-9.99',
        ]);

        $structuralDetail->refresh();

        $this->assertSame($originalBasicScoreSnapshot, $structuralDetail->basic_score_snapshot);
        $this->assertSame($originalModifierSnapshot, $structuralDetail->applied_level_one_modifiers_snapshot[0]);
        $this->assertSame($originalTrace, $structuralDetail->level_one_calculation_trace);
    }

    public function test_minimum_score_floor_is_applied_to_final_level_one_score(): void
    {
        [$assessment, $structuralDetail] = $this->createScoringAssessment();

        FemaBasicScore::whereKey($this->basicScore($assessment, $structuralDetail)->id)->update(['basic_score' => '0.10']);
        FemaMinimumScore::whereKey($this->minimumScore($assessment, $structuralDetail)->id)->update(['minimum_score' => '1.50']);

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $structuralDetail->refresh();

        $this->assertSame('0.10', $structuralDetail->calculated_level_one_score);
        $this->assertSame('1.50', $structuralDetail->minimum_score_snapshot);
        $this->assertSame('1.50', $structuralDetail->final_level_one_score);
        $this->assertTrue($structuralDetail->level_one_calculation_trace['minimum_score_applied']);
    }

    public function test_incomplete_lookup_does_not_persist_level_one_score_snapshots(): void
    {
        $building = Building::create([
            'building_name' => 'Incomplete Snapshot Building',
            'barangay' => 'Barangay Snapshot',
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-21',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'status' => 'Draft',
        ]);

        $result = app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);

        $this->assertFalse($result['persisted']);
        $this->assertNull($assessment->structuralDetail);
    }

    private function basicScore(Assessment $assessment, AssessmentStructuralDetail $structuralDetail): FemaBasicScore
    {
        return FemaBasicScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();
    }

    private function minimumScore(Assessment $assessment, AssessmentStructuralDetail $structuralDetail): FemaMinimumScore
    {
        return FemaMinimumScore::where('fema_version_id', $assessment->fema_version_id)
            ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
            ->where('seismicity_level', $structuralDetail->seismicity_level)
            ->firstOrFail();
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
            'building_name' => 'FEMA Snapshot Test Building',
            'barangay' => 'Barangay Snapshot',
            'number_of_storeys' => $storeys,
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-21',
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
}
