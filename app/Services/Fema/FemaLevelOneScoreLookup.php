<?php

namespace App\Services\Fema;

use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\FemaBasicScore;
use App\Models\FemaMinimumScore;
use App\Models\FemaScoreModifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FemaLevelOneScoreLookup
{
    private const ASSESSMENT_LEVEL = 'Level 1';

    public function lookup(Assessment $assessment): array
    {
        $assessment->loadMissing(['buildingSnapshot', 'structuralDetail']);

        $structuralDetail = $assessment->structuralDetail;
        $missingInputs = $this->missingRequiredInputs($assessment, $structuralDetail);
        $errors = [];

        $result = [
            'ready' => false,
            'missing_inputs' => $missingInputs,
            'errors' => [],
            'basic_score' => [
                'reference' => null,
                'value' => null,
            ],
            'minimum_score' => [
                'reference' => null,
                'value' => null,
            ],
            'level_1_modifiers' => [],
            'modifier_codes' => [],
            'level_1_modifier_total' => 0.0,
        ];

        if ($missingInputs !== []) {
            return $result;
        }

        $basicScore = $this->resolveSingleReference(
            FemaBasicScore::query()
                ->where('fema_version_id', $assessment->fema_version_id)
                ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
                ->where('seismicity_level', $structuralDetail->seismicity_level)
                ->where('is_active', true),
            'basic_score',
            $errors,
        );

        $minimumScore = $this->resolveSingleReference(
            FemaMinimumScore::query()
                ->where('fema_version_id', $assessment->fema_version_id)
                ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
                ->where('seismicity_level', $structuralDetail->seismicity_level)
                ->where('is_active', true),
            'minimum_score',
            $errors,
        );

        $modifierCodes = $this->resolveModifierCodes($assessment, $structuralDetail, $missingInputs, $errors);
        $modifiers = $this->resolveModifiers($assessment, $structuralDetail, $modifierCodes, $errors);
        $modifierTotal = array_reduce(
            $modifiers,
            fn (float $total, array $modifier): float => $total + ($modifier['is_applicable'] ? (float) ($modifier['value'] ?? 0) : 0.0),
            0.0,
        );

        return [
            'ready' => $missingInputs === [] && $errors === [],
            'missing_inputs' => $missingInputs,
            'errors' => $errors,
            'basic_score' => [
                'reference' => $basicScore,
                'value' => $basicScore instanceof FemaBasicScore ? (float) $basicScore->basic_score : null,
            ],
            'minimum_score' => [
                'reference' => $minimumScore,
                'value' => $minimumScore instanceof FemaMinimumScore ? (float) $minimumScore->minimum_score : null,
            ],
            'level_1_modifiers' => $modifiers,
            'modifier_codes' => array_values(array_unique($modifierCodes)),
            'level_1_modifier_total' => round($modifierTotal, 2),
        ];
    }

    private function missingRequiredInputs(Assessment $assessment, ?AssessmentStructuralDetail $structuralDetail): array
    {
        $missing = [];

        if ($assessment->fema_version_id === null) {
            $missing[] = 'assessment.fema_version_id';
        }

        if ($structuralDetail === null) {
            $missing[] = 'assessment.structuralDetail';

            return $missing;
        }

        if ($structuralDetail->fema_building_type_id === null) {
            $missing[] = 'structuralDetail.fema_building_type_id';
        }

        if ($structuralDetail->seismicity_level === null) {
            $missing[] = 'structuralDetail.seismicity_level';
        }

        return $missing;
    }

    /**
     * @param Builder<Model> $query
     */
    private function resolveSingleReference(Builder $query, string $label, array &$errors): ?Model
    {
        $matches = $query->limit(2)->get();

        if ($matches->count() === 0) {
            $errors[] = "missing_{$label}";

            return null;
        }

        if ($matches->count() > 1) {
            $errors[] = "ambiguous_{$label}";

            return null;
        }

        return $matches->first();
    }

    private function resolveModifierCodes(
        Assessment $assessment,
        AssessmentStructuralDetail $structuralDetail,
        array &$missingInputs,
        array &$errors,
    ): array {
        $codes = [];

        if ($structuralDetail->vertical_irregularity_type === 'severe') {
            $codes[] = 'VERTICAL_SEVERE';
        }

        if ($structuralDetail->vertical_irregularity_type === 'moderate') {
            $codes[] = 'VERTICAL_MODERATE';
        }

        if ($structuralDetail->plan_irregularity_type === 'irregular') {
            $codes[] = 'PLAN_IRREGULARITY';
        }

        if ($structuralDetail->has_pre_code_condition === true) {
            $codes[] = 'PRE_CODE';
        }

        if ($structuralDetail->has_post_benchmark_condition === true) {
            $codes[] = 'POST_BENCHMARK';
        }

        $soilModifierCode = $this->resolveSoilModifierCode($assessment, $structuralDetail, $missingInputs, $errors);

        if ($soilModifierCode !== null) {
            $codes[] = $soilModifierCode;
        }

        return array_values(array_unique($codes));
    }

    private function resolveSoilModifierCode(
        Assessment $assessment,
        AssessmentStructuralDetail $structuralDetail,
        array &$missingInputs,
        array &$errors,
    ): ?string {
        if ($structuralDetail->soil_type === null) {
            return null;
        }

        if ($structuralDetail->soil_type === 'SOIL_AB') {
            return 'SOIL_AB';
        }

        if (! in_array($structuralDetail->soil_type, ['SOIL_E_LOW_RISE', 'SOIL_E_MID_HIGH_RISE'], true)) {
            $errors[] = 'unsupported_soil_type';

            return null;
        }

        $storeys = $assessment->buildingSnapshot?->number_of_storeys_snapshot;

        if ($storeys === null) {
            $missingInputs[] = 'assessment_building_snapshot.number_of_storeys_snapshot';

            return null;
        }

        return (int) $storeys <= 3 ? 'SOIL_E_LOW_RISE' : 'SOIL_E_MID_HIGH_RISE';
    }

    private function resolveModifiers(
        Assessment $assessment,
        AssessmentStructuralDetail $structuralDetail,
        array $modifierCodes,
        array &$errors,
    ): array {
        $modifiers = [];

        foreach ($modifierCodes as $modifierCode) {
            $modifier = $this->resolveSingleReference(
                FemaScoreModifier::query()
                    ->where('fema_version_id', $assessment->fema_version_id)
                    ->where('fema_building_type_id', $structuralDetail->fema_building_type_id)
                    ->where('seismicity_level', $structuralDetail->seismicity_level)
                    ->where('assessment_level', self::ASSESSMENT_LEVEL)
                    ->where('modifier_code', $modifierCode)
                    ->where('is_active', true),
                "modifier_{$modifierCode}",
                $errors,
            );

            if (! $modifier instanceof FemaScoreModifier) {
                continue;
            }

            if (! $modifier->is_applicable) {
                $errors[] = "modifier_not_applicable_{$modifierCode}";
            }

            $modifiers[] = [
                'code' => $modifierCode,
                'reference' => $modifier,
                'value' => $modifier->modifier_value === null ? null : (float) $modifier->modifier_value,
                'is_applicable' => $modifier->is_applicable,
            ];
        }

        return $modifiers;
    }
}
