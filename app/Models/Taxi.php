<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Taxi",
 *     required={"title", "engine_capacity", "kmpl", "fuel_type", "gear_type", "passenger_count", "cost_per_day"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="engine_capacity", type="string"),
 *     @OA\Property(property="kmpl", type="number", format="float"),
 *     @OA\Property(property="fuel_type", type="string"),
 *     @OA\Property(property="gear_type", type="string"),
 *     @OA\Property(property="passenger_count", type="integer"),
 *     @OA\Property(property="cost_per_day", type="number", format="float"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance", "in_tour"}),
 *     @OA\Property(property="display_image", type="string"),
 *     @OA\Property(property="image1", type="string", nullable=true),
 *     @OA\Property(property="image2", type="string", nullable=true),
 *     @OA\Property(property="image3", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Taxi extends Model
{
    use HasFactory, SoftDeletes;

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
        'number_plate',
        'description',
        'display_image',
        'image1',
        'image2',
        'image3',
        'status',
    ];
}