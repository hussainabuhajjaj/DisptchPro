<?php

namespace App\Filament\Resources\SettlementResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema as FormSchema;
use Filament\Notifications\Notification;
use App\Models\CreditBalance;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Payments';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    DatePicker::make('paid_at')->label('Paid at')->required(),
                    TextInput::make('amount')->numeric()->required(),
                    TextInput::make('method')->label('Method'),
                    TextInput::make('reference')->label('Reference'),
                    \Filament\Forms\Components\Select::make('overpay_handling')
                        ->label('If amount exceeds balance')
                        ->options([
                            'cap' => 'Cap to balance',
                            'credit' => 'Create credit',
                            'refund' => 'Record refund note',
                        ])
                        ->default('cap'),
                    \Filament\Forms\Components\Textarea::make('note')->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')->date(),
                Tables\Columns\TextColumn::make('amount')->money('usd'),
                Tables\Columns\TextColumn::make('method'),
                Tables\Columns\TextColumn::make('reference'),
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Add')
                    ->icon('heroicon-o-plus')
                    ->form($this->form(app(FormSchema::class))->getComponents())
                    ->action(function (array $data) {
                        $settlement = $this->getOwnerRecord();
                        $outstanding = $settlement->balance ?? ($settlement->total - $settlement->payments()->sum('amount'));
                        if ($outstanding <= 0) {
                            Notification::make()->title('Nothing to pay')->warning()->send();
                            return null;
                        }
                        $requested = $data['amount'];
                        $handling = $data['overpay_handling'] ?? 'cap';
                        $data['amount'] = $handling === 'cap' ? min($requested, $outstanding) : min($requested, max($outstanding, 0));
                        $record = $settlement->payments()->create($data);
                        $settlement->refreshTotals();
                        $extra = $requested - $data['amount'];
                        if ($extra > 0 && $handling === 'credit') {
                            CreditBalance::create([
                                'entity_type' => $settlement->settlement_type,
                                'entity_id' => $settlement->entity_id,
                                'source_type' => 'settlement',
                                'source_id' => $settlement->id,
                                'amount' => $extra,
                                'remaining' => $extra,
                                'reason' => 'overpayment',
                            ]);
                            Notification::make()->title('Overpayment saved as credit')->info()->send();
                        } elseif ($extra > 0 && $handling === 'refund') {
                            Notification::make()->title('Overpayment noted as refund – adjust manually')->info()->send();
                        } elseif ($data['amount'] < $requested) {
                            Notification::make()->title('Payment capped to outstanding balance')->info()->send();
                        }
                        return $record;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function ($record, array $data) {
                        $record->update($data);
                        $this->getOwnerRecord()->refreshTotals();
                        return $record;
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->refreshTotals();
                    }),
            ])
            ->paginated(false);
    }
}
