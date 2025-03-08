<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Tour",
 *     required={"title", "itinerary", "include", "exclude", "per_adult_price", "location"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="itinerary", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="include", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="exclude", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="per_adult_price", type="number", format="float"),
 *     @OA\Property(property="location", type="string"),
 *     @OA\Property(property="status", type="string", enum={"available", "unavailable", "booked", "seasonal"}),
 *     @OA\Property(property="display_image", type="string"),
 *     @OA\Property(property="image1", type="string", nullable=true),
 *     @OA\Property(property="image2", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Tour extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'itinerary',
        'include',
        'exclude',
        'per_adult_price',
        'location',
        'status',
        'display_image',
        'image1',
        'image2',
    ];
}