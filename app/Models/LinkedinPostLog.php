<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'linkedin_post_id',
    'status',
    'message',
    'request_payload',
    'response_payload',
])]
class LinkedinPostLog extends Model
{
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function linkedinPost(): BelongsTo
    {
        return $this->belongsTo(LinkedinPost::class);
    }
}
