<?php

namespace App\Filament\Resources\Assessments;

use App\Filament\Resources\Assessments\Pages\CreateAssessment;
use App\Filament\Resources\Assessments\Pages\EditAssessment;
use App\Filament\Resources\Assessments\Pages\ListAssessments;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Models\Assessment;
use App\Models\Building;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                Section::make('Assessment Identification')
                    ->description('Assessment identity and linked building record.')
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
                    ])
                    ->columns(2),

                Section::make('Assessment Details')
                    ->description('Core assessment date, level, type, and assigned assessor.')
                    ->schema([
                        DatePicker::make('assessment_date')
                            ->label('Assessment Date')
                            ->required(),
                        TimePicker::make('assessment_time')
                            ->label('Assessment Time')
                            ->seconds(false),
                        Select::make('assessor_id')
                            ->label('Assessor')
                            ->relationship('assessor', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('assessment_type')
                            ->label('Assessment Type')
                            ->required()
                            ->options([
                                'Initial' => 'Initial',
                                'Reassessment' => 'Reassessment',
                            ]),
                        Select::make('assessment_level')
                            ->label('Assessment Level')
                            ->required()
                            ->options([
                                'Level 1' => 'Level 1',
                                'Level 2' => 'Level 2',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Status and Remarks')
                    ->description('Basic assessment status only. Workflow actions are not implemented yet.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(static::statusOptions())
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
                Section::make('Assessment Identification')
                    ->schema([
                        TextEntry::make('assessment_number')
                            ->label('Assessment Number'),
                        TextEntry::make('building.building_name')
                            ->label('Building')
                            ->formatStateUsing(fn (Assessment $record): string => "{$record->building?->building_code} - {$record->building?->building_name}"),
                    ])
                    ->columns(2),

                Section::make('Assessment Details')
                    ->schema([
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
                    ])
                    ->columns(2),

                Section::make('Status and Remarks')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state ?: '-')
                            ->color(fn (?string $state): string => static::statusColor($state)),
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
        ];
    }

    protected static function statusOptions(): array
    {
        return [
            'Draft' => 'Draft',
            'For Review' => 'For Review',
            'Reviewed' => 'Reviewed',
            'Completed' => 'Completed',
            'Returned' => 'Returned',
            'Cancelled' => 'Cancelled',
            'Incomplete' => 'Incomplete',
        ];
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
