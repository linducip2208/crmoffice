<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        if (empty($changes)) {
            return;
        }
        $before = collect($changes)->mapWithKeys(fn ($_, $k) => [$k => $model->getOriginal($k)])->toArray();
        $this->record('updated', $model, $before, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, $model->getOriginal(), []);
    }

    private function record(string $action, Model $model, array $before, array $after): void
    {
        // Strip sensitive fields
        $strip = ['password', 'remember_token', 'api_key_encrypted', 'value_encrypted',
            'two_factor_secret', 'two_factor_recovery_codes', 'secret', 'invitation_token'];
        foreach ($strip as $k) {
            unset($before[$k], $after[$k]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'before' => $before ?: null,
            'after' => $after ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 250),
            'created_at' => now(),
        ]);
    }
}
