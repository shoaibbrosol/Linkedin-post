<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'linkedin_account_id',
    'title',
    'content',
    'media_path',
    'scheduled_at',
    'posted_at',
    'status',
    'linkedin_post_id',
    'api_response',
    'error_message',
    'retry_count',
])]
class LinkedinPost extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'pending', 'posted', 'failed', 'cancelled'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'posted_at' => 'datetime',
            'api_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function linkedinAccount(): BelongsTo
    {
        return $this->belongsTo(LinkedinAccount::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LinkedinPostLog::class);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'failed'], true);
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < config('services.linkedin.max_retries', 3);
    }
}
