<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class ClinicModel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;
}