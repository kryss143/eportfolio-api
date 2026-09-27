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
        // getChanges() holds the raw dirty values that were just written; it is
        // populated by syncChanges() before the 'updated' event fires (and
        // before syncOriginal() in finishSave), so getOriginal() still yields
        // the pre-update snapshot here.
        //
        // The previous implementation diffed getAttributes() (raw storage —
        // JSON strings for array/json casts on the MongoDB package) against
        // getOriginal() (cast-decoded PHP arrays). array_diff_assoc() then
        // string-compared an array value and raised "Array to string
        // conversion", which Laravel's error handler converts to an
        // ErrorException -> 500 on every Project update (audit F1, 2026-09-28).
        // It also flagged technologies/metrics as changed on every save.
        $changes = $model->getChanges();

        if (! empty($changes)) {
            $this->log($model, 'updated', $model->getOriginal(), $changes);
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
