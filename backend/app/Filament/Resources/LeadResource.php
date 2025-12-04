<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static UnitEnum|string|null $navigationGroup = 'CRM & Accounts';

    public static function getNavigationBadge(): ?string
    {
        $new = Lead::where('status', 'new')->count();
        return $new > 0 ? (string) $new : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Lead info')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('company_name'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\Select::make('source')->options([
                            'website' => 'Website',
                            'referral' => 'Referral',
                            'cold_call' => 'Cold Call',
                            'ads' => 'Ads',
                            'other' => 'Other',
                        ])->default('website'),
                        Forms\Components\Select::make('status')->options([
                            'new' => 'New',
                            'contacted' => 'Contacted',
                            'qualified' => 'Qualified',
                            'converted' => 'Converted',
                            'lost' => 'Lost',
                        ])->default('new'),
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(User::query()->pluck('name', 'id'))
                            ->searchable(),
                    ]),
                ]),
            Section::make('Freight & notes')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('origin'),
                        Forms\Components\TextInput::make('destination'),
                        Forms\Components\TextInput::make('freight_details'),
                    ]),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('company_name'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'contacted',
                        'info' => 'qualified',
                        'success' => 'converted',
                        'danger' => 'lost',
                    ]),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assignee'),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'converted' => 'Converted',
                        'lost' => 'Lost',
                    ]),
                Tables\Filters\SelectFilter::make('source')
                    ->multiple()
                    ->options([
                        'website' => 'Website',
                        'referral' => 'Referral',
                        'cold_call' => 'Cold Call',
                        'ads' => 'Ads',
                        'other' => 'Other',
                    ]),
            ])
            ->emptyStateHeading('No leads yet')
            ->emptyStateDescription('Capture website and inbound leads to work your pipeline.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
