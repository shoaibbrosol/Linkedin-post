<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'linkedin_user_id',
    'name',
    'email',
    'access_token',
    'refresh_token',
    'token_expires_at',
    'status',
])]
class LinkedinAccount extends Model
{
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(LinkedinPost::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->token_expires_at === null || $this->token_expires_at->isFuture());
    }
}
