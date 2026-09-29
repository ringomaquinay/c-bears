<?php

namespace App\Filament\Resources\Assessments;

use App\Filament\Resources\Assessments\Pages\CreateAssessment;
use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Filament\Resources\Assessments\Pages\ListAssessments;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Filament\Resources\Assessments\Pages\ViewAssessmentReport;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use BackedEnum;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Assessments';

    protected static string|UnitEnum|null $navigationGroup = 'Assessment Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Assessment';

    protected static ?string $pluralModelLabel = 'Assessments';

    protected static ?string $recordTitleAttribute = 'assessment_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assessment Information')
                    ->description('Assessment identity, linked building, FEMA version, and core event details.')
                    ->schema([
                        TextInput::make('assessment_number')
                            ->label('Assessment Number')
                            ->helperText('Generated automatically when the assessment is saved.')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Generated after save')
                            ->maxLength(255),
                        Select::make('building_id')
                            ->label('Building')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->required()
                            ->relationship(
                                name: 'building',
                                titleAttribute: 'building_name',
                                modifyQueryUsing: fn ($query) => $query->orderBy('building_code'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Building $record): string => "{$record->building_code} - {$record->building_name}")
                            ->searchable(['building_code', 'building_name'])
                            ->forceSearchCaseInsensitive()
                            ->preload(),
                        DatePicker::make('assessment_date')
                            ->label('Assessment Date')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->required(),
                        TimePicker::make('assessment_time')
                            ->label('Assessment Time')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->seconds(false),
                        Select::make('assessor_id')
                            ->label('Assessor')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->relationship('assessor', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('assessment_type')
                            ->label('Assessment Type')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->required()
                            ->options([
                                'Initial' => 'Initial',
                                'Reassessment' => 'Reassessment',
                            ]),
                        Select::make('assessment_level')
                            ->label('Assessment Level')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->required()
                            ->options([
                                'Level 1' => 'Level 1',
                                'Level 2' => 'Level 2',
                            ]),
                        Select::make('fema_version_id')
                            ->label('FEMA Version')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(fn (): array => static::femaVersionOptions())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('FEMA Classification')
                    ->relationship(
                        'structuralDetail',
                        condition: fn (?array $state): bool => static::structuralDetailHasInput($state),
                    )
                    ->schema([
                        Select::make('fema_building_type_id')
                            ->label('FEMA Building Type')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(fn (Get $get): array => static::femaBuildingTypeOptions($get('../../fema_version_id')))
                            ->searchable()
                            ->preload(),
                        Select::make('seismicity_level')
                            ->label('Seismicity Level')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(static::seismicityLevelOptions()),
                        TextInput::make('fema_version_code_snapshot')
                            ->label('FEMA Version Code Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                        TextInput::make('fema_version_title_snapshot')
                            ->label('FEMA Version Title Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                        TextInput::make('fema_version_edition_snapshot')
                            ->label('FEMA Version Edition Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                        TextInput::make('fema_building_type_code_snapshot')
                            ->label('Building Type Code Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                        TextInput::make('fema_building_type_name_snapshot')
                            ->label('Building Type Name Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                        TextInput::make('material_category_snapshot')
                            ->label('Material Category Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                        TextInput::make('structural_system_snapshot')
                            ->label('Structural System Snapshot')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Captured on save'),
                    ])
                    ->columns(2),

                Section::make('Site / Soil')
                    ->relationship(
                        'structuralDetail',
                        condition: fn (?array $state): bool => static::structuralDetailHasInput($state),
                    )
                    ->schema([
                        Select::make('soil_type')
                            ->label('Soil Type')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(static::soilTypeOptions()),
                        Textarea::make('site_condition_notes')
                            ->label('Site Condition Notes')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Irregularities')
                    ->relationship(
                        'structuralDetail',
                        condition: fn (?array $state): bool => static::structuralDetailHasInput($state),
                    )
                    ->schema([
                        Select::make('vertical_irregularity_type')
                            ->label('Vertical Irregularity')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(static::verticalIrregularityOptions()),
                        Select::make('plan_irregularity_type')
                            ->label('Plan Irregularity')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(static::planIrregularityOptions()),
                    ])
                    ->columns(2),

                Section::make('Code Conditions')
                    ->relationship(
                        'structuralDetail',
                        condition: fn (?array $state): bool => static::structuralDetailHasInput($state),
                    )
                    ->schema([
                        Select::make('has_pre_code_condition')
                            ->label('Pre-Code Condition')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->boolean('Yes', 'No', 'Unknown / Not Yet Assessed'),
                        Select::make('has_post_benchmark_condition')
                            ->label('Post-Benchmark Condition')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->boolean('Yes', 'No', 'Unknown / Not Yet Assessed'),
                    ])
                    ->columns(2),

                Section::make('Structural Notes')
                    ->relationship(
                        'structuralDetail',
                        condition: fn (?array $state): bool => static::structuralDetailHasInput($state),
                    )
                    ->schema([
                        Textarea::make('structural_observation_notes')
                            ->label('Structural Observation Notes')
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Section::make('FEMA Level 1 Score Summary')
                    ->description('Read-only persisted Level 1 scoring snapshot. Use the page action to calculate or refresh it.')
                    ->schema(static::levelOneScoreSummarySchema())
                    ->columns(2),
                Section::make('Screening Recommendation')
                    ->description('Read-only FEMA P-154 Level 1 screening recommendation snapshot. This is not a safety certification.')
                    ->schema(static::screeningRecommendationSchema())
                    ->columns(2),
                Section::make('Status and Remarks')
                    ->description('Draft status is editable during assessment. Use workflow actions for completion.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->disabled(fn (mixed $record): bool => static::isRecordCompleted($record))
                            ->options(fn (?Assessment $record): array => static::statusOptions($record?->isCompleted() ?? false))
                            ->default('Draft'),
                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assessment Information')
                    ->schema([
                        TextEntry::make('assessment_number')
                            ->label('Assessment Number'),
                        TextEntry::make('building.building_name')
                            ->label('Building')
                            ->formatStateUsing(fn (Assessment $record): string => "{$record->building?->building_code} - {$record->building?->building_name}"),
                        TextEntry::make('assessment_date')
                            ->label('Assessment Date')
                            ->date(),
                        TextEntry::make('assessment_time')
                            ->label('Assessment Time')
                            ->time()
                            ->placeholder('-'),
                        TextEntry::make('assessor.name')
                            ->label('Assessor')
                            ->placeholder('-'),
                        TextEntry::make('assessment_type')
                            ->label('Assessment Type'),
                        TextEntry::make('assessment_level')
                            ->label('Assessment Level'),
                        TextEntry::make('femaVersion.code')
                            ->label('FEMA Version')
                            ->formatStateUsing(fn (Assessment $record): string => $record->femaVersion ? static::femaVersionLabel($record->femaVersion) : '-')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Structural Detail')
                    ->schema([
                        TextEntry::make('structuralDetail.femaBuildingType.code')
                            ->label('FEMA Building Type')
                            ->formatStateUsing(fn (Assessment $record): string => $record->structuralDetail?->femaBuildingType ? static::femaBuildingTypeLabel($record->structuralDetail->femaBuildingType) : '-')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.seismicity_level')
                            ->label('Seismicity Level')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.soil_type')
                            ->label('Soil Type')
                            ->formatStateUsing(fn (?string $state): string => static::soilTypeOptions()[$state] ?? ($state ?: '-'))
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.vertical_irregularity_type')
                            ->label('Vertical Irregularity')
                            ->formatStateUsing(fn (?string $state): string => static::verticalIrregularityOptions()[$state] ?? ($state ?: '-'))
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.plan_irregularity_type')
                            ->label('Plan Irregularity')
                            ->formatStateUsing(fn (?string $state): string => static::planIrregularityOptions()[$state] ?? ($state ?: '-'))
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.has_pre_code_condition')
                            ->label('Pre-Code Condition')
                            ->formatStateUsing(fn (mixed $state): string => static::nullableBooleanLabel($state)),
                        TextEntry::make('structuralDetail.has_post_benchmark_condition')
                            ->label('Post-Benchmark Condition')
                            ->formatStateUsing(fn (mixed $state): string => static::nullableBooleanLabel($state)),
                        TextEntry::make('structuralDetail.site_condition_notes')
                            ->label('Site Condition Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('structuralDetail.structural_observation_notes')
                            ->label('Structural Observation Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Structural Reference Snapshots')
                    ->schema([
                        TextEntry::make('structuralDetail.fema_version_code_snapshot')
                            ->label('FEMA Version Code')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.fema_version_title_snapshot')
                            ->label('FEMA Version Title')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.fema_version_edition_snapshot')
                            ->label('FEMA Version Edition')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.fema_building_type_code_snapshot')
                            ->label('Building Type Code')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.fema_building_type_name_snapshot')
                            ->label('Building Type Name')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.material_category_snapshot')
                            ->label('Material Category')
                            ->placeholder('-'),
                        TextEntry::make('structuralDetail.structural_system_snapshot')
                            ->label('Structural System')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('FEMA Level 1 Score Summary')
                    ->schema(static::levelOneScoreSummarySchema())
                    ->columns(2),
                Section::make('Screening Recommendation')
                    ->schema(static::screeningRecommendationSchema())
                    ->columns(2),
                Section::make('Status and Remarks')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state ?: '-')
                            ->color(fn (?string $state): string => static::statusColor($state)),
                        TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('assessment_number')
            ->columns([
                TextColumn::make('assessment_number')
                    ->label('Assessment Number')
                    ->searchable(),
                TextColumn::make('building.building_name')
                    ->label('Building')
                    ->formatStateUsing(fn (Assessment $record): string => "{$record->building?->building_code} - {$record->building?->building_name}")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assessment_date')
                    ->label('Assessment Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('assessment_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assessment_level')
                    ->label('Level')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ?: '-')
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('structuralDetail.fema_building_type_code_snapshot')
                    ->label('FEMA Type')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('structuralDetail.seismicity_level')
                    ->label('Seismicity')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('assessor.name')
                    ->label('Assessor')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessments::route('/'),
            'create' => CreateAssessment::route('/create'),
            'view' => ViewAssessment::route('/{record}'),
            'edit' => EditAssessment::route('/{record}/edit'),
            'report' => ViewAssessmentReport::route('/{record}/report'),
        ];
    }

    public static function femaVersionLabel(FemaVersion $femaVersion): string
    {
        return trim("{$femaVersion->code} - {$femaVersion->title} ({$femaVersion->edition})");
    }

    public static function femaBuildingTypeLabel(FemaBuildingType $femaBuildingType): string
    {
        return "{$femaBuildingType->code} - {$femaBuildingType->name}";
    }

    public static function soilTypeOptions(): array
    {
        return [
            'SOIL_AB' => 'Soil Type A or B',
            'SOIL_E_LOW_RISE' => 'Soil Type E - 1 to 3 Stories',
            'SOIL_E_MID_HIGH_RISE' => 'Soil Type E - More Than 3 Stories',
        ];
    }

    public static function verticalIrregularityOptions(): array
    {
        return [
            'none' => 'None',
            'moderate' => 'Moderate',
            'severe' => 'Severe',
        ];
    }

    public static function planIrregularityOptions(): array
    {
        return [
            'none' => 'None',
            'irregular' => 'Irregular',
        ];
    }

    public static function seismicityLevelOptions(): array
    {
        return array_combine(
            AssessmentStructuralDetail::SEISMICITY_LEVELS,
            AssessmentStructuralDetail::SEISMICITY_LEVELS,
        );
    }

    protected static function femaVersionOptions(): array
    {
        return FemaVersion::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (FemaVersion $femaVersion): array => [
                $femaVersion->id => static::femaVersionLabel($femaVersion),
            ])
            ->all();
    }

    protected static function femaBuildingTypeOptions(mixed $femaVersionId = null): array
    {
        return FemaBuildingType::query()
            ->when(
                filled($femaVersionId),
                fn (Builder $query) => $query->where('fema_version_id', $femaVersionId),
            )
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (FemaBuildingType $femaBuildingType): array => [
                $femaBuildingType->id => static::femaBuildingTypeLabel($femaBuildingType),
            ])
            ->all();
    }

    protected static function levelOneScoreSummarySchema(): array
    {
        return [
            TextEntry::make('assessment_number')
                ->label('Score Status')
                ->formatStateUsing(fn (Assessment $record): string => $record->structuralDetail?->level_one_calculated_at
                    ? 'Level 1 score calculated'
                    : 'No Level 1 score has been calculated yet.')
                ->columnSpanFull(),
            TextEntry::make('structuralDetail.basic_score_snapshot')
                ->label('Basic Score')
                ->formatStateUsing(fn (mixed $state): string => static::formatScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('structuralDetail.minimum_score_snapshot')
                ->label('Minimum Score')
                ->formatStateUsing(fn (mixed $state): string => static::formatScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('assessment_type')
                ->label('Applied Level 1 Modifiers')
                ->formatStateUsing(fn (Assessment $record): string => static::formatAppliedLevelOneModifiers($record->structuralDetail?->applied_level_one_modifiers_snapshot))
                ->placeholder('No applied Level 1 modifiers.')
                ->columnSpanFull(),
            TextEntry::make('structuralDetail.level_one_modifier_total_snapshot')
                ->label('Level 1 Modifier Total')
                ->formatStateUsing(fn (mixed $state): string => static::formatSignedScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('structuralDetail.calculated_level_one_score')
                ->label('Calculated Level 1 Score')
                ->formatStateUsing(fn (mixed $state): string => static::formatScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('structuralDetail.final_level_one_score')
                ->label('Final Level 1 Score')
                ->formatStateUsing(fn (mixed $state): string => static::formatScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('structuralDetail.level_one_calculated_at')
                ->label('Last Calculated At')
                ->dateTime()
                ->placeholder('-'),
            TextEntry::make('assessment_level')
                ->label('Calculation Details')
                ->formatStateUsing(fn (Assessment $record): string => static::formatLevelOneCalculationDetails($record->structuralDetail?->level_one_calculation_trace))
                ->placeholder('-')
                ->columnSpanFull(),
        ];
    }

    protected static function screeningRecommendationSchema(): array
    {
        return [
            TextEntry::make('structuralDetail.final_level_one_score')
                ->label('Final Level 1 Score')
                ->formatStateUsing(fn (mixed $state): string => static::formatScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('structuralDetail.level_one_screening_cutoff_snapshot')
                ->label('Screening Cutoff')
                ->formatStateUsing(fn (mixed $state): string => static::formatScoreValue($state))
                ->placeholder('-'),
            TextEntry::make('assessment_number')
                ->label('Recommendation')
                ->formatStateUsing(fn (Assessment $record): string => $record->structuralDetail?->level_one_recommendation_label ?: 'Screening recommendation has not been generated yet.')
                ->columnSpanFull(),
            TextEntry::make('assessment_level')
                ->label('Explanation')
                ->formatStateUsing(fn (Assessment $record): string => $record->structuralDetail?->level_one_recommendation_explanation ?: 'Complete the Level 1 assessment to snapshot the screening recommendation.')
                ->columnSpanFull(),
            TextEntry::make('structuralDetail.level_one_recommendation_generated_at')
                ->label('Recommendation Generated At')
                ->dateTime()
                ->placeholder('-'),
        ];
    }
    public static function formatScoreValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return number_format((float) $value, 2, '.', '');
    }

    public static function formatSignedScoreValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $number = (float) $value;

        return ($number >= 0 ? '+' : '') . number_format($number, 2, '.', '');
    }

    public static function formatAppliedLevelOneModifiers(mixed $modifiers): string
    {
        if (! is_array($modifiers) || $modifiers === []) {
            return 'No applied Level 1 modifiers.';
        }

        return collect($modifiers)
            ->map(function (array $modifier): string {
                $code = $modifier['code'] ?? '-';
                $name = $modifier['name'] ?? '-';
                $value = static::formatSignedScoreValue($modifier['value'] ?? null);

                return "{$code} - {$name} - {$value}";
            })
            ->implode("\n");
    }

    public static function formatLevelOneCalculationDetails(mixed $trace): string
    {
        if (! is_array($trace)) {
            return '-';
        }

        $basicScore = static::formatScoreValue($trace['basic_score']['value'] ?? null);
        $modifierTotal = static::formatSignedScoreValue($trace['modifier_total'] ?? null);
        $calculatedScore = static::formatScoreValue($trace['calculated_level_one_score'] ?? null);
        $minimumScore = static::formatScoreValue($trace['minimum_score']['value'] ?? null);
        $finalScore = static::formatScoreValue($trace['final_level_one_score'] ?? null);

        return "Basic Score {$basicScore} + Level 1 Modifier Total {$modifierTotal} = Calculated Score {$calculatedScore}\n"
            . "Final Level 1 Score = max({$calculatedScore}, {$minimumScore}) = {$finalScore}";
    }

    protected static function isRecordCompleted(mixed $record): bool
    {
        if ($record instanceof Assessment) {
            return $record->isCompleted();
        }

        return $record?->assessment?->isCompleted() ?? false;
    }
    protected static function nullableBooleanLabel(mixed $state): string
    {
        return match ($state) {
            true, 1, '1' => 'Yes',
            false, 0, '0' => 'No',
            default => 'Unknown / Not Yet Assessed',
        };
    }

    protected static function structuralDetailHasInput(?array $state): bool
    {
        if (! is_array($state)) {
            return false;
        }

        foreach ([
            'fema_building_type_id',
            'seismicity_level',
            'soil_type',
            'vertical_irregularity_type',
            'plan_irregularity_type',
            'has_pre_code_condition',
            'has_post_benchmark_condition',
            'site_condition_notes',
            'structural_observation_notes',
        ] as $key) {
            if (array_key_exists($key, $state) && $state[$key] !== null && $state[$key] !== '') {
                return true;
            }
        }

        return false;
    }

    protected static function statusOptions(bool $includeCompleted = true): array
    {
        $options = [
            'Draft' => 'Draft',
            'For Review' => 'For Review',
            'Reviewed' => 'Reviewed',
            'Returned' => 'Returned',
            'Cancelled' => 'Cancelled',
            'Incomplete' => 'Incomplete',
        ];

        if ($includeCompleted) {
            $options['Completed'] = 'Completed';
        }

        return $options;
    }

    protected static function statusColor(?string $state): string
    {
        return match ($state) {
            'Draft' => 'gray',
            'For Review' => 'warning',
            'Reviewed' => 'info',
            'Completed' => 'success',
            'Returned', 'Incomplete' => 'danger',
            'Cancelled' => 'gray',
            default => 'gray',
        };
    }
}








