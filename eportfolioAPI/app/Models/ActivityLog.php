<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MongoDB\Laravel\Eloquent\Model;

// activity_log lives on MongoDB (see create_activity_log_table), so this is
// the package's document model: the plain base Model routes queries through
// SQL grammar and crashes on the MongoDB connection ("Call to a member
// function prepare() on null"). old_values/new_values use the 'json' cast
// (audit F4 pattern): the MongoDB package stores arrays as JSON-encoded
// strings and the 'array' cast triggers a USER_DEPRECATED on every write;
// both casts decode to PHP arrays.
class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'event',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
