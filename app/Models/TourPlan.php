<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TourPlan",
 *     required={"tour_id", "tour_date", "requester_name", "requester_email", "requester_id_passport", "contact_number", "adult_count", "country", "vehicle_id"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="tour_id", type="integer", format="int64"),
 *     @OA\Property(property="tour_date", type="string", format="date"),
 *     @OA\Property(property="requester_name", type="string"),
 *     @OA\Property(property="requester_email", type="string", format="email"),
 *     @OA\Property(property="requester_id_passport", type="string"),
 *     @OA\Property(property="contact_number", type="string"),
 *     @OA\Property(property="whatsapp", type="string", nullable=true),
 *     @OA\Property(property="adult_count", type="integer"),
 *     @OA\Property(property="kids_count", type="integer"),
 *     @OA\Property(property="country", type="string"),
 *     @OA\Property(property="vehicle_id", type="integer", format="int64"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TourPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tour_id',
        'tour_date',
        'requester_name',
        'requester_email',
        'requester_id_passport',
        'contact_number',
        'whatsapp',
        'adult_count',
        'kids_count',
        'country',
        'vehicle_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tour_date' => 'date',
        'adult_count' => 'integer',
        'kids_count' => 'integer',
    ];

    /**
     * Get the tour associated with the plan.
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the vehicle associated with the plan.
     */
    public function vehicle()
    {
        return $this->belongsTo(Taxi::class, 'vehicle_id');
    }
}