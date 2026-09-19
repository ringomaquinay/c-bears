<?php

namespace App\Filament\Resources\Buildings;

use App\Filament\Resources\Buildings\Pages\CreateBuilding;
use App\Filament\Resources\Buildings\Pages\EditBuilding;
use App\Filament\Resources\Buildings\Pages\ListBuildings;
use App\Filament\Resources\Buildings\Pages\ViewBuilding;
use App\Models\Building;
use BackedEnum;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Buildings';

    protected static string|UnitEnum|null $navigationGroup = 'Building Registry';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Building';

    protected static ?string $pluralModelLabel = 'Buildings';

    protected static ?string $recordTitleAttribute = 'building_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Building Identification')
                    ->description('Permanent registry identity for this building.')
                    ->schema([
                        TextInput::make('building_code')
                            ->label('C-BEARS Building Code')
                            ->helperText('Generated automatically when the building record is saved.')
                            ->default(fn (): string => Building::previewNextBuildingCode())
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        TextInput::make('building_name')
                            ->label('Building Name')
                            ->helperText('Use the official or commonly recognized building name.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('owner_or_responsible_office')
                            ->label('Owner / Responsible Office')
                            ->helperText('Office, department, or unit responsible for the building.')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->description('Basic location details for registry and future point-based mapping.')
                    ->schema([
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        TextInput::make('barangay')
                            ->label('Barangay')
                            ->maxLength(255),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->helperText('Decimal degrees, from -90 to 90.')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->helperText('Decimal degrees, from -180 to 180.')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                    ])
                    ->columns(3),

                Section::make('Building Information')
                    ->description('Descriptive building characteristics for the registry record.')
                    ->schema([
                        TextInput::make('primary_occupancy')
                            ->label('Primary Occupancy')
                            ->helperText('Primary use of the building, if known.')
                            ->maxLength(255),
                        TextInput::make('number_of_storeys')
                            ->label('Number of Storeys')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(200),
                        TextInput::make('year_built')
                            ->label('Year Built')
                            ->helperText('Use best available year if exact records are unavailable.')
                            ->numeric()
                            ->integer()
                            ->minValue(1800)
                            ->maxValue(now()->year),
                        TextInput::make('approximate_floor_area')
                            ->label('Approximate Floor Area')
                            ->helperText('Approximate total floor area, if available.')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),

                Section::make('Permits / References')
                    ->description('Permit numbers and local property references, if available.')
                    ->schema([
                        TextInput::make('building_permit_number')
                            ->label('Building Permit Number')
                            ->maxLength(255),
                        TextInput::make('occupancy_permit_number')
                            ->label('Occupancy Permit Number')
                            ->maxLength(255),
                        TextInput::make('property_reference_no')
                            ->label('Property Reference No.')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Status / Remarks')
                    ->description('Current registry status and general administrative notes.')
                    ->schema([
                        Select::make('record_status')
                            ->label('Record Status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'archived' => 'Archived',
                            ])
                            ->default('active'),
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
                TextEntry::make('building_code')
                    ->label('Building Code'),
                TextEntry::make('building_name')
                    ->label('Building Name'),
                TextEntry::make('owner_or_responsible_office')
                    ->label('Owner / Responsible Office')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->label('Address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('barangay')
                    ->label('Barangay')
                    ->placeholder('-'),
                TextEntry::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('primary_occupancy')
                    ->label('Primary Occupancy')
                    ->placeholder('-'),
                TextEntry::make('number_of_storeys')
                    ->label('Number of Storeys')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('year_built')
                    ->label('Year Built')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('approximate_floor_area')
                    ->label('Approximate Floor Area')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('building_permit_number')
                    ->label('Building Permit Number')
                    ->placeholder('-'),
                TextEntry::make('occupancy_permit_number')
                    ->label('Occupancy Permit Number')
                    ->placeholder('-'),
                TextEntry::make('property_reference_no')
                    ->label('Property Reference No.')
                    ->placeholder('-'),
                TextEntry::make('remarks')
                    ->label('Remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('record_status')
                    ->label('Record Status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('building_name')
            ->columns([
                TextColumn::make('building_code')
                    ->label('C-BEARS Code')
                    ->searchable(),
                TextColumn::make('building_name')
                    ->label('Building Name')
                    ->searchable(),
                TextColumn::make('owner_or_responsible_office')
                    ->label('Owner / Responsible Office')
                    ->searchable(),
                TextColumn::make('barangay')
                    ->label('Barangay')
                    ->searchable(),
                TextColumn::make('record_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? ucwords(str_replace('_', ' ', $state)) : '-')
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('primary_occupancy')
                    ->label('Primary Occupancy')
                    ->toggleable(),
                TextColumn::make('number_of_storeys')
                    ->label('Storeys')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('year_built')
                    ->label('Year Built')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('approximate_floor_area')
                    ->label('Approx. Floor Area')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('building_permit_number')
                    ->label('Building Permit No.')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('occupancy_permit_number')
                    ->label('Occupancy Permit No.')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('property_reference_no')
                    ->label('Property Reference No.')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
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
            'index' => ListBuildings::route('/'),
            'create' => CreateBuilding::route('/create'),
            'view' => ViewBuilding::route('/{record}'),
            'edit' => EditBuilding::route('/{record}/edit'),
        ];
    }
}
