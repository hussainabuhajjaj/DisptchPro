<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoadResource\Pages;
use App\Filament\Resources\LoadResource\RelationManagers\StopsRelationManager;
use App\Models\Client;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Load;
use App\Models\Document;
use App\Models\CheckCall;
use App\Models\Trailer;
use App\Models\Truck;
use App\Models\User;
use App\Models\BolTemplate;
use App\Models\Accessorial;
use Filament\Actions;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use BackedEnum;
use UnitEnum;
use Illuminate\Support\Facades\Cache;
use App\Events\TmsMapUpdated;
use App\Notifications\LoadAlertNotification;
use Filament\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\LoadResource\RelationManagers\AccessorialsRelationManager;
use App\Filament\Resources\LoadResource\RelationManagers\PodsRelationManager;

class LoadResource extends Resource
{
    protected static ?string $model = Load::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function getNavigationBadge(): ?string
    {
        $unassigned = Load::query()
            ->where(function ($q) {
                $q->whereNull('carrier_id')->orWhereNull('driver_id');
            })
            ->count();

        return $unassigned > 0 ? (string) $unassigned : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Load details')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('load_number')->required(),
                        Forms\Components\Select::make('status')->options([
                            'draft' => 'Draft',
                            'posted' => 'Posted',
                            'assigned' => 'Assigned',
                            'in_transit' => 'In Transit',
                            'delivered' => 'Delivered',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])->default('draft')
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $allowed = self::allowedStatusChange($get('status'), $state, $get('pickup_actual_at'), $get('delivery_actual_at'));
                                if (!$allowed) {
                                    Notification::make()
                                        ->title('Invalid status transition')
                                        ->danger()
                                        ->body('Cannot set status to ' . $state . ' before pickup/delivery actuals are set.')
                                        ->send();
                                    // revert
                                    $set('status', $get('status'));
                                }
                            }),
                        Forms\Components\Select::make('client_id')->label('Client')->options(Client::query()->pluck('name', 'id'))->required()->searchable(),
                        Forms\Components\Select::make('carrier_id')->label('Carrier')->options(Carrier::query()->pluck('name', 'id'))->searchable(),
                        Forms\Components\Select::make('dispatcher_id')->label('Dispatcher')->options(\App\Models\User::query()->pluck('name', 'id'))->searchable(),
                        Forms\Components\Select::make('driver_id')->label('Driver')->options(Driver::query()->pluck('name', 'id'))->searchable(),
                        Forms\Components\Select::make('bol_template_id')
                            ->label('BOL template')
                            ->options(fn () => BolTemplate::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('bol_path')->label('Generated BOL path')->disabled(),
                        Forms\Components\DateTimePicker::make('bol_generated_at')->label('BOL generated at')->disabled(),
                        Forms\Components\Select::make('truck_id')->label('Truck')->options(Truck::query()->pluck('unit_number', 'id'))->searchable(),
                        Forms\Components\Select::make('trailer_id')->label('Trailer')->options(Trailer::query()->pluck('trailer_number', 'id'))->searchable(),
                        Forms\Components\TextInput::make('trailer_type'),
                        Forms\Components\TextInput::make('equipment_requirements'),
                        Forms\Components\TextInput::make('distance_miles')->numeric(),
                        Forms\Components\TextInput::make('estimated_distance')->numeric(),
                        Forms\Components\TextInput::make('commodity'),
                        Forms\Components\TextInput::make('weight')->numeric(),
                        Forms\Components\TextInput::make('pieces')->numeric(),
                        Forms\Components\KeyValue::make('reference_numbers')->label('Reference numbers (JSON)')->columnSpan(2),
                        Forms\Components\DateTimePicker::make('pickup_actual_at')->label('Pickup actual')->disabled(),
                        Forms\Components\DateTimePicker::make('delivery_actual_at')->label('Delivery actual')->disabled(),
                    ]),
                ]),
            Section::make('Financials')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('rate_to_client')->numeric(),
                        Forms\Components\TextInput::make('rate_to_carrier')->numeric(),
                        Forms\Components\TextInput::make('fuel_surcharge')->numeric(),
                    ]),
                        Forms\Components\KeyValue::make('accessorial_charges')->label('Accessorial charges (JSON)'),
                    ]),
            Section::make('Tender lifecycle')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\DateTimePicker::make('tendered_at'),
                        Forms\Components\DateTimePicker::make('carrier_accepted_at'),
                        Forms\Components\DateTimePicker::make('carrier_rejected_at'),
                        Forms\Components\DateTimePicker::make('driver_acknowledged_at'),
                    ]),
                ]),
            Section::make('Notes & documents')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Textarea::make('internal_notes'),
                        Forms\Components\Textarea::make('driver_notes'),
                    ]),
                    Grid::make(2)->schema([
                        FileUpload::make('rate_con_path')->label('Rate Confirmation')->directory('load-docs'),
                        FileUpload::make('pod_path')->label('POD')->directory('load-docs'),
                    ]),
                    Grid::make(3)->schema([
                        Forms\Components\Textarea::make('route_polyline')
                            ->label('Route polyline (GeoJSON coords)')
                            ->columnSpan(2)
                            ->helperText('Optional: prefill with coordinates array to skip fetching.'),
                        Forms\Components\TextInput::make('route_distance_km')->label('Route distance (km)')->numeric(),
                        Forms\Components\TextInput::make('route_duration_hr')->label('Route duration (hr)')->numeric(),
                    ])->columns(3),
                    Forms\Components\Placeholder::make('ai_plan_tip')
                        ->label('Planning assistant')
                        ->content('Add mid-route stops like fuel/break/inspection to improve ETA accuracy. After saving, map will auto-cache routes and SLA.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $recordActions = [
            Actions\EditAction::make(),
            Actions\Action::make('pdf')
                ->label('PDF')
                  ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn (Load $record) => route('admin.documents.loads.pdf', ['load' => $record->id, 'type' => 'general', 'template' => 'clean']))
                ->openUrlInNewTab(),
            Actions\Action::make('generateBol')
                ->label('Generate BOL')
                ->icon('heroicon-o-document-text')
                ->color('secondary')
                ->requiresConfirmation()
                ->action(function (Load $record) {
                    $url = route('admin.documents.loads.pdf', [
                        'load' => $record->id,
                        'type' => 'bol',
                        'template' => 'bol',
                    ]);
                    $record->update([
                        'bol_generated_at' => now(),
                        'bol_path' => $url,
                    ]);
                    return redirect()->away($url);
                }),
            Actions\Action::make('tender')
                ->label('Tender')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (Load $record) => !$record->tendered_at)
                ->action(fn (Load $record) => $record->update(['tendered_at' => now(), 'status' => 'posted'])),
            Actions\Action::make('carrierAccept')
                ->label('Carrier Accept')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Load $record) => !$record->carrier_accepted_at)
                ->action(fn (Load $record) => $record->update(['carrier_accepted_at' => now(), 'status' => 'assigned'])),
            Actions\Action::make('carrierReject')
                ->label('Carrier Reject')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (Load $record) => !$record->carrier_rejected_at)
                ->action(fn (Load $record) => $record->update(['carrier_rejected_at' => now(), 'status' => 'posted'])),
            Actions\Action::make('driverAck')
                ->label('Driver Ack')
                ->icon('heroicon-o-user-check')
                ->visible(fn (Load $record) => !$record->driver_acknowledged_at)
                ->action(fn (Load $record) => $record->update(['driver_acknowledged_at' => now()])),
            Actions\Action::make('previewPdf')
                ->label('Preview')
                  ->icon('heroicon-o-eye')
                ->color('primary')
                ->modalHeading('PDF Preview')
                ->modalContent(fn (Load $record) => view('documents.preview', [
                    'url' => route('admin.documents.loads.pdf', [
                        'load' => $record->id,
                        'type' => 'general',
                        'template' => 'clean',
                    ]),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelAction(false),
            Actions\Action::make('pdfTemplate')
                ->label('PDF (custom)')
                  ->icon('heroicon-o-document')
                ->color('primary')
                ->schema([
                    Forms\Components\Select::make('template')
                        ->options([
                            'clean' => 'Clean',
                            'rate-con' => 'Rate Con',
                            'invoice' => 'Invoice',
                            'bol' => 'BOL',
                            'pod' => 'POD',
                            'modern' => 'Modern',
                        ])
                        ->default(config('app.doc_template', 'clean'))
                        ->required(),
                    Forms\Components\Select::make('type')
                        ->options([
                            'general' => 'General',
                            'rate-con' => 'Rate Con',
                            'invoice' => 'Invoice',
                            'bol' => 'BOL',
                            'pod' => 'POD',
                        ])
                        ->default(config('app.doc_type', 'general'))
                        ->required(),
                    Forms\Components\TextInput::make('invoice_number')->label('Invoice #')->visible(fn ($get) => $get('template') === 'invoice'),
                    Forms\Components\DatePicker::make('due_date')->label('Due date')->visible(fn ($get) => $get('template') === 'invoice'),
                    Forms\Components\TextInput::make('payment_terms')->label('Payment terms')->default('Net 30')->visible(fn ($get) => $get('template') === 'invoice'),
                    Forms\Components\TextInput::make('broker_ref')->label('Broker ref')->visible(fn ($get) => in_array($get('template'), ['rate-con', 'bol', 'pod'])),
                    Forms\Components\TextInput::make('equipment')->label('Equipment')->visible(fn ($get) => in_array($get('template'), ['rate-con', 'bol', 'pod'])),
                    Forms\Components\TextInput::make('contact_name')->label('Contact name')->visible(fn ($get) => in_array($get('template'), ['rate-con', 'bol', 'pod', 'invoice'])),
                    Forms\Components\TextInput::make('contact_phone')->label('Contact phone')->visible(fn ($get) => in_array($get('template'), ['rate-con', 'bol', 'pod', 'invoice'])),
                    Forms\Components\TextInput::make('recipient_name')->label('Recipient name')->visible(fn ($get) => $get('template') === 'pod'),
                    Forms\Components\DateTimePicker::make('delivery_date')->label('Delivered at')->visible(fn ($get) => $get('template') === 'pod'),
                    Forms\Components\Toggle::make('show_signatures')->label('Show signatures')->default(false)->visible(fn ($get) => in_array($get('template'), ['rate-con', 'bol', 'pod'])),
                    Forms\Components\TextInput::make('brand_logo')->label('Brand logo URL'),
                    Forms\Components\ColorPicker::make('brand_color')->label('Brand color'),
                    Forms\Components\TextInput::make('brand_font')->label('Brand font')->placeholder('e.g. Arial, sans-serif'),
                    Forms\Components\TextInput::make('company_name')->label('Company name'),
                    Forms\Components\Textarea::make('company_address')->label('Company address'),
                    Forms\Components\Toggle::make('force')->label('Regenerate / overwrite cached')->default(false),
                ])
                ->action(function (array $data, Load $record) {
                    return redirect()->route('admin.documents.loads.pdf', [
                        'load' => $record->id,
                        'type' => $data['type'],
                        'template' => $data['template'],
                        'invoice_number' => $data['invoice_number'] ?? null,
                        'due_date' => $data['due_date'] ?? null,
                        'payment_terms' => $data['payment_terms'] ?? null,
                        'broker_ref' => $data['broker_ref'] ?? null,
                        'equipment' => $data['equipment'] ?? null,
                        'contact_name' => $data['contact_name'] ?? null,
                        'contact_phone' => $data['contact_phone'] ?? null,
                        'show_signatures' => $data['show_signatures'] ?? null,
                        'brand_logo' => $data['brand_logo'] ?? null,
                        'brand_color' => $data['brand_color'] ?? null,
                        'brand_font' => $data['brand_font'] ?? null,
                        'company_name' => $data['company_name'] ?? null,
                        'company_address' => $data['company_address'] ?? null,
                        'delivery_date' => $data['delivery_date'] ?? null,
                        'recipient_name' => $data['recipient_name'] ?? null,
                        'force' => $data['force'] ?? null,
                    ]);
                }),
            Actions\Action::make('regenerate_pdf')
                ->label('Regenerate PDF')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->url(fn (Load $record) => route('admin.documents.loads.pdf', [
                    'load' => $record->id,
                    'type' => 'general',
                    'template' => 'clean',
                    'force' => 1,
                ]))
                ->openUrlInNewTab(),
        ];

        if ($mediaAction = self::mediaAction()) {
            $recordActions[] = $mediaAction;
        }

        return $table
            ->defaultSort('id', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('load_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.name')->label('Client')->sortable(),
                Tables\Columns\TextColumn::make('carrier.name')->label('Carrier'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'posted',
                        'info' => 'assigned',
                        'success' => 'delivered',
                        'primary' => 'in_transit',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('assignment')
                    ->label('Assignment')
                    ->state(fn ($record) => ($record->carrier_id && $record->driver_id) ? 'Assigned' : 'Needs assignment')
                    ->badge()
                    ->color(fn ($record) => ($record->carrier_id && $record->driver_id) ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('route_status')
                    ->label('SLA')
                    ->badge()
                    ->color(fn (Load $record) => match ($record->route_status) {
                        'late' => 'danger',
                        'at_risk' => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('rpm')
                    ->label('RPM')
                    ->state(fn ($record) => $record->distance_miles ? (($record->rate_to_client + $record->fuel_surcharge) / max($record->distance_miles, 1)) : null)
                    ->formatStateUsing(fn ($state) => $state ? '$' . number_format($state, 2) . '/mi' : '—')
                    ->color(fn ($state) => $state && $state >= 3 ? 'success' : ($state && $state >= 2 ? 'info' : null)),
                Tables\Columns\TextColumn::make('pickup_actual_at')->label('Pickup actual')->dateTime(),
                Tables\Columns\TextColumn::make('delivery_actual_at')->label('Delivery actual')->dateTime(),
                Tables\Columns\TextColumn::make('rate_to_client')->money('usd')->sortable()->summarize(Sum::make()->label('Total bill')),
                Tables\Columns\TextColumn::make('rate_to_carrier')->money('usd')->sortable()->summarize(Sum::make()->label('Total carrier')),
                Tables\Columns\TextColumn::make('profit')
                    ->label('Profit')
                    ->state(fn (Load $record) => $record->profit)
                    ->money('usd'),
                Tables\Columns\TextColumn::make('margin')->suffix('%'),
                Tables\Columns\TextColumn::make('accessorials_sum')
                    ->label('Accessorials')
                    ->state(fn (Load $record) => $record->accessorials()->sum('amount'))
                    ->money('usd')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('accessorials_approved')
                    ->label('Approved acc.')
                    ->state(fn (Load $record) => $record->accessorials()->where('status', 'approved')->sum('amount'))
                    ->money('usd'),
                Tables\Columns\TextColumn::make('total_with_accessorials')
                    ->label('Billable total')
                    ->state(fn (Load $record) => ($record->rate_to_client ?? 0) + ($record->fuel_surcharge ?? 0) + $record->accessorials()->where('status', 'approved')->sum('amount'))
                    ->money('usd'),
                Tables\Columns\IconColumn::make('pod_flag')
                    ->label('POD')
                    ->boolean()
                    ->state(fn (Load $record) => !is_null($record->pod_id) || !is_null($record->pod_path)),
                Tables\Columns\TextColumn::make('checkCalls.status')
                    ->label('Last event')
                    ->state(fn (Load $record) => optional($record->checkCalls()->latest('reported_at')->first())->status)
                    ->badge()
                    ->sortable(false),
                Tables\Columns\TextColumn::make('checkCalls.reported_at')
                    ->label('Last event time')
                    ->state(fn (Load $record) => optional($record->checkCalls()->latest('reported_at')->first())->reported_at)
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'posted' => 'Posted',
                    'assigned' => 'Assigned',
                    'in_transit' => 'In Transit',
                    'delivered' => 'Delivered',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('dispatcher_id')
                    ->label('Dispatcher')
                    ->options(fn () => User::query()->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned')
                    ->query(fn ($query) => $query
                        ->whereNull('carrier_id')
                        ->orWhereNull('driver_id')),
                Tables\Filters\Filter::make('high_margin')
                    ->label('Margin ≥ 20%')
                    ->query(fn ($query) => $query->where('margin', '>=', 20)),
                Tables\Filters\Filter::make('late_only')
                    ->label('Late')
                    ->query(fn (Builder $query) => $query->whereHas('stops', function ($q) {
                        $q->where('type', 'delivery')->whereDate('date_from', '<', now()->toDateString());
                    })->whereNotIn('status', ['delivered', 'completed'])),
                Tables\Filters\Filter::make('at_risk')
                    ->label('At risk (next 6h)')
                    ->query(fn (Builder $query) => $query->whereHas('stops', function ($q) {
                        $q->where('type', 'delivery')
                            ->whereDate('date_from', '<=', now()->addHours(6)->toDateString());
                    })->whereNotIn('status', ['delivered', 'completed'])),
                Tables\Filters\Filter::make('created_at')->form([
                    Forms\Components\DatePicker::make('from'),
                    Forms\Components\DatePicker::make('until'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                }),
            ])
            ->emptyStateHeading('No loads yet')
            ->emptyStateDescription('Create your first load to start dispatching.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions(               ActionGroup::make($recordActions))
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
                Actions\Action::make('quickCheckCall')
                    ->label('Log check call')
                    ->form([
                        Forms\Components\Select::make('status')
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
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('reported_at')->default(now()),
                        Forms\Components\Textarea::make('note'),
                        Forms\Components\Select::make('record_id')
                            ->label('Load')
                            ->options(fn () => Load::query()->orderByDesc('id')->limit(50)->pluck('load_number', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $load = Load::find($data['record_id']);
                        if (!$load) return;
                        $call = $load->checkCalls()->create([
                            'status' => $data['status'],
                            'note' => $data['note'] ?? null,
                            'reported_at' => $data['reported_at'] ?? now(),
                            'user_id' => auth()->id(),
                        ]);
                        self::applyStatusTransition($load, $call->status);
                        self::notifyIfNeeded($load, $call->status);
                        Cache::forget('tms-map-data');
                        broadcast(new \App\Events\TmsMapUpdated());
                    }),
            ]);
    }

    protected static function applyStatusTransition(Load $load, ?string $event): void
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
        if (in_array($event, ['arrived_pickup', 'loaded']) && is_null($load->pickup_actual_at)) {
            $load->pickup_actual_at = now();
        }
        if (in_array($event, ['arrived_delivery', 'unloaded']) && is_null($load->delivery_actual_at)) {
            $load->delivery_actual_at = now();
        }
        if ($newIndex !== false && $currentIndex !== false && $newIndex >= $currentIndex && $newStatus !== $load->status) {
            $load->status = $newStatus;
        }
        $load->saveQuietly();
    }

    protected static function notifyIfNeeded(Load $load, string $status): void
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

    public static function getRelations(): array
    {
        return [
            StopsRelationManager::class,
            \App\Filament\Resources\LoadResource\RelationManagers\DocumentsRelationManager::class,
            \App\Filament\Resources\LoadResource\RelationManagers\CheckCallsRelationManager::class,
            AccessorialsRelationManager::class,
            PodsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoads::route('/'),
            'create' => Pages\CreateLoad::route('/create'),
            'edit' => Pages\EditLoad::route('/{record}/edit'),
        ];
    }

    protected static function mediaAction(): ?Filament\Actions\Action
    {
        if (!class_exists(\Hugomyb\FilamentMediaAction\Actions\MediaAction::class)) {
            return null;
        }

        return \Hugomyb\FilamentMediaAction\Actions\MediaAction::make('view-media')
            ->label('View media')
            ->icon('heroicon-o-photo')
            ->media(fn (Load $record) => self::resolveMediaUrl($record))
            ->mediaType(\Hugomyb\FilamentMediaAction\Actions\MediaAction::TYPE_PDF)
            ->modalHeading(fn (Load $record) => "Documents for {$record->load_number}")
            ->disabled(fn (Load $record) => !self::resolveMediaUrl($record))
            ->visible(fn () => true);
    }

    protected static function resolveMediaUrl(Load $record): ?string
    {
        $doc = Document::where('documentable_type', Load::class)
            ->where('documentable_id', $record->id)
            ->where('mime_type', 'application/pdf')
            ->latest('uploaded_at')
            ->first();

        if ($doc && $doc->file_path) {
            return Storage::disk('public')->url($doc->file_path);
        }

        return route('admin.documents.loads.pdf', ['load' => $record->id, 'type' => 'general']);
    }
    protected static function allowedStatusChange(?string $from, ?string $to, $pickupActual, $deliveryActual): bool
    {
        $order = ['draft', 'posted', 'assigned', 'in_transit', 'delivered', 'completed', 'cancelled'];
        if (!$to || !$from) return true;
        if ($to === 'cancelled') return true;
        $fromIndex = array_search($from, $order);
        $toIndex = array_search($to, $order);
        if ($fromIndex === false || $toIndex === false) {
            return true;
        }
        if ($toIndex < $fromIndex) {
            return false;
        }
        // require pickup before delivered/completed
        if (in_array($to, ['delivered', 'completed']) && !$pickupActual) {
            return false;
        }
        // require delivery before completed
        if ($to === 'completed' && !$deliveryActual) {
            return false;
        }
        return true;
    }
}
