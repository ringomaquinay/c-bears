<?php

namespace App\Filament\Resources\Assessments\Pages\Concerns;

use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

trait CalculatesLevelOneScore
{
    protected function calculateLevelOneScoreAction(): Action
    {
        return Action::make('calculateLevelOneScore')
            ->label(fn (): string => $this->record->structuralDetail?->level_one_calculated_at
                ? 'Refresh Level 1 Score'
                : 'Calculate Level 1 Score')
            ->disabled(fn (): bool => $this->record->isCompleted())
            ->action(function (FemaLevelOneScoreSnapshotter $snapshotter): void {
                try {
                    $result = $snapshotter->calculateAndPersist($this->record->refresh());
                } catch (Throwable) {
                    Notification::make()
                        ->title('Level 1 score could not be calculated.')
                        ->body('An unexpected calculation error occurred. Please review the assessment inputs and try again.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($result['persisted']) {
                    $this->record->refresh();
                    $this->refreshFormAfterLevelOneScoreCalculation();

                    Notification::make()
                        ->title('Level 1 score calculated.')
                        ->body('The persisted Level 1 scoring snapshot has been updated.')
                        ->success()
                        ->send();

                    return;
                }

                $lookup = $result['lookup'];
                $messages = array_merge(
                    $lookup['missing_inputs'] ?? [],
                    $lookup['errors'] ?? [],
                );

                Notification::make()
                    ->title('Level 1 score is not ready.')
                    ->body($messages === []
                        ? 'Complete the required FEMA structural inputs before calculating the score.'
                        : 'Resolve these items: ' . implode(', ', $messages))
                    ->warning()
                    ->send();
            });
    }

    protected function refreshFormAfterLevelOneScoreCalculation(): void
    {
        if (method_exists($this, 'fillForm')) {
            $this->fillForm();
        }
    }
}
