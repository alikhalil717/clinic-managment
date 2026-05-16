<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Secretary extends ClinicModel
{
    protected $table = 'secretary';

    protected $primaryKey = 'secretary_id';

    public $incrementing = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id', 'user_id');
    }
}