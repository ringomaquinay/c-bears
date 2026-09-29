<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AssessmentStructuralDetail extends Model
{
    public const VERTICAL_IRREGULARITY_TYPES = [
        'none',
        'moderate',
        'severe',
    ];

    public const PLAN_IRREGULARITY_TYPES = [
        'none',
        'irregular',
    ];

    public const SOIL_TYPES = [
        'SOIL_AB',
        'SOIL_E_LOW_RISE',
        'SOIL_E_MID_HIGH_RISE',
    ];

    public const SEISMICITY_LEVELS = [
        'Low',
        'Moderate',
        'Moderately High',
        'High',
        'Very High',
    ];

    protected $fillable = [
        'assessment_id',
        'fema_building_type_id',
        'fema_version_code_snapshot',
        'fema_version_title_snapshot',
        'fema_version_edition_snapshot',
        'fema_building_type_code_snapshot',
        'fema_building_type_name_snapshot',
        'material_category_snapshot',
        'structural_system_snapshot',
        'seismicity_level',
        'soil_type',
        'vertical_irregularity_type',
        'plan_irregularity_type',
        'has_pre_code_condition',
        'has_post_benchmark_condition',
        'site_condition_notes',
        'structural_observation_notes',
    ];

    protected function casts(): array
    {
        return [
            'basic_score_snapshot' => 'decimal:2',
            'minimum_score_snapshot' => 'decimal:2',
            'level_one_modifier_total_snapshot' => 'decimal:2',
            'calculated_level_one_score' => 'decimal:2',
            'final_level_one_score' => 'decimal:2',
            'applied_level_one_modifiers_snapshot' => 'array',
            'level_one_calculation_trace' => 'array',
            'level_one_calculated_at' => 'datetime',
            'level_one_screening_cutoff_snapshot' => 'decimal:2',
            'level_one_recommendation_generated_at' => 'datetime',
            'has_pre_code_condition' => 'boolean',
            'has_post_benchmark_condition' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (AssessmentStructuralDetail $structuralDetail): void {
            $structuralDetail->preventCompletedAssessmentMutation();
            $structuralDetail->syncReferenceSnapshots();
        });
    }

    private function preventCompletedAssessmentMutation(): void
    {
        if (! $this->exists || ! $this->isDirty()) {
            return;
        }

        $assessment = $this->assessment()->first();

        if (! $assessment?->isCompleted()) {
            return;
        }

        throw ValidationException::withMessages([
            'structuralDetail' => 'Completed assessments are locked. Reopening requires a future administrative workflow.',
        ]);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function femaBuildingType(): BelongsTo
    {
        return $this->belongsTo(FemaBuildingType::class);
    }

    public function femaBasicScore(): BelongsTo
    {
        return $this->belongsTo(FemaBasicScore::class);
    }

    public function femaMinimumScore(): BelongsTo
    {
        return $this->belongsTo(FemaMinimumScore::class);
    }

    public function syncReferenceSnapshots(): void
    {
        $this->syncFemaVersionSnapshot();
        $this->syncFemaBuildingTypeSnapshot();
    }

    private function syncFemaVersionSnapshot(): void
    {
        $assessment = $this->assessment()->with('femaVersion')->first();
        $femaVersion = $assessment?->femaVersion;

        $this->fema_version_code_snapshot = $femaVersion?->code;
        $this->fema_version_title_snapshot = $femaVersion?->title;
        $this->fema_version_edition_snapshot = $femaVersion?->edition;
    }

    private function syncFemaBuildingTypeSnapshot(): void
    {
        $femaBuildingType = $this->femaBuildingType()->first();

        $this->fema_building_type_code_snapshot = $femaBuildingType?->code;
        $this->fema_building_type_name_snapshot = $femaBuildingType?->name;
        $this->material_category_snapshot = $femaBuildingType?->material_category;
        $this->structural_system_snapshot = $femaBuildingType?->structural_system;
    }
}

