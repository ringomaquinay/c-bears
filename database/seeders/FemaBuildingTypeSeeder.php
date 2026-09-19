<?php

namespace Database\Seeders;

use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FemaBuildingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $femaVersion = FemaVersion::updateOrCreate(
            ['code' => 'P154-3E'],
            [
                'title' => 'Rapid Visual Screening of Buildings for Potential Seismic Hazards',
                'edition' => 'Third Edition',
                'publication_year' => 2015,
                'is_active' => true,
            ],
        );

        $buildingTypes = [
            [
                'code' => 'W1',
                'name' => 'Light wood frame single- or multiple-family dwellings',
                'material_category' => 'Wood',
                'structural_system' => 'Light wood frame',
            ],
            [
                'code' => 'W1A',
                'name' => 'Light wood frame multi-unit, multi-story residential buildings with floor area greater than 3,000 sq ft per floor',
                'material_category' => 'Wood',
                'structural_system' => 'Light wood frame',
            ],
            [
                'code' => 'W2',
                'name' => 'Wood frame commercial and industrial buildings',
                'material_category' => 'Wood',
                'structural_system' => 'Wood frame',
            ],
            [
                'code' => 'S1',
                'name' => 'Steel moment-resisting frame buildings',
                'material_category' => 'Steel',
                'structural_system' => 'Moment-resisting frame',
            ],
            [
                'code' => 'S2',
                'name' => 'Braced steel frame buildings',
                'material_category' => 'Steel',
                'structural_system' => 'Braced frame',
            ],
            [
                'code' => 'S3',
                'name' => 'Light metal buildings',
                'material_category' => 'Metal',
                'structural_system' => 'Light metal',
            ],
            [
                'code' => 'S4',
                'name' => 'Steel frame buildings with cast-in-place concrete shear walls',
                'material_category' => 'Steel',
                'structural_system' => 'Steel frame with cast-in-place concrete shear walls',
            ],
            [
                'code' => 'S5',
                'name' => 'Steel frame buildings with unreinforced masonry infill walls',
                'material_category' => 'Steel',
                'structural_system' => 'Steel frame with unreinforced masonry infill walls',
            ],
            [
                'code' => 'C1',
                'name' => 'Concrete moment-resisting frame buildings',
                'material_category' => 'Concrete',
                'structural_system' => 'Moment-resisting frame',
            ],
            [
                'code' => 'C2',
                'name' => 'Concrete shear wall buildings',
                'material_category' => 'Concrete',
                'structural_system' => 'Shear wall',
            ],
            [
                'code' => 'C3',
                'name' => 'Concrete frame buildings with unreinforced masonry infill walls',
                'material_category' => 'Concrete',
                'structural_system' => 'Concrete frame with unreinforced masonry infill walls',
            ],
            [
                'code' => 'PC1',
                'name' => 'Tilt-up buildings',
                'material_category' => null,
                'structural_system' => 'Tilt-up',
            ],
            [
                'code' => 'PC2',
                'name' => 'Precast concrete frame buildings',
                'material_category' => 'Precast concrete',
                'structural_system' => 'Precast concrete frame',
            ],
            [
                'code' => 'RM1',
                'name' => 'Reinforced masonry buildings with flexible floor and roof diaphragms',
                'material_category' => 'Reinforced masonry',
                'structural_system' => 'Flexible floor and roof diaphragms',
            ],
            [
                'code' => 'RM2',
                'name' => 'Reinforced masonry buildings with rigid floor and roof diaphragms',
                'material_category' => 'Reinforced masonry',
                'structural_system' => 'Rigid floor and roof diaphragms',
            ],
            [
                'code' => 'URM',
                'name' => 'Unreinforced masonry bearing wall buildings',
                'material_category' => 'Unreinforced masonry',
                'structural_system' => 'Bearing wall',
            ],
            [
                'code' => 'MH',
                'name' => 'Manufactured housing',
                'material_category' => 'Manufactured housing',
                'structural_system' => null,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($buildingTypes as $buildingType) {
            $record = FemaBuildingType::updateOrCreate(
                [
                    'fema_version_id' => $femaVersion->id,
                    'code' => $buildingType['code'],
                ],
                [
                    'name' => $buildingType['name'],
                    'description' => null,
                    'material_category' => $buildingType['material_category'],
                    'structural_system' => $buildingType['structural_system'],
                    'is_active' => true,
                    'remarks' => null,
                ],
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Seeded {$created} new and updated {$updated} existing FEMA P-154 Third Edition building type records.");
    }
}
