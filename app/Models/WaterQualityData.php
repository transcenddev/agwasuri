<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterQualityData extends Model
{
    protected $fillable = ['recorded_at', 'temperature', 'salinity', 'dissolved_oxygen', 'ph_level'];
}
