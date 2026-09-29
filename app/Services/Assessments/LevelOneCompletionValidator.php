<?php

namespace App\Services\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentBuildingSnapshot;
use App\Models\AssessmentStructuralDetail;
use Illuminate\Support\Carbon;

class LevelOneCompletionValidator
{
    /**
     * @return array<int, string>
     */
    public function completionErrors(Assessment $assessment): array
    {
        $assessment->load(['building', 'buildingSnapshot', 'structuralDetail']);

        $errors = [];
        $structuralDetail = $assessment->structuralDetail;
        $buildingSnapshot = $assessment->buildingSnapshot;

        if ($assessment->building_id === null || $assessment->building === null) {
            $errors[] = 'A linked Building is required.';
        }

        if ($assessment->assessment_date === null) {
            $errors[] = 'Assessment date is required.';
        }

        if ($assessment->fema_version_id === null) {
            $errors[] = 'FEMA Version is required.';
        }

        if ($assessment->assessment_level !== 'Level 1') {
            $errors[] = 'Assessment level must be Level 1.';
        }

        if (! $buildingSnapshot instanceof AssessmentBuildingSnapshot) {
            $errors[] = 'Assessment Building Snapshot is required.';
        }

        if (! $structuralDetail instanceof AssessmentStructuralDetail) {
            $errors[] = 'Assessment Structural Detail is required.';

            return $errors;
        }

        if ($structuralDetail->fema_building_type_id === null) {
            $errors[] = 'FEMA Building Type is required.';
        }

        if ($structuralDetail->seismicity_level === null) {
            $errors[] = 'Seismicity Level is required.';
        }

        if ($structuralDetail->soil_type === null) {
            $errors[] = 'Soil Type is required.';
        }

        if ($structuralDetail->vertical_irregularity_type === null) {
            $errors[] = 'Vertical Irregularity must be assessed.';
        }

        if ($structuralDetail->plan_irregularity_type === null) {
            $errors[] = 'Plan Irregularity must be assessed.';
        }

        if ($structuralDetail->has_pre_code_condition === null) {
            $errors[] = 'Pre-Code condition must be explicitly assessed as Yes or No.';
        }

        if ($structuralDetail->has_post_benchmark_condition === null) {
            $errors[] = 'Post-Benchmark condition must be explicitly assessed as Yes or No.';
        }

        if ($structuralDetail->level_one_calculated_at === null) {
            $errors[] = 'Calculate the Level 1 score before completion.';
        }

        if ($structuralDetail->final_level_one_score === null) {
            $errors[] = 'Final Level 1 score is required.';
        }

        if ($structuralDetail->fema_basic_score_id === null || $structuralDetail->basic_score_snapshot === null) {
            $errors[] = 'Persisted Basic Score snapshot is required.';
        }

        if ($structuralDetail->fema_minimum_score_id === null || $structuralDetail->minimum_score_snapshot === null) {
            $errors[] = 'Persisted Minimum Score snapshot is required.';
        }

        if (! is_array($structuralDetail->level_one_calculation_trace)) {
            $errors[] = 'Persisted Level 1 calculation trace is required.';
        }

        if ($this->isScoreStale($assessment)) {
            $errors[] = 'Refresh the Level 1 score before completion because scoring inputs changed after the last calculation.';
        }

        return $errors;
    }

    public function isReadyForCompletion(Assessment $assessment): bool
    {
        return $this->completionErrors($assessment) === [];
    }

    public function isScoreStale(Assessment $assessment): bool
    {
        $assessment->load(['buildingSnapshot', 'structuralDetail']);

        $structuralDetail = $assessment->structuralDetail;

        if (! $structuralDetail instanceof AssessmentStructuralDetail) {
            return false;
        }

        if ($structuralDetail->level_one_calculated_at === null) {
            return false;
        }

        if (! $this->traceInputsMatchCurrentInputs($assessment, $structuralDetail)) {
            return true;
        }

        $calculatedAt = $structuralDetail->level_one_calculated_at instanceof Carbon
            ? $structuralDetail->level_one_calculated_at
            : Carbon::parse($structuralDetail->level_one_calculated_at);

        foreach ([$structuralDetail->updated_at, $assessment->buildingSnapshot?->updated_at] as $updatedAt) {
            if ($updatedAt instanceof Carbon && $updatedAt->greaterThan($calculatedAt->copy()->addSecond())) {
                return true;
            }
        }

        return false;
    }

    private function traceInputsMatchCurrentInputs(Assessment $assessment, AssessmentStructuralDetail $structuralDetail): bool
    {
        $traceInputs = $structuralDetail->level_one_calculation_trace['inputs'] ?? null;

        if (! is_array($traceInputs)) {
            return false;
        }

        $currentInputs = [
            'fema_version_id' => $assessment->fema_version_id,
            'fema_building_type_id' => $structuralDetail->fema_building_type_id,
            'seismicity_level' => $structuralDetail->seismicity_level,
            'soil_type' => $structuralDetail->soil_type,
            'vertical_irregularity_type' => $structuralDetail->vertical_irregularity_type,
            'plan_irregularity_type' => $structuralDetail->plan_irregularity_type,
            'has_pre_code_condition' => $structuralDetail->has_pre_code_condition,
            'has_post_benchmark_condition' => $structuralDetail->has_post_benchmark_condition,
            'number_of_storeys_snapshot' => $assessment->buildingSnapshot?->number_of_storeys_snapshot,
        ];

        foreach ($currentInputs as $key => $value) {
            if (($traceInputs[$key] ?? null) !== $value) {
                return false;
            }
        }

        return true;
    }
}