<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends ClinicModel
{
    protected $table = 'notification';

    protected $primaryKey = 'notification_id';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}