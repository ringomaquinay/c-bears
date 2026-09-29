<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use App\Filament\Resources\Assessments\Pages\Concerns\CalculatesLevelOneScore;
use App\Filament\Resources\Assessments\Pages\Concerns\CompletesAssessment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessment extends ViewRecord
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
            EditAction::make()
                ->hidden(fn (): bool => $this->record->isCompleted()),
        ];
    }
}
