<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\SlaAlertNotification;
use App\Models\User;

class CheckCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'user_id',
        'status',
        'note',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saved(function (self $call) {
            try {
                event(new \App\Events\TmsMapUpdated('check_call', $call->load_id));
            } catch (\Throwable $e) {
                // ignore broadcast failures
            }

            // SLA alerts for issue/delayed check calls
            $watch = ['issue', 'delayed', 'delay', 'late'];
            $status = strtolower($call->status ?? '');
            if ($call->load_id && collect($watch)->contains(fn ($w) => str_contains($status, $w))) {
                $load = $call->loadRelation()->first();
                if ($load) {
                    $reason = "Check call marked '{$call->status}'";
                    $users = User::all();
                    foreach ($users as $user) {
                        $user->notify(new SlaAlertNotification($load->load_number ?? '#', $reason, $load->status, $load->id));
                    }
                }
            }
        });

        static::deleted(function (self $call) {
            try {
                event(new \App\Events\TmsMapUpdated('check_call', $call->load_id));
            } catch (\Throwable $e) {
                // ignore broadcast failures
            }
        });
    }
}
