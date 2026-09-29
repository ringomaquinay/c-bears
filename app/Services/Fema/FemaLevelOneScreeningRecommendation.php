<?php

namespace App\Services\Fema;

use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use Illuminate\Support\Carbon;

class FemaLevelOneScreeningRecommendation
{
    public const CODE_DETAILED_EVALUATION_RECOMMENDED = 'detailed_evaluation_recommended';

    public const CODE_SCREENING_THRESHOLD_NOT_TRIGGERED = 'screening_threshold_not_triggered';

    public const CODE_UNAVAILABLE = 'unavailable';

    public const LABEL_DETAILED_EVALUATION_RECOMMENDED = 'Further Detailed Seismic Evaluation Recommended';

    public const LABEL_SCREENING_THRESHOLD_NOT_TRIGGERED = 'Detailed Evaluation Not Triggered by Screening Score';

    public const LABEL_UNAVAILABLE = 'Screening Recommendation Unavailable';

    /**
     * @return array<string, mixed>
     */
    public function evaluate(Assessment $assessment): array
    {
        $assessment->load(['structuralDetail']);

        return $this->evaluateStructuralDetail($assessment->structuralDetail);
    }

    /**
     * @return array<string, mixed>
     */
    public function evaluateStructuralDetail(?AssessmentStructuralDetail $structuralDetail): array
    {
        $cutoff = $this->screeningCutoff();

        if ($structuralDetail === null || $structuralDetail->final_level_one_score === null) {
            return [
                'available' => false,
                'final_level_one_score' => null,
                'screening_cutoff' => $this->formatScore($cutoff),
                'recommendation_code' => self::CODE_UNAVAILABLE,
                'recommendation_label' => self::LABEL_UNAVAILABLE,
                'explanation' => 'A persisted Final Level 1 Score is required before a screening recommendation can be generated.',
            ];
        }

        $finalScore = (float) $structuralDetail->final_level_one_score;
        $belowCutoff = $finalScore < $cutoff;

        return [
            'available' => true,
            'final_level_one_score' => $this->formatScore($finalScore),
            'screening_cutoff' => $this->formatScore($cutoff),
            'recommendation_code' => $belowCutoff
                ? self::CODE_DETAILED_EVALUATION_RECOMMENDED
                : self::CODE_SCREENING_THRESHOLD_NOT_TRIGGERED,
            'recommendation_label' => $belowCutoff
                ? self::LABEL_DETAILED_EVALUATION_RECOMMENDED
                : self::LABEL_SCREENING_THRESHOLD_NOT_TRIGGERED,
            'explanation' => $belowCutoff
                ? 'The Final Level 1 Score is below the configured FEMA P-154 screening cutoff. Further detailed seismic evaluation should be considered; this is a screening result, not an engineering diagnosis.'
                : 'The Final Level 1 Score is at or above the configured FEMA P-154 screening cutoff. The screening score alone does not trigger detailed evaluation and does not certify building performance.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function persistForAssessment(Assessment $assessment): array
    {
        $assessment->load(['structuralDetail']);
        $structuralDetail = $assessment->structuralDetail;
        $recommendation = $this->evaluateStructuralDetail($structuralDetail);

        if (! $recommendation['available'] || $structuralDetail === null) {
            return $recommendation;
        }

        $generatedAt = Carbon::now();

        $structuralDetail->forceFill([
            'level_one_screening_cutoff_snapshot' => $recommendation['screening_cutoff'],
            'level_one_recommendation_code' => $recommendation['recommendation_code'],
            'level_one_recommendation_label' => $recommendation['recommendation_label'],
            'level_one_recommendation_explanation' => $recommendation['explanation'],
            'level_one_recommendation_generated_at' => $generatedAt,
        ])->save();

        return array_merge($recommendation, [
            'generated_at' => $generatedAt,
        ]);
    }

    private function screeningCutoff(): float
    {
        return (float) config('cbears.fema.level_one_screening_cutoff', '2.00');
    }

    private function formatScore(float $score): string
    {
        return number_format($score, 2, '.', '');
    }
}