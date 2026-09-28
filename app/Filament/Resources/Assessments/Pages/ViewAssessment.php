<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use App\Filament\Resources\Assessments\Pages\Concerns\CalculatesLevelOneScore;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessment extends ViewRecord
{
    use CalculatesLevelOneScore;

    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->calculateLevelOneScoreAction(),
            EditAction::make(),
        ];
    }
}
