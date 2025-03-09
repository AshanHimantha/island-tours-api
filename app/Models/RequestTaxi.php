<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestTaxi extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identification_number',
        'contact_number',
        'whatsapp_number',
        'adult_count',
        'kids_count',
        'date_from',
        'date_to',
        'status'
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];
}