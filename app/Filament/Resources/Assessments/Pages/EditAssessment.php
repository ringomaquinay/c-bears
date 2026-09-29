<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use App\Filament\Resources\Assessments\Pages\Concerns\CalculatesLevelOneScore;
use App\Filament\Resources\Assessments\Pages\Concerns\CompletesAssessment;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessment extends EditRecord
{
    use CalculatesLevelOneScore;
    use CompletesAssessment;

    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->calculateLevelOneScoreAction(),
            $this->completeAssessmentAction(),
            Action::make('viewReport')
                ->label('View Report')
                ->url(fn (): string => $this->getResourceUrl('report'))
                ->visible(fn (): bool => $this->record->isCompleted() && $this->record->assessment_level === 'Level 1'),
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
