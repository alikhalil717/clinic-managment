<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends ClinicModel
{
    protected $table = 'device_token';

    protected $primaryKey = 'device_token_id';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}