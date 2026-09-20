<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $old = $model->getOriginal();
        $new = $model->getAttributes();
        $changes = array_diff_assoc($new, $old);

        if (! empty($changes)) {
            $this->log($model, 'updated', $old, $changes);
        }
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->getOriginal(), null);
    }

    protected function log(Model $model, string $event, ?array $old, ?array $new): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
