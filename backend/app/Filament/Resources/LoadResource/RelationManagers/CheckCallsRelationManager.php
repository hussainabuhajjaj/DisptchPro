<?php

namespace App\Filament\Resources\LoadResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Events\TmsMapUpdated;
use App\Models\Load;
use Carbon\Carbon;
use App\Notifications\LoadAlertNotification;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;

class CheckCallsRelationManager extends RelationManager
{
    protected static string $relationship = 'checkCalls';

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Event')
                ->options([
                    'dispatched' => 'Dispatched',
                    'en_route' => 'En route',
                    'arrived_pickup' => 'Arrived pickup',
                    'loaded' => 'Loaded',
                    'arrived_delivery' => 'Arrived delivery',
                    'unloaded' => 'Unloaded',
                    'delayed' => 'Delayed',
                    'issue' => 'Issue',
                    'check_call' => 'Check call',
                ]),
            Forms\Components\DateTimePicker::make('reported_at')->default(now()),
            Forms\Components\Textarea::make('note'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('reported_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Event')->badge(),
                Tables\Columns\TextColumn::make('note')->limit(40),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
            ])
            ->headerActions([
               CreateAction::make()
                    ->using(function ($data, RelationManager $livewire) {
                        $load = $livewire->getOwnerRecord();
                        $call = $load->checkCalls()->create([
                            'status' => $data['status'] ?? null,
                            'note' => $data['note'] ?? null,
                            'reported_at' => $data['reported_at'] ?? now(),
                            'user_id' => Auth::id(),
                        ]);
                        $this->applyStatusTransition($load, $call->status);
                        Cache::forget('tms-map-data');
                        broadcast(new TmsMapUpdated());
                        $this->notifyIfNeeded($load, $call->status);
                        return $call;
                    }),
            ])
            ->actions([
                Action::make('edit')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Event')
                            ->options([
                                'dispatched' => 'Dispatched',
                                'en_route' => 'En route',
                                'arrived_pickup' => 'Arrived pickup',
                                'loaded' => 'Loaded',
                                'arrived_delivery' => 'Arrived delivery',
                                'unloaded' => 'Unloaded',
                                'delayed' => 'Delayed',
                                'issue' => 'Issue',
                                'check_call' => 'Check call',
                            ]),
                        Forms\Components\DateTimePicker::make('reported_at'),
                        Forms\Components\Textarea::make('note'),
                    ])
                    ->action(fn ($record, $data) => $record->update($data)),
           DeleteAction::make(),
            ]);
    }

    protected function applyStatusTransition(Load $load, ?string $event): void
    {
        if (!$event) return;
        $order = ['draft', 'posted', 'assigned', 'in_transit', 'delivered', 'completed'];
        $map = [
            'dispatched' => 'posted',
            'en_route' => 'in_transit',
            'arrived_pickup' => 'in_transit',
            'loaded' => 'in_transit',
            'arrived_delivery' => 'delivered',
            'unloaded' => 'delivered',
        ];
        $newStatus = $map[$event] ?? $load->status;
        $currentIndex = array_search($load->status, $order);
        $newIndex = array_search($newStatus, $order);

        // Stamp actuals
        if (in_array($event, ['arrived_pickup', 'loaded']) && is_null($load->pickup_actual_at)) {
            $load->pickup_actual_at = Carbon::now();
        }
        if (in_array($event, ['arrived_delivery', 'unloaded']) && is_null($load->delivery_actual_at)) {
            $load->delivery_actual_at = Carbon::now();
        }

        if ($newIndex !== false && $currentIndex !== false && $newIndex >= $currentIndex && $newStatus !== $load->status) {
            $load->status = $newStatus;
        }

        $load->saveQuietly();
    }

    protected function notifyIfNeeded(Load $load, string $status): void
    {
        if (!in_array($status, ['issue', 'delayed'])) {
            return;
        }
        $dispatcher = $load->dispatcher;
        if (!$dispatcher) return;
        $dispatcher->notify(new LoadAlertNotification(
            "Load {$load->load_number} flagged: {$status}",
            [
                'load_id' => $load->id,
                'load_number' => $load->load_number,
                'status' => $status,
                'url' => route('filament.admin.resources.loads.edit', $load),
            ]
        ));

        $webhook = config('services.slack.webhook_url') ?? env('SLA_SLACK_WEBHOOK');
        if ($webhook) {
            \Illuminate\Support\Facades\Http::post($webhook, [
                'text' => "Load {$load->load_number} flagged: {$status}",
            ]);
        }
    }
}
