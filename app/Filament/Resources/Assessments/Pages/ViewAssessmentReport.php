<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use App\Models\Assessment;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

class ViewAssessmentReport extends ViewRecord
{
    protected static string $resource = AssessmentResource::class;

    protected string $view = 'filament.resources.assessments.pages.view-assessment-report';

    public function getTitle(): string
    {
        return 'Level 1 Final Assessment Report';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('View Assessment')
                ->url(fn (): string => $this->getResourceUrl('view')),
            Action::make('printReport')
                ->label('Print Report')
                ->url('#')
                ->extraAttributes([
                    'onclick' => 'window.print(); return false;',
                ]),
        ];
    }

    public function getReportRecord(): Assessment
    {
        /** @var Assessment $record */
        $record = $this->getRecord();

        return $record->loadMissing([
            'assessor',
            'buildingSnapshot',
            'structuralDetail',
        ]);
    }

    public function display(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('M d, Y h:i A');
        }

        if ($value === null || $value === '') {
            return 'Not recorded';
        }

        return (string) $value;
    }

    public function displayDate(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('M d, Y');
        }

        if ($value === null || $value === '') {
            return 'Not recorded';
        }

        return (string) $value;
    }

    public function score(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Not recorded';
        }

        return number_format((float) $value, 2, '.', '');
    }

    public function signedScore(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Not recorded';
        }

        $number = (float) $value;

        return ($number >= 0 ? '+' : '') . number_format($number, 2, '.', '');
    }

    public function booleanLabel(mixed $value): string
    {
        return match ($value) {
            true, 1, '1' => 'Yes',
            false, 0, '0' => 'No',
            default => 'Not recorded',
        };
    }

    public function optionLabel(?string $value, array $options): string
    {
        if ($value === null || $value === '') {
            return 'Not recorded';
        }

        return $options[$value] ?? $value;
    }

    public function appliedModifiers(Assessment $assessment): array
    {
        $modifiers = $assessment->structuralDetail?->applied_level_one_modifiers_snapshot;

        return is_array($modifiers) ? $modifiers : [];
    }

    public function traceRows(Assessment $assessment): array
    {
        $trace = $assessment->structuralDetail?->level_one_calculation_trace;

        if (! is_array($trace)) {
            return [];
        }

        $inputs = $trace['inputs'] ?? [];

        return [
            'Assessment Number' => $trace['assessment_number'] ?? null,
            'Calculated At' => $trace['calculated_at'] ?? null,
            'FEMA Version' => $inputs['fema_version_code'] ?? null,
            'FEMA Building Type' => $inputs['fema_building_type_code'] ?? null,
            'Seismicity Level' => $inputs['seismicity_level'] ?? null,
            'Soil Type' => $inputs['soil_type'] ?? null,
            'Vertical Irregularity' => $inputs['vertical_irregularity_type'] ?? null,
            'Plan Irregularity' => $inputs['plan_irregularity_type'] ?? null,
            'Pre-Code Condition' => array_key_exists('has_pre_code_condition', $inputs) ? $this->booleanLabel($inputs['has_pre_code_condition']) : null,
            'Post-Benchmark Condition' => array_key_exists('has_post_benchmark_condition', $inputs) ? $this->booleanLabel($inputs['has_post_benchmark_condition']) : null,
            'Snapshot Storeys' => $inputs['number_of_storeys_snapshot'] ?? null,
            'Formula' => $trace['formula'] ?? null,
            'Minimum Score Applied' => array_key_exists('minimum_score_applied', $trace) ? $this->booleanLabel($trace['minimum_score_applied']) : null,
        ];
    }
}