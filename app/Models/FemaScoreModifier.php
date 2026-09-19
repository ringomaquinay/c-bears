<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FemaScoreModifier extends Model
{
    protected $fillable = [
        'fema_version_id',
        'fema_building_type_id',
        'seismicity_level',
        'assessment_level',
        'modifier_category',
        'modifier_code',
        'modifier_name',
        'modifier_value',
        'is_applicable',
        'applicability_notes',
        'is_active',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'modifier_value' => 'decimal:2',
            'is_applicable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function femaVersion(): BelongsTo
    {
        return $this->belongsTo(FemaVersion::class);
    }

    public function femaBuildingType(): BelongsTo
    {
        return $this->belongsTo(FemaBuildingType::class);
    }
}
