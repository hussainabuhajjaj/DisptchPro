<?php

namespace App\Console\Commands;

use App\Models\Carrier;
use App\Models\Client;
use App\Models\CreditBalance;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Settlement;
use App\Models\SettlementPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class AutoApplyCredits extends Command
{
    protected $signature = 'credits:auto-apply';
    protected $description = 'Auto-apply available credits to open invoices/settlements and expire stale credits.';

    public function handle(): int
    {
        $this->expireCredits();
        $this->notifyExpiringCredits();
        $this->applyClientCredits();
        $this->applyCarrierCredits();
        $this->info('Credits auto-apply finished.');
        return self::SUCCESS;
    }

    protected function expireCredits(): void
    {
        $expired = CreditBalance::whereNotNull('expires_at')
            ->whereDate('expires_at', '<', now()->toDateString())
            ->where('remaining', '>', 0)
            ->update(['remaining' => 0]);

        if ($expired) {
            $this->info("Expired {$expired} credits.");
        }
    }

    protected function notifyExpiringCredits(): void
    {
        $window = now()->addDays(7)->toDateString();
        $expiring = CreditBalance::where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', $window)
            ->get();

        if ($expiring->isEmpty()) {
            return;
        }

        $summary = $expiring->map(function ($c) {
            $exp = $c->expires_at ? $c->expires_at->format('Y-m-d') : 'n/a';
            return "{$c->entity_type} #{$c->entity_id} - credit #{$c->id} \${$c->remaining} exp {$exp}";
        })->implode("\n");

        $webhook = config('services.slack.webhook_url') ?? env('SLA_SLACK_WEBHOOK');
        if ($webhook) {
            Http::post($webhook, [
                'text' => "Credits expiring within 7 days:\n" . $summary,
            ]);
        }

        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new ExpiringCreditsNotification($expiring));
        }

        $this->info('Notified expiring credits.');
    }

    protected function applyClientCredits(): void
    {
        $clients = Client::where('auto_apply_credit', true)->pluck('id');
        if ($clients->isEmpty()) {
            return;
        }

        $credits = CreditBalance::where('entity_type', 'client')
            ->whereIn('entity_id', $clients)
            ->where('remaining', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->orderBy('expires_at')
            ->orderBy('id')
            ->get()
            ->groupBy('entity_id');

        foreach ($credits as $clientId => $creditGroup) {
            $invoices = Invoice::where('client_id', $clientId)
                ->where(function ($q) {
                    $q->where('status', '!=', 'paid')->orWhereNull('status');
                })
                ->get();

            foreach ($invoices as $invoice) {
                $balance = $this->invoiceBalance($invoice);
                if ($balance <= 0) {
                    continue;
                }

                foreach ($creditGroup as $credit) {
                    if ($credit->remaining <= 0) {
                        continue;
                    }

                    $apply = min($balance, $credit->remaining);
                    if ($apply <= 0) {
                        continue;
                    }

                    DB::transaction(function () use ($invoice, $credit, $apply, &$balance) {
                        InvoicePayment::create([
                            'invoice_id' => $invoice->id,
                            'paid_at' => now(),
                            'amount' => $apply,
                            'method' => 'credit',
                            'reference' => 'Auto credit #' . $credit->id,
                        ]);
                        $credit->decrement('remaining', $apply);
                        $invoice->refreshTotals();
                        $balance -= $apply;
                    });

                    if ($balance <= 0) {
                        break;
                    }
                }
            }
        }
    }

    protected function applyCarrierCredits(): void
    {
        $carriers = Carrier::where('auto_apply_credit', true)->pluck('id');
        if ($carriers->isEmpty()) {
            return;
        }

        $credits = CreditBalance::where('entity_type', 'carrier')
            ->whereIn('entity_id', $carriers)
            ->where('remaining', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->orderBy('expires_at')
            ->orderBy('id')
            ->get()
            ->groupBy('entity_id');

        foreach ($credits as $carrierId => $creditGroup) {
            $settlements = Settlement::where('settlement_type', 'carrier')
                ->where('entity_id', $carrierId)
                ->where(function ($q) {
                    $q->where('status', '!=', 'paid')->orWhereNull('status');
                })
                ->get();

            foreach ($settlements as $settlement) {
                $balance = $this->settlementBalance($settlement);
                if ($balance <= 0) {
                    continue;
                }

                foreach ($creditGroup as $credit) {
                    if ($credit->remaining <= 0) {
                        continue;
                    }

                    $apply = min($balance, $credit->remaining);
                    if ($apply <= 0) {
                        continue;
                    }

                    DB::transaction(function () use ($settlement, $credit, $apply, &$balance) {
                        SettlementPayment::create([
                            'settlement_id' => $settlement->id,
                            'paid_at' => now(),
                            'amount' => $apply,
                            'method' => 'credit',
                            'reference' => 'Auto credit #' . $credit->id,
                        ]);
                        $credit->decrement('remaining', $apply);
                        $settlement->refreshTotals();
                        $balance -= $apply;
                    });

                    if ($balance <= 0) {
                        break;
                    }
                }
            }
        }
    }

    protected function invoiceBalance(Invoice $invoice): float
    {
        $balance = $invoice->balance ?? $invoice->balance_due ?? ($invoice->total - $invoice->payments()->sum('amount'));
        return max($balance, 0);
    }

    protected function settlementBalance(Settlement $settlement): float
    {
        $balance = $settlement->balance ?? ($settlement->total - $settlement->payments()->sum('amount'));
        return max($balance, 0);
    }
}
