<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentBuildingSnapshot;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentStructuralDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_structural_detail_can_be_created_with_relationships_and_snapshots(): void
    {
        [$assessment, $femaBuildingType] = $this->createAssessmentWithFemaReferences();

        $structuralDetail = AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
            'soil_type' => 'SOIL_AB',
            'vertical_irregularity_type' => 'moderate',
            'plan_irregularity_type' => 'irregular',
            'has_pre_code_condition' => true,
            'has_post_benchmark_condition' => false,
            'site_condition_notes' => 'Near sloping ground.',
            'structural_observation_notes' => 'Visible soft-story concern.',
        ]);

        $assessment->refresh();
        $structuralDetail->refresh();

        $this->assertTrue($assessment->structuralDetail->is($structuralDetail));
        $this->assertTrue($structuralDetail->assessment->is($assessment));
        $this->assertTrue($structuralDetail->femaBuildingType->is($femaBuildingType));
        $this->assertSame('P154-3E', $structuralDetail->fema_version_code_snapshot);
        $this->assertSame('Rapid Visual Screening of Buildings for Potential Seismic Hazards', $structuralDetail->fema_version_title_snapshot);
        $this->assertSame('Third Edition', $structuralDetail->fema_version_edition_snapshot);
        $this->assertSame('C1', $structuralDetail->fema_building_type_code_snapshot);
        $this->assertSame('Concrete moment-resisting frame buildings', $structuralDetail->fema_building_type_name_snapshot);
        $this->assertSame('Concrete', $structuralDetail->material_category_snapshot);
        $this->assertSame('Moment-resisting frame', $structuralDetail->structural_system_snapshot);
        $this->assertTrue($structuralDetail->has_pre_code_condition);
        $this->assertFalse($structuralDetail->has_post_benchmark_condition);
    }

    public function test_nullable_draft_fields_and_unknown_boolean_values_are_preserved(): void
    {
        [$assessment] = $this->createAssessmentWithFemaReferences();

        $structuralDetail = AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
        ]);

        $structuralDetail->refresh();

        $this->assertNull($structuralDetail->fema_building_type_id);
        $this->assertNull($structuralDetail->seismicity_level);
        $this->assertNull($structuralDetail->soil_type);
        $this->assertNull($structuralDetail->vertical_irregularity_type);
        $this->assertNull($structuralDetail->plan_irregularity_type);
        $this->assertNull($structuralDetail->has_pre_code_condition);
        $this->assertNull($structuralDetail->has_post_benchmark_condition);
    }

    public function test_duplicate_structural_detail_for_one_assessment_is_rejected(): void
    {
        [$assessment] = $this->createAssessmentWithFemaReferences();

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
        ]);

        $this->expectException(QueryException::class);

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
        ]);
    }

    public function test_deleting_assessment_cascades_to_structural_detail(): void
    {
        [$assessment] = $this->createAssessmentWithFemaReferences();

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
        ]);

        $assessment->delete();

        $this->assertSame(0, AssessmentStructuralDetail::count());
    }

    public function test_deleting_referenced_fema_building_type_is_restricted(): void
    {
        [$assessment, $femaBuildingType] = $this->createAssessmentWithFemaReferences();

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
        ]);

        $this->expectException(QueryException::class);

        $femaBuildingType->delete();
    }

    public function test_existing_assessment_building_snapshot_behavior_still_works(): void
    {
        [$assessment] = $this->createAssessmentWithFemaReferences();

        $this->assertSame(1, AssessmentBuildingSnapshot::where('assessment_id', $assessment->id)->count());

        $snapshot = $assessment->buildingSnapshot()->firstOrFail();

        $this->assertSame('Structural Detail Test Building', $snapshot->building_name_snapshot);
        $this->assertSame('Barangay Structural', $snapshot->barangay_snapshot);
        $this->assertMatchesRegularExpression('/^CBEARS-ASMT-2026-\d{6}$/', $assessment->assessment_number);
    }

    /**
     * @return array{0: Assessment, 1: FemaBuildingType}
     */
    private function createAssessmentWithFemaReferences(): array
    {
        $femaVersion = FemaVersion::create([
            'code' => 'P154-3E',
            'title' => 'Rapid Visual Screening of Buildings for Potential Seismic Hazards',
            'edition' => 'Third Edition',
            'publication_year' => 2015,
            'is_active' => true,
        ]);

        $femaBuildingType = FemaBuildingType::create([
            'fema_version_id' => $femaVersion->id,
            'code' => 'C1',
            'name' => 'Concrete moment-resisting frame buildings',
            'material_category' => 'Concrete',
            'structural_system' => 'Moment-resisting frame',
            'is_active' => true,
        ]);

        $building = Building::create([
            'building_name' => 'Structural Detail Test Building',
            'barangay' => 'Barangay Structural',
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

        return [$assessment, $femaBuildingType];
    }
}
