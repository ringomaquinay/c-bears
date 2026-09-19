<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentBuildingSnapshot;
use App\Models\Building;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentBuildingSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_creation_captures_and_preserves_building_snapshot_history(): void
    {
        $building = Building::create([
            'building_name' => 'Old City Hall',
            'owner_or_responsible_office' => 'Engineering Office',
            'address' => 'Old Address',
            'barangay' => 'Barangay A',
            'latitude' => '14.1234567',
            'longitude' => '121.1234567',
            'primary_occupancy' => 'Government Office',
            'number_of_storeys' => 2,
            'year_built' => 1998,
            'approximate_floor_area' => '1234.50',
            'building_permit_number' => 'BP-OLD-001',
            'occupancy_permit_number' => 'OP-OLD-001',
            'property_reference_no' => 'PRN-OLD-001',
            'record_status' => 'active',
        ]);

        $assessmentA = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-12',
            'assessment_time' => '09:00:00',
            'assessment_type' => 'Rapid Visual Screening',
            'assessment_level' => 'Level 1',
            'status' => 'Draft',
        ]);

        $snapshotA = $assessmentA->buildingSnapshot()->first();

        $this->assertNotNull($snapshotA);
        $this->assertMatchesRegularExpression('/^CBEARS-ASMT-2026-\d{6}$/', $assessmentA->assessment_number);
        $this->assertSame(1, AssessmentBuildingSnapshot::where('assessment_id', $assessmentA->id)->count());
        $this->assertSame('Old City Hall', $snapshotA->building_name_snapshot);
        $this->assertSame($building->building_code, $snapshotA->building_code_snapshot);
        $this->assertSame('Engineering Office', $snapshotA->owner_or_responsible_office_snapshot);
        $this->assertSame('Old Address', $snapshotA->address_snapshot);
        $this->assertSame('Barangay A', $snapshotA->barangay_snapshot);
        $this->assertSame('14.1234567', $snapshotA->latitude_snapshot);
        $this->assertSame('121.1234567', $snapshotA->longitude_snapshot);
        $this->assertSame('Government Office', $snapshotA->primary_occupancy_snapshot);
        $this->assertSame(2, $snapshotA->number_of_storeys_snapshot);
        $this->assertSame(1998, $snapshotA->year_built_snapshot);
        $this->assertSame('1234.50', $snapshotA->approximate_floor_area_snapshot);
        $this->assertSame('BP-OLD-001', $snapshotA->building_permit_number_snapshot);
        $this->assertSame('OP-OLD-001', $snapshotA->occupancy_permit_number_snapshot);
        $this->assertSame('PRN-OLD-001', $snapshotA->property_reference_no_snapshot);

        $assessmentA->createBuildingSnapshot();
        $this->assertSame(1, AssessmentBuildingSnapshot::where('assessment_id', $assessmentA->id)->count());

        $building->update([
            'building_name' => 'Updated City Hall',
            'barangay' => 'Barangay B',
            'latitude' => '14.7654321',
            'approximate_floor_area' => '1500.75',
        ]);

        $building->refresh();
        $snapshotA->refresh();

        $this->assertSame('Updated City Hall', $building->building_name);
        $this->assertSame('Old City Hall', $snapshotA->building_name_snapshot);
        $this->assertSame('Barangay A', $snapshotA->barangay_snapshot);
        $this->assertSame('14.1234567', $snapshotA->latitude_snapshot);
        $this->assertSame('1234.50', $snapshotA->approximate_floor_area_snapshot);

        $assessmentB = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-13',
            'assessment_time' => '10:30:00',
            'assessment_type' => 'Rapid Visual Screening',
            'assessment_level' => 'Level 1',
            'status' => 'Draft',
        ]);

        $snapshotB = $assessmentB->buildingSnapshot()->first();

        $this->assertNotNull($snapshotB);
        $this->assertNotSame($snapshotA->id, $snapshotB->id);
        $this->assertSame(1, AssessmentBuildingSnapshot::where('assessment_id', $assessmentB->id)->count());
        $this->assertSame('Updated City Hall', $snapshotB->building_name_snapshot);
        $this->assertSame('Barangay B', $snapshotB->barangay_snapshot);
        $this->assertSame('14.7654321', $snapshotB->latitude_snapshot);
        $this->assertSame('1500.75', $snapshotB->approximate_floor_area_snapshot);

        $snapshotA->refresh();
        $this->assertSame('Old City Hall', $snapshotA->building_name_snapshot);
        $this->assertSame('Barangay A', $snapshotA->barangay_snapshot);
        $this->assertSame(2, Assessment::where('building_id', $building->id)->count());
        $this->assertSame(2, AssessmentBuildingSnapshot::count());
    }
}
