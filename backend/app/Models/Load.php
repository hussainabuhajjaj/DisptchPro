<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Settlement;
use App\Models\SettlementItem;
use Illuminate\Support\Arr;
use App\Models\Accessorial;

class Load extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'load_number',
        'client_id',
        'carrier_id',
        'driver_id',
        'truck_id',
        'trailer_id',
        'dispatcher_id',
        'trailer_type',
        'rate_to_client',
        'rate_to_carrier',
        'fuel_surcharge',
        'accessorial_charges',
        'distance_miles',
        'estimated_distance',
        'commodity',
        'weight',
        'pieces',
        'equipment_requirements',
        'reference_numbers',
        'status',
        'lifecycle_status',
        'internal_notes',
        'driver_notes',
        'hazmat_flag',
        'hazmat_details',
        'weight_axle_limits',
        'route_polyline',
        'route_distance_km',
        'route_duration_hr',
        'last_lat',
        'last_lng',
        'last_location_at',
        'last_eta_minutes',
        'rate_confirmed_at',
    ];

    protected $casts = [
        'accessorial_charges' => 'array',
        'reference_numbers' => 'array',
        'weight_axle_limits' => 'array',
        'route_polyline' => 'array',
        'route_distance_km' => 'decimal:2',
        'route_duration_hr' => 'decimal:2',
        'pickup_actual_at' => 'datetime',
        'delivery_actual_at' => 'datetime',
        'last_location_at' => 'datetime',
        'hazmat_flag' => 'boolean',
        'rate_confirmed_at' => 'datetime',
    ];

    public function accessorials()
    {
        return $this->hasMany(Accessorial::class);
    }

    protected static function booted()
    {
        static::saved(function (self $load) {
            try {
                event(new \App\Events\TmsMapUpdated('load', $load->id));
            } catch (\Throwable $e) {
                // ignore broadcast failures
            }

            // Keep detention accessorials synced into invoices/settlements
            $load->syncDetentionBilling();
        });

        static::deleted(function (self $load) {
            try {
                event(new \App\Events\TmsMapUpdated('load', $load->id));
            } catch (\Throwable $e) {
                // ignore broadcast failures
            }
        });
    }

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function client() { return $this->belongsTo(Client::class); }
    public function carrier() { return $this->belongsTo(Carrier::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function truck() { return $this->belongsTo(Truck::class); }
    public function trailer() { return $this->belongsTo(Trailer::class); }
    public function dispatcher() { return $this->belongsTo(User::class, 'dispatcher_id'); }
    public function stops() { return $this->hasMany(LoadStop::class)->orderBy('sequence'); }
    public function invoices() { return $this->belongsToMany(Invoice::class)->withPivot('line_amount', 'description')->withTimestamps(); }
    public function documents() { return $this->morphMany(Document::class, 'documentable'); }
    public function checkCalls() { return $this->hasMany(CheckCall::class); }
    public function locations() { return $this->hasMany(LoadLocation::class); }
    public function pods() { return $this->hasMany(Pod::class); }
    public function accessorialItems() { return $this->hasMany(Accessorial::class); }

    public function getRevenueAttribute(): float
    {
        return (float) $this->rate_to_client + (float) $this->fuel_surcharge + $this->accessorialSum('revenue');
    }

    public function getCostAttribute(): float
    {
        return (float) $this->rate_to_carrier + $this->accessorialSum('cost');
    }

    public function getProfitAttribute(): float
    {
        return $this->revenue - $this->cost;
    }

    public function getMarginAttribute(): float
    {
        return $this->revenue > 0 ? round(($this->profit / $this->revenue) * 100, 2) : 0;
    }

    public function getDeliveryDateAttribute(): ?string
    {
        $date = $this->stops
            ->where('type', 'delivery')
            ->map(fn ($stop) => optional($stop->date_from)->toDateString())
            ->filter()
            ->max();

        return $date ?: null;
    }

    public function getRouteStatusAttribute(): string
    {
        $end = $this->delivery_date;
        if (!$end) {
            return 'on_time';
        }
        $today = now()->toDateString();
        if ($end < $today && !in_array($this->status, ['delivered', 'completed'])) {
            return 'late';
        }
        if (!in_array($this->status, ['delivered', 'completed']) && $end <= now()->addHours(6)->toDateString()) {
            return 'at_risk';
        }
        return 'on_time';
    }

    protected function accessorialSum(string $key): float
    {
        $items = $this->accessorial_charges ?? [];
        return collect($items)->sum(fn ($i) => (float) ($i[$key] ?? 0));
    }

    public function syncDetentionBilling(): void
    {
        $accessorials = $this->accessorial_charges ?? [];
        $detentions = collect($accessorials)->filter(function ($acc, $code) {
            $label = strtolower($acc['label'] ?? $code);
            return str_contains($label, 'detention') || str_contains($label, 'layover');
        });

        if ($detentions->isEmpty()) {
            return;
        }

        $amount = $detentions->sum(fn ($acc) => (float) Arr::get($acc, 'revenue', 0));

        // Invoice items
        if ($this->id) {
            $invoices = Invoice::where('load_id', $this->id)->get();
            foreach ($invoices as $invoice) {
                InvoiceItem::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'description' => 'Detention/Layover'],
                    ['quantity' => 1, 'rate' => $amount, 'amount' => $amount]
                );
                $invoice->refreshTotals();
            }

            $settlements = Settlement::where('load_id', $this->id)->get();
            foreach ($settlements as $settlement) {
                SettlementItem::updateOrCreate(
                    ['settlement_id' => $settlement->id, 'description' => 'Detention/Layover'],
                    ['quantity' => 1, 'rate' => $amount, 'amount' => $amount]
                );
                $settlement->refreshTotals();
            }
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
