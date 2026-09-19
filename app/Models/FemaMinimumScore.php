<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FemaMinimumScore extends Model
{
    protected $fillable = [
        'fema_version_id',
        'fema_building_type_id',
        'seismicity_level',
        'minimum_score',
        'is_active',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'minimum_score' => 'decimal:2',
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
