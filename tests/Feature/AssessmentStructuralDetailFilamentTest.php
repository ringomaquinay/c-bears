<?php

namespace Tests\Feature;

use App\Filament\Resources\Assessments\Pages\CreateAssessment;
use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AssessmentStructuralDetailFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_edit_form_loads_successfully_without_structural_detail(): void
    {
        [$assessment] = $this->createAssessmentContext();

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertFormSet([
                'building_id' => $assessment->building_id,
                'assessment_type' => 'Initial',
                'assessment_level' => 'Level 1',
                'fema_version_id' => $assessment->fema_version_id,
                'status' => 'Draft',
            ]);

        $this->assertNull($assessment->fresh()->structuralDetail);
    }

    public function test_existing_structural_detail_loads_correctly_on_assessment_edit_form(): void
    {
        [$assessment, $femaBuildingType] = $this->createAssessmentContext();

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
            'soil_type' => 'SOIL_AB',
            'vertical_irregularity_type' => 'moderate',
            'plan_irregularity_type' => 'irregular',
            'has_pre_code_condition' => null,
            'has_post_benchmark_condition' => false,
            'site_condition_notes' => 'Existing site notes.',
            'structural_observation_notes' => 'Existing structural notes.',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertFormSet([
                'structuralDetail.fema_building_type_id' => $femaBuildingType->id,
                'structuralDetail.seismicity_level' => 'High',
                'structuralDetail.soil_type' => 'SOIL_AB',
                'structuralDetail.vertical_irregularity_type' => 'moderate',
                'structuralDetail.plan_irregularity_type' => 'irregular',
                'structuralDetail.has_pre_code_condition' => null,
                'structuralDetail.has_post_benchmark_condition' => false,
                'structuralDetail.site_condition_notes' => 'Existing site notes.',
                'structuralDetail.structural_observation_notes' => 'Existing structural notes.',
            ]);
    }

    public function test_structural_detail_can_be_created_from_assessment_create_form(): void
    {
        [$building, $femaVersion, $femaBuildingType] = $this->createReferenceContext();

        Livewire::actingAs(User::factory()->create())
            ->test(CreateAssessment::class)
            ->fillForm([
                'building_id' => $building->id,
                'assessment_date' => '2026-09-28',
                'assessment_type' => 'Initial',
                'assessment_level' => 'Level 1',
                'fema_version_id' => $femaVersion->id,
                'status' => 'Draft',
                'structuralDetail' => [
                    'fema_building_type_id' => $femaBuildingType->id,
                    'seismicity_level' => 'Moderately High',
                    'soil_type' => 'SOIL_E_LOW_RISE',
                    'vertical_irregularity_type' => 'severe',
                    'plan_irregularity_type' => 'irregular',
                    'has_pre_code_condition' => true,
                    'has_post_benchmark_condition' => null,
                    'site_condition_notes' => 'Created site notes.',
                    'structural_observation_notes' => 'Created structural notes.',
                    'fema_building_type_code_snapshot' => 'USER-CONTROLLED',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $assessment = Assessment::query()->latest('id')->firstOrFail();
        $structuralDetail = $assessment->structuralDetail()->firstOrFail();

        $this->assertSame($femaVersion->id, $assessment->fema_version_id);
        $this->assertFalse(Schema::hasColumn('assessment_structural_details', 'fema_version_id'));
        $this->assertSame($femaBuildingType->id, $structuralDetail->fema_building_type_id);
        $this->assertSame('Moderately High', $structuralDetail->seismicity_level);
        $this->assertSame('SOIL_E_LOW_RISE', $structuralDetail->soil_type);
        $this->assertTrue($structuralDetail->has_pre_code_condition);
        $this->assertNull($structuralDetail->has_post_benchmark_condition);
        $this->assertSame('C1', $structuralDetail->fema_building_type_code_snapshot);
        $this->assertSame('P154-3E', $structuralDetail->fema_version_code_snapshot);
    }

    public function test_structural_detail_can_be_updated_from_assessment_edit_form(): void
    {
        [$assessment, $femaBuildingType, $alternateFemaBuildingType] = $this->createAssessmentContext(includeAlternateType: true);

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'Low',
            'soil_type' => 'SOIL_AB',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->fillForm([
                'structuralDetail' => [
                    'fema_building_type_id' => $alternateFemaBuildingType->id,
                    'seismicity_level' => 'Very High',
                    'soil_type' => 'SOIL_E_MID_HIGH_RISE',
                    'vertical_irregularity_type' => 'none',
                    'plan_irregularity_type' => 'none',
                    'has_pre_code_condition' => false,
                    'has_post_benchmark_condition' => true,
                    'site_condition_notes' => 'Updated site notes.',
                    'structural_observation_notes' => 'Updated structural notes.',
                    'fema_building_type_name_snapshot' => 'USER-CONTROLLED',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $structuralDetail = $assessment->fresh()->structuralDetail;

        $this->assertSame($alternateFemaBuildingType->id, $structuralDetail->fema_building_type_id);
        $this->assertSame('Very High', $structuralDetail->seismicity_level);
        $this->assertSame('SOIL_E_MID_HIGH_RISE', $structuralDetail->soil_type);
        $this->assertFalse($structuralDetail->has_pre_code_condition);
        $this->assertTrue($structuralDetail->has_post_benchmark_condition);
        $this->assertSame('W1', $structuralDetail->fema_building_type_code_snapshot);
        $this->assertSame('Light wood frame single- or multiple-family dwellings', $structuralDetail->fema_building_type_name_snapshot);
    }

    public function test_fema_version_change_refreshes_structural_detail_version_snapshot_on_next_save(): void
    {
        [$assessment, $femaBuildingType] = $this->createAssessmentContext();
        $newFemaVersion = FemaVersion::create([
            'code' => 'P154-LOCAL',
            'title' => 'Local Adopted Screening Reference',
            'edition' => 'Pilot Edition',
            'is_active' => true,
        ]);

        AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditAssessment::class, ['record' => $assessment->getKey()])
            ->fillForm([
                'fema_version_id' => $newFemaVersion->id,
                'structuralDetail' => [
                    'fema_building_type_id' => $femaBuildingType->id,
                    'seismicity_level' => 'High',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $structuralDetail = $assessment->fresh()->structuralDetail;

        $this->assertSame($newFemaVersion->id, $assessment->fresh()->fema_version_id);
        $this->assertSame('P154-LOCAL', $structuralDetail->fema_version_code_snapshot);
        $this->assertSame('Local Adopted Screening Reference', $structuralDetail->fema_version_title_snapshot);
        $this->assertSame('Pilot Edition', $structuralDetail->fema_version_edition_snapshot);
    }

    public function test_existing_assessment_creation_behavior_still_works_without_structural_detail(): void
    {
        [$building, $femaVersion] = $this->createReferenceContext();

        Livewire::actingAs(User::factory()->create())
            ->test(CreateAssessment::class)
            ->fillForm([
                'building_id' => $building->id,
                'assessment_date' => '2026-09-28',
                'assessment_type' => 'Initial',
                'assessment_level' => 'Level 1',
                'fema_version_id' => $femaVersion->id,
                'status' => 'Draft',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $assessment = Assessment::query()->latest('id')->firstOrFail();

        $this->assertSame($femaVersion->id, $assessment->fema_version_id);
        $this->assertNull($assessment->structuralDetail);
        $this->assertNotNull($assessment->buildingSnapshot);
    }

    /**
     * @return array{0: Assessment, 1: FemaBuildingType, 2?: FemaBuildingType}
     */
    private function createAssessmentContext(bool $includeAlternateType = false): array
    {
        $context = $this->createReferenceContext($includeAlternateType);
        $building = $context[0];
        $femaVersion = $context[1];
        $femaBuildingType = $context[2];
        $alternateFemaBuildingType = $context[3] ?? null;

        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-28',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'fema_version_id' => $femaVersion->id,
            'status' => 'Draft',
        ]);

        return $includeAlternateType
            ? [$assessment, $femaBuildingType, $alternateFemaBuildingType]
            : [$assessment, $femaBuildingType];
    }

    /**
     * @return array{0: Building, 1: FemaVersion, 2: FemaBuildingType, 3?: FemaBuildingType}
     */
    private function createReferenceContext(bool $includeAlternateType = false): array
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
            'building_name' => 'Filament Structural Detail Test Building',
            'barangay' => 'Barangay Filament',
            'record_status' => 'active',
        ]);

        if (! $includeAlternateType) {
            return [$building, $femaVersion, $femaBuildingType];
        }

        $alternateFemaBuildingType = FemaBuildingType::create([
            'fema_version_id' => $femaVersion->id,
            'code' => 'W1',
            'name' => 'Light wood frame single- or multiple-family dwellings',
            'material_category' => 'Wood',
            'structural_system' => 'Light wood frame',
            'is_active' => true,
        ]);

        return [$building, $femaVersion, $femaBuildingType, $alternateFemaBuildingType];
    }
}


