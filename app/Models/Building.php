<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = [
        'building_name',
        'owner_or_responsible_office',
        'address',
        'barangay',
        'latitude',
        'longitude',
        'primary_occupancy',
        'number_of_storeys',
        'year_built',
        'approximate_floor_area',
        'building_permit_number',
        'occupancy_permit_number',
        'property_reference_no',
        'remarks',
        'record_status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'number_of_storeys' => 'integer',
            'year_built' => 'integer',
            'approximate_floor_area' => 'decimal:2',
        ];
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Building $building): void {
            if (blank($building->building_code)) {
                $building->building_code = static::generateBuildingCode();
            }
        });

        static::updating(function (Building $building): void {
            if ($building->isDirty('building_code')) {
                $building->building_code = $building->getOriginal('building_code');
            }
        });
    }

    public static function previewNextBuildingCode(): string
    {
        $year = (int) now()->year;

        return static::formatBuildingCode($year, static::nextBuildingCodeSequence($year));
    }

    protected static function generateBuildingCode(): string
    {
        $year = (int) now()->year;
        $sequence = static::nextBuildingCodeSequence($year);

        do {
            $code = static::formatBuildingCode($year, $sequence);
            $sequence++;
        } while (static::query()->where('building_code', $code)->exists());

        return $code;
    }

    protected static function nextBuildingCodeSequence(int $year): int
    {
        $prefix = "CBEARS-{$year}-";

        $latestCode = static::query()
            ->where('building_code', 'like', "{$prefix}%")
            ->orderByDesc('building_code')
            ->value('building_code');

        if (! $latestCode) {
            return 1;
        }

        $latestSequence = (int) substr($latestCode, strlen($prefix));

        return $latestSequence + 1;
    }

    protected static function formatBuildingCode(int $year, int $sequence): string
    {
        return sprintf('CBEARS-%d-%06d', $year, $sequence);
    }
}
