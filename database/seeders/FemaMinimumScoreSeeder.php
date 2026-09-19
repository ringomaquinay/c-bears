<?php

namespace Database\Seeders;

use App\Models\FemaBuildingType;
use App\Models\FemaMinimumScore;
use App\Models\FemaVersion;
use Illuminate\Database\Seeder;

class FemaMinimumScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $femaVersion = FemaVersion::where('code', 'P154-3E')->firstOrFail();

        $scores = [
            'W1' => ['Low' => '2.7', 'Moderate' => '1.6', 'Moderately High' => '1.6', 'High' => '1.1', 'Very High' => '0.7'],
            'W1A' => ['Low' => '2.1', 'Moderate' => '1.2', 'Moderately High' => '1.2', 'High' => '0.9', 'Very High' => '0.7'],
            'W2' => ['Low' => '1.5', 'Moderate' => '0.9', 'Moderately High' => '0.8', 'High' => '0.7', 'Very High' => '0.7'],
            'S1' => ['Low' => '0.9', 'Moderate' => '0.6', 'Moderately High' => '0.5', 'High' => '0.5', 'Very High' => '0.5'],
            'S2' => ['Low' => '0.8', 'Moderate' => '0.6', 'Moderately High' => '0.5', 'High' => '0.5', 'Very High' => '0.5'],
            'S3' => ['Low' => '1.2', 'Moderate' => '0.8', 'Moderately High' => '0.9', 'High' => '0.6', 'Very High' => '0.5'],
            'S4' => ['Low' => '0.8', 'Moderate' => '0.6', 'Moderately High' => '0.5', 'High' => '0.5', 'Very High' => '0.5'],
            'S5' => ['Low' => '0.9', 'Moderate' => '0.6', 'Moderately High' => '0.5', 'High' => '0.5', 'Very High' => '0.5'],
            'C1' => ['Low' => '0.5', 'Moderate' => '0.3', 'Moderately High' => '0.3', 'High' => '0.3', 'Very High' => '0.3'],
            'C2' => ['Low' => '0.6', 'Moderate' => '0.3', 'Moderately High' => '0.3', 'High' => '0.3', 'Very High' => '0.3'],
            'C3' => ['Low' => '0.5', 'Moderate' => '0.3', 'Moderately High' => '0.3', 'High' => '0.3', 'Very High' => '0.3'],
            'PC1' => ['Low' => '0.6', 'Moderate' => '0.3', 'Moderately High' => '0.3', 'High' => '0.2', 'Very High' => '0.2'],
            'PC2' => ['Low' => '0.4', 'Moderate' => '0.2', 'Moderately High' => '0.2', 'High' => '0.2', 'Very High' => '0.2'],
            'RM1' => ['Low' => '0.6', 'Moderate' => '0.3', 'Moderately High' => '0.3', 'High' => '0.3', 'Very High' => '0.3'],
            'RM2' => ['Low' => '0.5', 'Moderate' => '0.3', 'Moderately High' => '0.3', 'High' => '0.3', 'Very High' => '0.3'],
            'URM' => ['Low' => '0.4', 'Moderate' => '0.2', 'Moderately High' => '0.2', 'High' => '0.2', 'Very High' => '0.2'],
            'MH' => ['Low' => '2.5', 'Moderate' => '1.5', 'Moderately High' => '1.4', 'High' => '1.0', 'Very High' => '1.0'],
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
            foreach ($seismicityScores as $seismicityLevel => $minimumScore) {
                $record = FemaMinimumScore::updateOrCreate(
                    [
                        'fema_version_id' => $femaVersion->id,
                        'fema_building_type_id' => $buildingTypes[$buildingTypeCode]->id,
                        'seismicity_level' => $seismicityLevel,
                    ],
                    [
                        'minimum_score' => $minimumScore,
                        'is_active' => true,
                        'remarks' => null,
                    ],
                );

                $record->wasRecentlyCreated ? $created++ : $updated++;
            }
        }

        $this->command?->info("Seeded {$created} new and updated {$updated} existing FEMA P-154 Third Edition minimum score records.");
    }
}
