<?php

namespace App\Models;

use Carbon\CarbonInterface;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assessment extends Model
{
    protected $fillable = [
        'building_id',
        'assessment_date',
        'assessment_time',
        'assessor_id',
        'assessment_type',
        'assessment_level',
        'fema_version_id',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'assessment_time' => 'datetime:H:i:s',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Assessment $assessment): void {
            $assessment->assessment_number = static::generateAssessmentNumber($assessment);
        });

        static::created(function (Assessment $assessment): void {
            $assessment->createBuildingSnapshot();
        });

        static::updating(function (Assessment $assessment): void {
            if ($assessment->isDirty('assessment_number')) {
                $assessment->assessment_number = $assessment->getOriginal('assessment_number');
            }
        });
    }

    protected static function generateAssessmentNumber(Assessment $assessment): string
    {
        $year = static::assessmentYear($assessment);
        $sequence = static::nextAssessmentNumberSequence($year);

        do {
            $assessmentNumber = static::formatAssessmentNumber($year, $sequence);
            $sequence++;
        } while (static::query()->where('assessment_number', $assessmentNumber)->exists());

        return $assessmentNumber;
    }

    protected static function assessmentYear(Assessment $assessment): int
    {
        if (blank($assessment->assessment_date)) {
            throw new InvalidArgumentException('Assessment date is required before generating an assessment number.');
        }

        $assessmentDate = $assessment->assessment_date;

        if (! $assessmentDate instanceof CarbonInterface) {
            $assessmentDate = $assessment->asDateTime($assessmentDate);
        }

        return (int) $assessmentDate->format('Y');
    }

    protected static function nextAssessmentNumberSequence(int $year): int
    {
        $prefix = "CBEARS-ASMT-{$year}-";

        $latestAssessmentNumber = static::query()
            ->where('assessment_number', 'like', "{$prefix}%")
            ->orderByDesc('assessment_number')
            ->value('assessment_number');

        if (! $latestAssessmentNumber) {
            return 1;
        }

        $latestSequence = (int) substr($latestAssessmentNumber, strlen($prefix));

        return $latestSequence + 1;
    }

    protected static function formatAssessmentNumber(int $year, int $sequence): string
    {
        return sprintf('CBEARS-ASMT-%d-%06d', $year, $sequence);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function buildingSnapshot(): HasOne
    {
        return $this->hasOne(AssessmentBuildingSnapshot::class);
    }

    public function createBuildingSnapshot(): AssessmentBuildingSnapshot
    {
        if ($this->buildingSnapshot()->exists()) {
            return $this->buildingSnapshot()->firstOrFail();
        }

        $building = $this->building()->firstOrFail();

        return $this->buildingSnapshot()->create([
            'building_code_snapshot' => $building->building_code,
            'building_name_snapshot' => $building->building_name,
            'owner_or_responsible_office_snapshot' => $building->owner_or_responsible_office,
            'address_snapshot' => $building->address,
            'barangay_snapshot' => $building->barangay,
            'latitude_snapshot' => $building->latitude,
            'longitude_snapshot' => $building->longitude,
            'primary_occupancy_snapshot' => $building->primary_occupancy,
            'number_of_storeys_snapshot' => $building->number_of_storeys,
            'year_built_snapshot' => $building->year_built,
            'approximate_floor_area_snapshot' => $building->approximate_floor_area,
            'building_permit_number_snapshot' => $building->building_permit_number,
            'occupancy_permit_number_snapshot' => $building->occupancy_permit_number,
            'property_reference_no_snapshot' => $building->property_reference_no,
        ]);
    }
}
