<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="RequestTaxi",
 *     title="Request Taxi",
 *     description="Request Taxi model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="ID"),
 *     @OA\Property(property="name", type="string", example="John Doe", description="Customer name"),
 *     @OA\Property(property="identification_number", type="string", example="AB123456", description="Customer identification number"),
 *     @OA\Property(property="contact_number", type="string", example="+1234567890", description="Contact phone number"),
 *     @OA\Property(property="whatsapp_number", type="string", example="+1234567890", description="WhatsApp contact number"),
 *     @OA\Property(property="adult_count", type="integer", example=2, description="Number of adults"),
 *     @OA\Property(property="kids_count", type="integer", example=1, description="Number of children"),
 *     @OA\Property(property="date_from", type="string", format="date", example="2025-03-10", description="Start date"),
 *     @OA\Property(property="date_to", type="string", format="date", example="2025-03-15", description="End date"),
 *     @OA\Property(property="status", type="string", example="pending", description="Request status"),
 *     @OA\Property(property="taxi_id", type="integer", example=1, description="ID of the requested taxi"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T12:00:00Z")
 * )
 */

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
        'status',
        'taxi_id',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];
    
    /**
     * Get the taxi associated with the request.
     */
    public function taxi(): BelongsTo
    {
        return $this->belongsTo(Taxi::class);
    }
}