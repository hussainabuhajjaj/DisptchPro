<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\PaymentsRelationManager;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Load;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use UnitEnum;
use BackedEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema as DbSchema;
use Filament\Notifications\Notification;
use App\Models\CreditBalance;
use Illuminate\Support\Collection;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static UnitEnum|string|null $navigationGroup = 'Financials';

    public static function getNavigationBadge(): ?string
    {
        if (!DbSchema::hasColumn('invoices', 'balance')) {
            return null;
        }

        $overdue = Invoice::where('balance', '>', 0)
            ->whereDate('due_date', '<', now())
            ->count();
        return $overdue > 0 ? (string) $overdue : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $form): Schema
    {
        $hasLoad = DbSchema::hasColumn('invoices', 'load_id');
        $hasInvoiceDate = DbSchema::hasColumn('invoices', 'invoice_date');

        return $form->schema([
            Section::make('Invoice')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->required()
                            ->options(Client::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        ...($hasLoad ? [
                            Forms\Components\Select::make('load_id')
                                ->label('Load')
                                ->options(Load::query()->pluck('load_number', 'id'))
                                ->searchable()
                                ->helperText('Optional: link the invoice to a load.'),
                        ] : []),
                        TextInput::make('invoice_number')->label('Invoice #')->required(),
                        ...($hasInvoiceDate ? [
                            DatePicker::make('invoice_date')
                                ->label('Invoice date')
                                ->required()
                                ->default(today()),
                        ] : []),
                        DatePicker::make('issue_date')->label('Issue date')->required(),
                        DatePicker::make('due_date')->label('Due date')->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'void' => 'Void',
                            ])
                            ->default('draft'),
                    ]),
                ]),
            Section::make('Financials')
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('total_preview')
                            ->label('Total')
                            ->content(fn (?Invoice $record) => $record ? '$' . number_format($record->total, 2) : '$0.00'),
                        Placeholder::make('balance_preview')
                            ->label('Balance due')
                            ->content(fn (?Invoice $record) => $record ? '$' . number_format($record->balance, 2) : '$0.00'),
                    ]),
                ]),
            Section::make('Notes & attachments')
                ->schema([
                    Grid::make(2)->schema([
                        Textarea::make('notes'),
                        Textarea::make('terms'),
                    ]),
                    FileUpload::make('pdf_path')
                        ->label('Invoice PDF')
                        ->directory('invoices')
                        ->helperText('Attach a generated PDF if needed.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $balanceColumn = DbSchema::hasColumn('invoices', 'balance')
            ? 'balance'
            : (DbSchema::hasColumn('invoices', 'balance_due') ? 'balance_due' : null);
        $hasLoad = DbSchema::hasColumn('invoices', 'load_id');

        return $table
            ->defaultSort('invoice_date', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('client.name')->label('Client')->sortable()->searchable(),
                ...($hasLoad ? [
                    Tables\Columns\TextColumn::make('loadRelation.load_number')->label('Load')->sortable(),
                ] : []),
                Tables\Columns\TextColumn::make('credits_available')
                    ->label('Credits')
                    ->state(fn (Invoice $record) => CreditBalance::where('entity_type', 'client')->where('entity_id', $record->client_id)->sum('remaining'))
                    ->formatStateUsing(fn ($state) => '$' . number_format($state ?? 0, 2))
                    ->badge()
                    ->color(fn ($state) => ($state ?? 0) > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'void' => 'gray',
                        'sent' => 'primary',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('usd')
                    ->summarize(Sum::make()->label('Total billed')),
                ...($balanceColumn ? [
                    Tables\Columns\TextColumn::make($balanceColumn)
                        ->label('Balance')
                        ->money('usd')
                        ->summarize(Sum::make()->label('Balance due')),
                ] : []),
                Tables\Columns\TextColumn::make('aging')
                    ->label('Aging')
                    ->state(function (Invoice $record) {
                        $balance = $record->balance ?? $record->balance_due ?? 0;
                        if (!$record->due_date || $balance <= 0) {
                            return 'Current';
                        }
                        $days = Carbon::parse($record->due_date)->diffInDays(now(), false);
                        return $days <= 0 ? 'Current' : $days . 'd overdue';
                    })
                    ->icon(function (Invoice $record) {
                        $balance = $record->balance ?? $record->balance_due ?? 0;
                        return ($balance > 0 && $record->due_date && Carbon::parse($record->due_date)->isPast()) ? 'heroicon-o-clock' : null;
                    })
                    ->color(function (Invoice $record) {
                        $balance = $record->balance ?? $record->balance_due ?? 0;
                        return ($balance > 0 && $record->due_date && Carbon::parse($record->due_date)->isPast()) ? 'danger' : 'gray';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'partial' => 'Partial',
                    'paid' => 'Paid',
                    'void' => 'Void',
                ]),
                Tables\Filters\Filter::make('overdue_1_30')
                    ->label('Overdue 1-30d')
                    ->query(fn ($query) => $query
                        ->whereRaw(($balanceColumn ?? 'balance') . ' > 0')
                        ->whereBetween('due_date', [now()->subDays(30), now()->subDay()])),
                Tables\Filters\Filter::make('overdue_31_60')
                    ->label('Overdue 31-60d')
                    ->query(fn ($query) => $query
                        ->whereRaw(($balanceColumn ?? 'balance') . ' > 0')
                        ->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])),
                Tables\Filters\Filter::make('overdue_60')
                    ->label('Overdue 60d+')
                    ->query(fn ($query) => $query
                        ->whereRaw(($balanceColumn ?? 'balance') . ' > 0')
                        ->whereDate('due_date', '<', now()->subDays(60))),
            ])
            ->emptyStateHeading('No invoices yet')
            ->emptyStateDescription('Create invoices to track billing and payments.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Invoice $record) => route('admin.documents.invoices.pdf', ['invoice' => $record->id, 'template' => 'invoice-model']))
                    ->openUrlInNewTab(),
                Actions\Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Invoice PDF Preview')
                    ->modalContent(fn (Invoice $record) => view('documents.preview', [
                        'url' => route('admin.documents.invoices.pdf', ['invoice' => $record->id, 'template' => 'invoice-model']),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
                Actions\Action::make('adjust_payments')
                    ->label('Adjust payments')
                    ->icon('heroicon-o-wrench')
                    ->url(fn (Invoice $record) => route('filament.admin.resources.invoices.edit', $record) . '#payments')
                    ->openUrlInNewTab(),
                Actions\Action::make('apply_credit')
                    ->label('Apply credit')
                    ->icon('heroicon-o-arrow-down-on-square-stack')
                    ->color('success')
                    ->visible(fn (Invoice $record) => CreditBalance::where('entity_type', 'client')->where('entity_id', $record->client_id)->where('remaining', '>', 0)->exists())
                    ->form(function (Invoice $record) {
                        $credits = CreditBalance::where('entity_type', 'client')
                            ->where('entity_id', $record->client_id)
                            ->where('remaining', '>', 0)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => 'Credit #' . $c->id . ' - $' . number_format($c->remaining, 2) . ($c->expires_at ? ' (exp ' . $c->expires_at->format('Y-m-d') . ')' : ''),
                            ]);

                        $balance = max($record->balance ?? $record->balance_due ?? ($record->total - $record->payments()->sum('amount')), 0);
                        return [
                            Forms\Components\Select::make('credit_id')
                                ->label('Credit')
                                ->options($credits)
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->numeric()
                                ->required()
                                ->default(fn () => $balance)
                                ->helperText('Max: remaining credit and invoice balance'),
                        ];
                    })
                    ->action(function (array $data, Invoice $record) {
                        $credit = CreditBalance::find($data['credit_id'] ?? null);
                        if (!$credit || $credit->remaining <= 0) {
                            Notification::make()->title('Credit not available')->warning()->send();
                            return;
                        }
                        $balance = max($record->balance ?? $record->balance_due ?? ($record->total - $record->payments()->sum('amount')), 0);
                        $apply = min($balance, $credit->remaining, $data['amount'] ?? $credit->remaining);
                        if ($apply <= 0) {
                            Notification::make()->title('Nothing to apply')->warning()->send();
                            return;
                        }
                        InvoicePayment::create([
                            'invoice_id' => $record->id,
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
                    ->color('primary')
                    ->form([
                        DatePicker::make('paid_at')->default(now())->required(),
                        TextInput::make('amount')->numeric()->required(),
                        TextInput::make('method')->label('Method')->placeholder('ACH, check, card'),
                        TextInput::make('reference')->label('Reference'),
                        Forms\Components\Select::make('overpay_handling')
                            ->label('If amount exceeds balance')
                            ->options([
                                'cap' => 'Cap to balance',
                                'credit' => 'Create client credit',
                                'refund' => 'Record refund note',
                            ])
                            ->default('cap'),
                        Forms\Components\Textarea::make('note')->label('Notes'),
                    ])
                    ->action(function (array $data, Invoice $record) {
                        $outstanding = max($record->balance ?? $record->balance_due ?? ($record->total - $record->payments()->sum('amount')), 0);
                        if ($outstanding <= 0) {
                            Notification::make()->title('Nothing to pay')->warning()->send();
                            return;
                        }
                        $overpayHandling = $data['overpay_handling'] ?? 'cap';
                        $requested = $data['amount'];
                        $payAmount = $overpayHandling === 'cap' ? min($requested, $outstanding) : min($requested, max($outstanding, 0));
                        InvoicePayment::create([
                            'invoice_id' => $record->id,
                            'paid_at' => $data['paid_at'],
                            'amount' => $payAmount,
                            'method' => $data['method'] ?? null,
                            'reference' => $data['reference'] ?? null,
                        ]);
                        $record->refreshTotals();
                        $extra = $requested - $payAmount;
                        if ($extra > 0 && $overpayHandling === 'credit') {
                            CreditBalance::create([
                                'entity_type' => 'client',
                                'entity_id' => $record->client_id,
                                'source_type' => 'invoice',
                                'source_id' => $record->id,
                                'amount' => $extra,
                                'remaining' => $extra,
                                'reason' => 'overpayment',
                            ]);
                            Notification::make()->title('Overpayment saved as client credit')->info()->send();
                        } elseif ($extra > 0 && $overpayHandling === 'refund') {
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
                    ->action(function (Invoice $record) {
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            PaymentsRelationManager::class,
            \App\Filament\Resources\InvoiceResource\RelationManagers\PaymentsInlineRelationManager::class,
        ];
    }
}
