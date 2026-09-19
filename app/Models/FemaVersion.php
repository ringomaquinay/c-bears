<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FemaVersion extends Model
{
    protected $fillable = [
        'code',
        'title',
        'edition',
        'publication_year',
        'effective_date',
        'is_active',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function femaBuildingTypes(): HasMany
    {
        return $this->hasMany(FemaBuildingType::class);
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

