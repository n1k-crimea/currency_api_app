<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    protected $casts = [
        'created_at' => 'datetime:d.m.Y',
    ];
    protected $fillable = [
        'name',
        'rate'
    ];
}
