<?php

namespace Database\Seeders;

use App\Models\FemaBasicScore;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FemaBasicScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $femaVersion = FemaVersion::where('code', 'P154-3E')->firstOrFail();

        $scores = [
            'W1' => ['Low' => '6.2', 'Moderate' => '5.1', 'Moderately High' => '4.1', 'High' => '3.6', 'Very High' => '2.1'],
            'W1A' => ['Low' => '5.9', 'Moderate' => '4.5', 'Moderately High' => '3.7', 'High' => '3.2', 'Very High' => '1.8'],
            'W2' => ['Low' => '5.7', 'Moderate' => '3.8', 'Moderately High' => '3.2', 'High' => '2.9', 'Very High' => '1.9'],
            'S1' => ['Low' => '3.8', 'Moderate' => '2.7', 'Moderately High' => '2.3', 'High' => '2.1', 'Very High' => '1.5'],
            'S2' => ['Low' => '3.9', 'Moderate' => '2.6', 'Moderately High' => '2.2', 'High' => '2.0', 'Very High' => '1.4'],
            'S3' => ['Low' => '4.4', 'Moderate' => '3.5', 'Moderately High' => '2.9', 'High' => '2.6', 'Very High' => '1.6'],
            'S4' => ['Low' => '4.1', 'Moderate' => '2.5', 'Moderately High' => '2.2', 'High' => '2.0', 'Very High' => '1.4'],
            'S5' => ['Low' => '4.5', 'Moderate' => '2.7', 'Moderately High' => '2.0', 'High' => '1.7', 'Very High' => '1.2'],
            'C1' => ['Low' => '3.3', 'Moderate' => '2.1', 'Moderately High' => '1.7', 'High' => '1.5', 'Very High' => '1.0'],
            'C2' => ['Low' => '4.2', 'Moderate' => '2.5', 'Moderately High' => '2.1', 'High' => '2.0', 'Very High' => '1.2'],
            'C3' => ['Low' => '3.5', 'Moderate' => '2.0', 'Moderately High' => '1.4', 'High' => '1.2', 'Very High' => '0.9'],
            'PC1' => ['Low' => '3.8', 'Moderate' => '2.1', 'Moderately High' => '1.8', 'High' => '1.6', 'Very High' => '1.1'],
            'PC2' => ['Low' => '3.3', 'Moderate' => '1.9', 'Moderately High' => '1.5', 'High' => '1.4', 'Very High' => '1.0'],
            'RM1' => ['Low' => '3.7', 'Moderate' => '2.1', 'Moderately High' => '1.8', 'High' => '1.7', 'Very High' => '1.1'],
            'RM2' => ['Low' => '3.7', 'Moderate' => '2.1', 'Moderately High' => '1.8', 'High' => '1.7', 'Very High' => '1.1'],
            'URM' => ['Low' => '3.2', 'Moderate' => '1.7', 'Moderately High' => '1.2', 'High' => '1.0', 'Very High' => '0.9'],
            'MH' => ['Low' => '4.6', 'Moderate' => '2.9', 'Moderately High' => '2.2', 'High' => '1.5', 'Very High' => '1.1'],
        ];

        $buildingTypes = FemaBuildingType::where('fema_version_id', $femaVersion->id)
            ->whereIn('code', array_keys($scores))
            ->get()
            ->keyBy('code');

        $missingCodes = array_diff(array_keys($scores), $buildingTypes->keys()->all());

        if ($missingCodes !== []) {
            throw new \RuntimeException('Missing FEMA building types for P154-3E: ' . implode(', ', $missingCodes));
        }

        $created = 0;
        $updated = 0;

        foreach ($scores as $buildingTypeCode => $seismicityScores) {
            foreach ($seismicityScores as $seismicityLevel => $basicScore) {
                $record = FemaBasicScore::updateOrCreate(
                    [
                        'fema_version_id' => $femaVersion->id,
                        'fema_building_type_id' => $buildingTypes[$buildingTypeCode]->id,
                        'seismicity_level' => $seismicityLevel,
                    ],
                    [
                        'basic_score' => $basicScore,
                        'is_active' => true,
                        'remarks' => null,
                    ],
                );

                $record->wasRecentlyCreated ? $created++ : $updated++;
            }
        }

        $this->command?->info("Seeded {$created} new and updated {$updated} existing FEMA P-154 Third Edition basic score records.");
    }
}


