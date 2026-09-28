<?php

namespace App\Services\Fema;

use App\Models\Assessment;
use App\Models\FemaBasicScore;
use App\Models\FemaMinimumScore;
use App\Models\FemaScoreModifier;
use Illuminate\Support\Carbon;
use RuntimeException;

class FemaLevelOneScoreSnapshotter
{
    public function __construct(
        private readonly FemaLevelOneScoreLookup $lookupService,
    ) {
    }

    public function calculateAndPersist(Assessment $assessment): array
    {
        $lookup = $this->lookupService->lookup($assessment);

        if (! $lookup['ready']) {
            return [
                'persisted' => false,
                'lookup' => $lookup,
                'calculation' => null,
            ];
        }

        $structuralDetail = $assessment->structuralDetail()->first();

        if ($structuralDetail === null) {
            throw new RuntimeException('A ready Level 1 lookup requires an assessment structural detail record.');
        }

        $basicScore = $lookup['basic_score']['reference'];
        $minimumScore = $lookup['minimum_score']['reference'];

        if (! $basicScore instanceof FemaBasicScore || ! $minimumScore instanceof FemaMinimumScore) {
            throw new RuntimeException('A ready Level 1 lookup requires resolved basic and minimum score references.');
        }

        $basicScoreValue = (float) $lookup['basic_score']['value'];
        $minimumScoreValue = (float) $lookup['minimum_score']['value'];
        $modifierTotal = (float) $lookup['level_1_modifier_total'];
        $calculatedScore = round($basicScoreValue + $modifierTotal, 2);
        $finalScore = round(max($calculatedScore, $minimumScoreValue), 2);
        $calculatedAt = Carbon::now();
        $modifierSnapshots = $this->snapshotModifiers($lookup['level_1_modifiers']);
        $trace = $this->buildTrace(
            assessment: $assessment->fresh(['buildingSnapshot', 'femaVersion', 'structuralDetail.femaBuildingType']),
            basicScore: $basicScore,
            basicScoreValue: $basicScoreValue,
            minimumScore: $minimumScore,
            minimumScoreValue: $minimumScoreValue,
            modifierSnapshots: $modifierSnapshots,
            modifierTotal: $modifierTotal,
            calculatedScore: $calculatedScore,
            finalScore: $finalScore,
            calculatedAt: $calculatedAt,
        );

        $structuralDetail->forceFill([
            'fema_basic_score_id' => $basicScore->id,
            'basic_score_snapshot' => $basicScoreValue,
            'fema_minimum_score_id' => $minimumScore->id,
            'minimum_score_snapshot' => $minimumScoreValue,
            'level_one_modifier_total_snapshot' => $modifierTotal,
            'calculated_level_one_score' => $calculatedScore,
            'final_level_one_score' => $finalScore,
            'applied_level_one_modifiers_snapshot' => $modifierSnapshots,
            'level_one_calculation_trace' => $trace,
            'level_one_calculated_at' => $calculatedAt,
        ])->save();

        return [
            'persisted' => true,
            'lookup' => $lookup,
            'calculation' => [
                'basic_score' => $basicScoreValue,
                'modifier_total' => $modifierTotal,
                'calculated_level_one_score' => $calculatedScore,
                'minimum_score' => $minimumScoreValue,
                'final_level_one_score' => $finalScore,
                'minimum_score_applied' => $finalScore !== $calculatedScore,
                'calculated_at' => $calculatedAt,
            ],
        ];
    }

    private function snapshotModifiers(array $modifiers): array
    {
        return array_values(array_map(function (array $modifier): array {
            $reference = $modifier['reference'];

            if (! $reference instanceof FemaScoreModifier) {
                throw new RuntimeException('Modifier snapshots require resolved FEMA score modifier references.');
            }

            return [
                'reference_id' => $reference->id,
                'fema_version_id' => $reference->fema_version_id,
                'fema_building_type_id' => $reference->fema_building_type_id,
                'seismicity_level' => $reference->seismicity_level,
                'assessment_level' => $reference->assessment_level,
                'category' => $reference->modifier_category,
                'code' => $reference->modifier_code,
                'name' => $reference->modifier_name,
                'value' => $modifier['value'],
                'is_applicable' => $reference->is_applicable,
            ];
        }, $modifiers));
    }

    private function buildTrace(
        Assessment $assessment,
        FemaBasicScore $basicScore,
        float $basicScoreValue,
        FemaMinimumScore $minimumScore,
        float $minimumScoreValue,
        array $modifierSnapshots,
        float $modifierTotal,
        float $calculatedScore,
        float $finalScore,
        Carbon $calculatedAt,
    ): array {
        $structuralDetail = $assessment->structuralDetail;

        return [
            'assessment_id' => $assessment->id,
            'assessment_number' => $assessment->assessment_number,
            'calculated_at' => $calculatedAt->toISOString(),
            'inputs' => [
                'fema_version_id' => $assessment->fema_version_id,
                'fema_version_code' => $assessment->femaVersion?->code,
                'fema_building_type_id' => $structuralDetail?->fema_building_type_id,
                'fema_building_type_code' => $structuralDetail?->femaBuildingType?->code,
                'seismicity_level' => $structuralDetail?->seismicity_level,
                'soil_type' => $structuralDetail?->soil_type,
                'vertical_irregularity_type' => $structuralDetail?->vertical_irregularity_type,
                'plan_irregularity_type' => $structuralDetail?->plan_irregularity_type,
                'has_pre_code_condition' => $structuralDetail?->has_pre_code_condition,
                'has_post_benchmark_condition' => $structuralDetail?->has_post_benchmark_condition,
                'number_of_storeys_snapshot' => $assessment->buildingSnapshot?->number_of_storeys_snapshot,
            ],
            'basic_score' => [
                'reference_id' => $basicScore->id,
                'value' => $basicScoreValue,
            ],
            'modifiers' => $modifierSnapshots,
            'modifier_total' => $modifierTotal,
            'calculated_level_one_score' => $calculatedScore,
            'minimum_score' => [
                'reference_id' => $minimumScore->id,
                'value' => $minimumScoreValue,
            ],
            'minimum_score_applied' => $finalScore !== $calculatedScore,
            'final_level_one_score' => $finalScore,
            'formula' => 'final_level_one_score = max(basic_score + level_one_modifier_total, minimum_score)',
        ];
    }
}


