<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherSummary extends Model
{
    use HasFactory;

    protected $fillable = array('city', 'data', 'created_at');

    public $timestamps = false;
}
