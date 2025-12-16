<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishpondOperator extends Model
{
    use HasFactory;

    protected $table = 'fishpond_operators';

    protected $fillable = [
        'name',
        'pond_name',
        'barangay',
        'municipality',
        'province',
        'total_area_ha',
        'species_cultured',
        'water_type',
        'contact',
        'notes',
        'source',
        'created_by',
    ];

    protected $casts = [
        'total_area_ha' => 'decimal:2',
    ];
}
