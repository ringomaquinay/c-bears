<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FemaBuildingType extends Model
{
    protected $fillable = [
        'fema_version_id',
        'code',
        'name',
        'description',
        'material_category',
        'structural_system',
        'is_active',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function femaVersion(): BelongsTo
    {
        return $this->belongsTo(FemaVersion::class);
    }

    public function femaBasicScores(): HasMany
    {
        return $this->hasMany(FemaBasicScore::class);
    }

    public function femaMinimumScores(): HasMany
    {
        return $this->hasMany(FemaMinimumScore::class);
    }

    public function femaScoreModifiers(): HasMany
    {
        return $this->hasMany(FemaScoreModifier::class);
    }
}

