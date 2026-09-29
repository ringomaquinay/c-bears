<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AssessmentBuildingSnapshot extends Model
{
    protected $fillable = [
        'assessment_id',
        'building_code_snapshot',
        'building_name_snapshot',
        'owner_or_responsible_office_snapshot',
        'address_snapshot',
        'barangay_snapshot',
        'latitude_snapshot',
        'longitude_snapshot',
        'primary_occupancy_snapshot',
        'number_of_storeys_snapshot',
        'year_built_snapshot',
        'approximate_floor_area_snapshot',
        'building_permit_number_snapshot',
        'occupancy_permit_number_snapshot',
        'property_reference_no_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'latitude_snapshot' => 'decimal:7',
            'longitude_snapshot' => 'decimal:7',
            'number_of_storeys_snapshot' => 'integer',
            'year_built_snapshot' => 'integer',
            'approximate_floor_area_snapshot' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (AssessmentBuildingSnapshot $snapshot): void {
            $snapshot->preventCompletedAssessmentMutation();
        });

        static::deleting(function (AssessmentBuildingSnapshot $snapshot): void {
            $snapshot->preventCompletedAssessmentMutation();
        });
    }

    private function preventCompletedAssessmentMutation(): void
    {
        $assessment = $this->assessment()->first();

        if (! $assessment?->isCompleted()) {
            return;
        }

        throw ValidationException::withMessages([
            'buildingSnapshot' => 'Completed assessment building snapshots are locked. Reopening requires a future administrative workflow.',
        ]);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
