<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettlementResource\Pages;
use App\Filament\Resources\SettlementResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\SettlementResource\RelationManagers\PaymentsRelationManager;
use App\Models\Settlement;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Load;
use App\Models\SettlementPayment;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Carbon;
use UnitEnum;
use BackedEnum;
use Filament\Notifications\Notification;
use App\Models\CreditBalance;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;

class SettlementResource extends Resource
{
    protected static ?string $model = Settlement::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static UnitEnum|string|null $navigationGroup = 'Financials';
    protected static ?string $navigationLabel = 'Settlements';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Settlement')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('settlement_type')
                            ->label('Type')
                            ->required()
                            ->options([
                                'carrier' => 'Carrier',
                                'driver' => 'Driver',
                            ])
                            ->live(),
                        Select::make('entity_id')
                            ->label('Carrier / Driver')
                            ->options(function (callable $get) {
                                return $get('settlement_type') === 'driver'
                                    ? Driver::query()->pluck('name', 'id')
                                    : Carrier::query()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->preload(),
                        Select::make('load_id')
                            ->label('Load')
                            ->options(Load::query()->pluck('load_number', 'id'))
                            ->searchable()
                            ->helperText('Optional: link settlement to a load.'),
                        DatePicker::make('issue_date')->label('Issue date')->default(today()),
                        Select::make('status')->options([
                            'draft' => 'Draft',
                            'issued' => 'Issued',
                            'partial' => 'Partial',
                            'paid' => 'Paid',
                            'void' => 'Void',
                        ])->default('draft'),
                    ]),
                ]),
            Section::make('Totals')
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('total_preview')
                            ->label('Total')
                            ->content(fn (?Settlement $record) => $record ? '$' . number_format($record->total, 2) : '$0.00'),
                        Placeholder::make('balance_preview')
                            ->label('Balance')
                            ->content(fn (?Settlement $record) => $record ? '$' . number_format($record->balance, 2) : '$0.00'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('issue_date', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->columns([
                Tables\Columns\TextColumn::make('settlement_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => $state === 'driver' ? 'primary' : 'secondary'),
                Tables\Columns\TextColumn::make('entity.name')
                    ->label('Carrier / Driver')
                    ->getStateUsing(function (Settlement $record) {
                        return optional($record->entity)->name;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('credits_available')
                    ->label('Credits')
                    ->state(fn (Settlement $record) => CreditBalance::where('entity_type', $record->settlement_type)->where('entity_id', $record->entity_id)->sum('remaining'))
                    ->formatStateUsing(fn ($state) => '$' . number_format($state ?? 0, 2))
                    ->badge()
                    ->color(fn ($state) => ($state ?? 0) > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('loadRelation.load_number')->label('Load'),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'void' => 'gray',
                        'issued' => 'primary',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('usd')
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('balance')
                    ->money('usd')
                    ->summarize(Sum::make()->label('Balance')),
                Tables\Columns\TextColumn::make('aging')
                    ->label('Aging')
                    ->state(function (Settlement $record) {
                        if (!$record->issue_date || $record->balance <= 0) {
                            return 'Current';
                        }
                        $days = Carbon::parse($record->issue_date)->diffInDays(now(), false);
                        return $days <= 0 ? 'Current' : $days . 'd';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('settlement_type')->options([
                    'carrier' => 'Carrier',
                    'driver' => 'Driver',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'issued' => 'Issued',
                    'partial' => 'Partial',
                    'paid' => 'Paid',
                    'void' => 'Void',
                ]),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Settlement $record) => route('admin.documents.settlements.pdf', ['settlement' => $record->id, 'template' => 'settlement']))
                    ->openUrlInNewTab(),
                Actions\Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Settlement PDF Preview')
                    ->modalContent(fn (Settlement $record) => view('documents.preview', [
                        'url' => route('admin.documents.settlements.pdf', ['settlement' => $record->id, 'template' => 'settlement']),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
                Actions\Action::make('adjust_payments')
                    ->label('Adjust payments')
                    ->icon('heroicon-o-wrench')
                    ->url(fn (Settlement $record) => route('filament.admin.resources.settlements.edit', $record) . '#payments')
                    ->openUrlInNewTab(),
                Actions\Action::make('apply_credit')
                    ->label('Apply credit')
                    ->icon('heroicon-o-arrow-down-on-square-stack')
                    ->color('success')
                    ->visible(fn (Settlement $record) => CreditBalance::where('entity_type', $record->settlement_type)->where('entity_id', $record->entity_id)->where('remaining', '>', 0)->exists())
                    ->form(function (Settlement $record) {
                        $credits = CreditBalance::where('entity_type', $record->settlement_type)
                            ->where('entity_id', $record->entity_id)
                            ->where('remaining', '>', 0)
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => 'Credit #' . $c->id . ' - $' . number_format($c->remaining, 2) . ($c->expires_at ? ' (exp ' . $c->expires_at->format('Y-m-d') . ')' : '')]);

                        $balance = max($record->balance ?? ($record->total - $record->payments()->sum('amount')), 0);
                        return [
                            Forms\Components\Select::make('credit_id')
                                ->label('Credit')
                                ->options($credits)
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->numeric()
                                ->required()
                                ->default(fn () => $balance)
                                ->helperText('Max: remaining credit and settlement balance'),
                        ];
                    })
                    ->action(function (array $data, Settlement $record) {
                        $credit = CreditBalance::find($data['credit_id'] ?? null);
                        if (!$credit || $credit->remaining <= 0) {
                            Notification::make()->title('Credit not available')->warning()->send();
                            return;
                        }
                        $balance = max($record->balance ?? ($record->total - $record->payments()->sum('amount')), 0);
                        $apply = min($balance, $credit->remaining, $data['amount'] ?? $credit->remaining);
                        if ($apply <= 0) {
                            Notification::make()->title('Nothing to apply')->warning()->send();
                            return;
                        }
                        \App\Models\SettlementPayment::create([
                            'settlement_id' => $record->id,
                            'paid_at' => now(),
                            'amount' => $apply,
                            'method' => 'credit',
                            'reference' => 'Credit #' . $credit->id,
                        ]);
                        $credit->decrement('remaining', $apply);
                        $record->refreshTotals();
                        Notification::make()->title('Applied $' . number_format($apply, 2) . ' credit')->success()->send();
                    }),
                Actions\Action::make('record_payment')
                    ->label('Record payment')
                    ->icon('heroicon-o-banknotes')
                    ->form([
                        DatePicker::make('paid_at')->default(today())->required(),
                        TextInput::make('amount')->numeric()->required(),
                        TextInput::make('method')->label('Method'),
                        TextInput::make('reference')->label('Reference'),
                        \Filament\Forms\Components\Select::make('overpay_handling')
                            ->label('If amount exceeds balance')
                            ->options([
                                'cap' => 'Cap to balance',
                                'credit' => 'Create carrier/driver credit',
                                'refund' => 'Record refund note',
                            ])
                            ->default('cap'),
                        \Filament\Forms\Components\Textarea::make('note')->columnSpanFull(),
                    ])
                    ->action(function (array $data, Settlement $record) {
                        $outstanding = $record->balance ?? ($record->total - $record->payments()->sum('amount'));
                        if ($outstanding <= 0) {
                            Notification::make()->title('Nothing to pay')->warning()->send();
                            return;
                        }
                        $handling = $data['overpay_handling'] ?? 'cap';
                        $requested = $data['amount'];
                        $payAmount = $handling === 'cap' ? min($requested, $outstanding) : min($requested, max($outstanding, 0));
                        SettlementPayment::create([
                            'settlement_id' => $record->id,
                            'paid_at' => $data['paid_at'],
                            'amount' => $payAmount,
                            'method' => $data['method'] ?? null,
                            'reference' => $data['reference'] ?? null,
                        ]);
                        $record->refreshTotals();
                        $extra = $requested - $payAmount;
                        if ($extra > 0 && $handling === 'credit') {
                            CreditBalance::create([
                                'entity_type' => $record->settlement_type,
                                'entity_id' => $record->entity_id,
                                'source_type' => 'settlement',
                                'source_id' => $record->id,
                                'amount' => $extra,
                                'remaining' => $extra,
                                'reason' => 'overpayment',
                            ]);
                            Notification::make()->title('Overpayment saved as credit')->info()->send();
                        } elseif ($extra > 0 && $handling === 'refund') {
                            Notification::make()->title('Overpayment noted as refund – adjust manually')->info()->send();
                        } elseif ($payAmount < ($data['amount'] ?? 0)) {
                            Notification::make()->title('Payment capped to outstanding balance')->info()->send();
                        }
                    }),
                Actions\Action::make('mark_paid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'paid')
                    ->requiresConfirmation()
                    ->action(function (Settlement $record) {
                        $record->update([
                            'status' => 'paid',
                            'balance' => 0,
                        ]);
                    }),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            PaymentsRelationManager::class,
            \App\Filament\Resources\SettlementResource\RelationManagers\PaymentsInlineRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettlements::route('/'),
            'create' => Pages\CreateSettlement::route('/create'),
            'edit' => Pages\EditSettlement::route('/{record}/edit'),
        ];
    }
}
