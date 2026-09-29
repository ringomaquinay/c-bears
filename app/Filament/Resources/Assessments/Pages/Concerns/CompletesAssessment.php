<?php

namespace App\Filament\Resources\Assessments\Pages\Concerns;

use App\Services\Assessments\LevelOneCompletionValidator;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Throwable;

trait CompletesAssessment
{
    protected function completeAssessmentAction(): Action
    {
        return Action::make('completeAssessment')
            ->label('Complete Assessment')
            ->disabled(fn (): bool => $this->record->isCompleted())
            ->action(function (LevelOneCompletionValidator $validator): void {
                $assessment = $this->record->refresh();

                if ($assessment->isCompleted()) {
                    Notification::make()
                        ->title('Assessment is already completed.')
                        ->body('Completed assessments are locked for historical review.')
                        ->warning()
                        ->send();

                    return;
                }

                $errors = $validator->completionErrors($assessment);

                if ($errors !== []) {
                    Notification::make()
                        ->title('Assessment cannot be completed yet.')
                        ->body(implode("\n", $errors))
                        ->warning()
                        ->send();

                    return;
                }

                try {
                    $assessment->completeLevelOne();
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Assessment cannot be completed yet.')
                        ->body(collect($exception->errors())->flatten()->implode("\n"))
                        ->warning()
                        ->send();

                    return;
                } catch (Throwable) {
                    Notification::make()
                        ->title('Assessment could not be completed.')
                        ->body('An unexpected workflow error occurred. Please review the assessment and try again.')
                        ->danger()
                        ->send();

                    return;
                }

                $this->record->refresh();
                $this->refreshFormAfterAssessmentCompletion();

                Notification::make()
                    ->title('Assessment completed.')
                    ->body('The Level 1 assessment is now locked for historical review.')
                    ->success()
                    ->send();
            });
    }

    protected function refreshFormAfterAssessmentCompletion(): void
    {
        if (method_exists($this, 'fillForm')) {
            $this->fillForm();
        }
    }
}