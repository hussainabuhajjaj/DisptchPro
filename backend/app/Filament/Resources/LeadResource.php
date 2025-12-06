<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\TasksRelationManager;
use App\Filament\Resources\LeadResource\Widgets\LeadPipelineWidget;
use App\Models\Lead;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-flag';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Leads';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->required()->label('Full name'),
                        TextInput::make('company_name')->label('Company'),
                        TextInput::make('email')->email(),
                        TextInput::make('phone'),
                        TextInput::make('whatsapp'),
                        Select::make('preferred_contact')
                            ->options([
                                'phone' => 'Phone',
                                'whatsapp' => 'WhatsApp',
                                'email' => 'Email',
                            ])
                            ->label('Preferred contact')
                            ->native(false),
                        TextInput::make('timezone')->label('Time zone'),
                    ]),
                ]),
            Section::make('Company & Authority')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('mc_number')->label('MC #'),
                        TextInput::make('dot_number')->label('DOT #'),
                        TextInput::make('years_in_business')->numeric()->minValue(0)->label('Years in business'),
                        TextInput::make('website')->url()->columnSpanFull(),
                    ]),
                ]),
            Section::make('Equipment & Ops')
                ->schema([
                    CheckboxList::make('equipment')
                        ->options([
                            'dry_van' => 'Dry Van',
                            'reefer' => 'Reefer',
                            'flatbed' => 'Flatbed',
                            'hotshot' => 'Hotshot',
                            'box_truck' => 'Box Truck',
                            'power_only' => 'Power Only',
                            'other' => 'Other',
                        ])
                        ->columns(3),
                    Grid::make(3)->schema([
                        TextInput::make('trucks_count')->numeric()->label('Number of trucks'),
                        TextInput::make('min_rate_per_mile')->numeric()->label('Min rate/mile'),
                        TextInput::make('max_deadhead_miles')->numeric()->label('Max deadhead (mi)'),
                    ]),
                    Grid::make(3)->schema([
                        Forms\Components\Toggle::make('currently_running')->label('Currently running'),
                        Forms\Components\Toggle::make('working_with_dispatcher')->label('Has dispatcher'),
                        Forms\Components\Toggle::make('runs_weekends')->label('Runs weekends'),
                    ]),
                    Textarea::make('home_time')->label('Home time needs')->rows(2),
                    CheckboxList::make('preferred_load_types')
                        ->options([
                            'spot' => 'Spot',
                            'contract' => 'Contract',
                            'dedicated' => 'Dedicated',
                            'drop_and_hook' => 'Drop & Hook',
                            'power_only' => 'Power Only',
                        ])
                        ->columns(3)
                        ->label('Load types'),
                    Textarea::make('preferred_lanes')->rows(2)->helperText('States / regions they prefer'),
                ]),
            Section::make('Pipeline & Ownership')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'qualified' => 'Qualified',
                                'converted' => 'Converted',
                                'lost' => 'Lost',
                            ])
                            ->default('new')
                            ->required()
                            ->native(false),
                        Select::make('pipeline_stage_id')
                            ->relationship('pipelineStage', 'name', fn ($query) => $query->orderBy('position'))
                            ->label('Stage')
                            ->native(false),
                        Select::make('lead_source_id')
                            ->relationship('source', 'name')
                            ->label('Source')
                            ->native(false),
                        Select::make('owner_id')
                            ->relationship('owner', 'name')
                            ->label('Owner/Dispatcher')
                            ->native(false),
                        Select::make('assigned_to')
                            ->relationship('assignee', 'name')
                            ->label('Assignee')
                            ->native(false),
                    ]),
                    Select::make('tags')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->preload()
                        ->native(false),
                    Grid::make(2)->schema([
                        DateTimePicker::make('last_contact_at')->label('Last contact'),
                        DateTimePicker::make('next_follow_up_at')->label('Next follow-up'),
                    ]),
                ]),
            Section::make('Context')
                ->schema([
                    TextInput::make('expectation_rate')->numeric()->label('Expected RPM'),
                    TextInput::make('current_weekly_gross')->numeric()->label('Current weekly gross'),
                    Textarea::make('objections')->rows(2),
                    Textarea::make('notes')->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['pipelineStage', 'source', 'owner']))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('company_name')->label('Company')->searchable(),
                BadgeColumn::make('pipelineStage.name')
                    ->label('Stage')
                    ->colors(['gray'])
                    ->sortable(),
                BadgeColumn::make('status')->label('Status'),
                TextColumn::make('source.name')->label('Source')->toggleable(),
                TextColumn::make('owner.name')->label('Owner')->toggleable(),
                TextColumn::make('assignee.name')->label('Assignee')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('next_follow_up_at')->dateTime()->label('Next follow-up')->sortable(),
                TextColumn::make('last_contact_at')->dateTime()->label('Last contact')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->date()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pipeline_stage_id')->label('Stage')->relationship('pipelineStage', 'name'),
                SelectFilter::make('lead_source_id')->label('Source')->relationship('source', 'name'),
                SelectFilter::make('owner_id')->label('Owner')->relationship('owner', 'name'),
                SelectFilter::make('status')->options([
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'qualified' => 'Qualified',
                    'converted' => 'Converted',
                    'lost' => 'Lost',
                ]),
            ])
            ->recordActions([
        EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LeadPipelineWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
