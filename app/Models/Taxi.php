<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'engine_capacity',
        'kmpl',
        'fuel_type',
        'gear_type',
        'passenger_count',
        'cost_per_day',
        'description',
        'display_image',
        'image1',
        'image2',
        'image3',
    ];
}