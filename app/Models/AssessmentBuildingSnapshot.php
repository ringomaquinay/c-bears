<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
