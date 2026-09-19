<?php

namespace Database\Seeders;

use App\Models\FemaBuildingType;
use App\Models\FemaScoreModifier;
use App\Models\FemaVersion;
use Illuminate\Database\Seeder;
use RuntimeException;
use SplFileObject;

class FemaScoreModifierSeeder extends Seeder
{
    private const FEMA_VERSION_CODE = 'P154-3E';

    private const ASSESSMENT_LEVEL = 'Level 1';

    private const SOURCE_CSV = 'docs/data/FEMA_P154_Level1_Modifiers_Verified.csv';

    private const MODIFIER_NAMES = [
        'VERTICAL_SEVERE' => 'Severe Vertical Irregularity',
        'VERTICAL_MODERATE' => 'Moderate Vertical Irregularity',
        'PLAN_IRREGULARITY' => 'Plan Irregularity',
        'PRE_CODE' => 'Pre-Code',
        'POST_BENCHMARK' => 'Post-Benchmark',
        'SOIL_AB' => 'Soil Type A or B',
        'SOIL_E_LOW_RISE' => 'Soil Type E - 1 to 3 Stories',
        'SOIL_E_MID_HIGH_RISE' => 'Soil Type E - More Than 3 Stories',
    ];

    private const EXPECTED_HEADERS = [
        'fema_version',
        'seismicity_level',
        'building_type',
        'assessment_level',
        'modifier_code',
        'modifier_category',
        'modifier_value',
        'is_applicable',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $femaVersion = FemaVersion::where('code', self::FEMA_VERSION_CODE)->firstOrFail();

        $rows = $this->readCsvRows(base_path(self::SOURCE_CSV));

        if (count($rows) !== 680) {
            throw new RuntimeException('Expected 680 FEMA Level 1 score modifier rows, found ' . count($rows) . '.');
        }

        $modifierCodes = array_keys(self::MODIFIER_NAMES);
        $buildingTypeCodes = array_values(array_unique(array_column($rows, 'building_type')));

        $buildingTypes = FemaBuildingType::where('fema_version_id', $femaVersion->id)
            ->whereIn('code', $buildingTypeCodes)
            ->get()
            ->keyBy('code');

        $missingCodes = array_diff($buildingTypeCodes, $buildingTypes->keys()->all());

        if ($missingCodes !== []) {
            throw new RuntimeException('Missing FEMA building types for P154-3E: ' . implode(', ', $missingCodes));
        }

        $created = 0;
        $updated = 0;

        foreach ($rows as $rowNumber => $row) {
            $this->validateRow($row, $rowNumber + 2, $modifierCodes);

            $isApplicable = $this->parseBoolean($row['is_applicable'], $rowNumber + 2);
            $modifierValue = $isApplicable ? $row['modifier_value'] : null;

            $record = FemaScoreModifier::updateOrCreate(
                [
                    'fema_version_id' => $femaVersion->id,
                    'fema_building_type_id' => $buildingTypes[$row['building_type']]->id,
                    'seismicity_level' => $row['seismicity_level'],
                    'assessment_level' => self::ASSESSMENT_LEVEL,
                    'modifier_code' => $row['modifier_code'],
                ],
                [
                    'modifier_category' => $row['modifier_category'],
                    'modifier_name' => self::MODIFIER_NAMES[$row['modifier_code']],
                    'modifier_value' => $modifierValue,
                    'is_applicable' => $isApplicable,
                    'applicability_notes' => null,
                    'is_active' => true,
                    'remarks' => null,
                ],
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Seeded {$created} new and updated {$updated} existing FEMA P-154 Third Edition Level 1 score modifier records.");
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readCsvRows(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("FEMA score modifier CSV not found: {$path}");
        }

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = null;
        $rows = [];

        foreach ($file as $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($headers === null) {
                $headers = $row;

                if ($headers !== self::EXPECTED_HEADERS) {
                    throw new RuntimeException('Unexpected FEMA score modifier CSV headers.');
                }

                continue;
            }

            if (count($row) !== count($headers)) {
                throw new RuntimeException('Invalid FEMA score modifier CSV row shape.');
            }

            $rows[] = array_combine($headers, $row);
        }

        return $rows;
    }

    /**
     * @param array<string, string> $row
     * @param array<int, string> $modifierCodes
     */
    private function validateRow(array $row, int $csvLine, array $modifierCodes): void
    {
        if ($row['fema_version'] !== self::FEMA_VERSION_CODE) {
            throw new RuntimeException("Unexpected FEMA version at CSV line {$csvLine}.");
        }

        if ($row['assessment_level'] !== self::ASSESSMENT_LEVEL) {
            throw new RuntimeException("Unexpected assessment level at CSV line {$csvLine}.");
        }

        if (! in_array($row['modifier_code'], $modifierCodes, true)) {
            throw new RuntimeException("Unexpected modifier code at CSV line {$csvLine}: {$row['modifier_code']}");
        }

        $isApplicable = $this->parseBoolean($row['is_applicable'], $csvLine);

        if ($isApplicable && $row['modifier_value'] === '') {
            throw new RuntimeException("Applicable modifier missing value at CSV line {$csvLine}.");
        }
    }

    private function parseBoolean(string $value, int $csvLine): bool
    {
        return match ($value) {
            'true' => true,
            'false' => false,
            default => throw new RuntimeException("Invalid boolean value at CSV line {$csvLine}: {$value}"),
        };
    }
}
