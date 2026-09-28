<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use App\Filament\Resources\Assessments\Pages\Concerns\CalculatesLevelOneScore;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessment extends EditRecord
{
    use CalculatesLevelOneScore;

    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->calculateLevelOneScoreAction(),
            ViewAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->refresh();

        $structuralDetail = $this->record->structuralDetail;

        if (! $structuralDetail) {
            return;
        }

        $structuralDetail->syncReferenceSnapshots();
        $structuralDetail->save();
    }
}
