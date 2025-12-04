<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'client_id',
        'invoice_number',
        'invoice_date',
        'issue_date',
        'due_date',
        'total',
        'balance',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function loadRelation()
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function refreshTotals(): void
    {
        $total = $this->items()->sum('amount');
        $paid = $this->payments()->sum('amount');
        $this->total = $total;

        $balanceValue = max($total - $paid, 0);

        if (Schema::hasColumn($this->getTable(), 'balance')) {
            $this->balance = $balanceValue;
        } elseif (Schema::hasColumn($this->getTable(), 'balance_due')) {
            $this->balance_due = $balanceValue;
        }

        $statusFieldValue = Schema::hasColumn($this->getTable(), 'balance') ? $this->balance : ($this->balance_due ?? null);
        $this->status = ($statusFieldValue !== null && $statusFieldValue <= 0)
            ? 'paid'
            : (($statusFieldValue !== null && $statusFieldValue < $total) ? 'partial' : $this->status);

        $this->saveQuietly();
    }
}
