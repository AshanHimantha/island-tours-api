<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Review",
 *     required={"name", "country", "rating", "comment"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="country", type="string"),
 *     @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
 *     @OA\Property(property="comment", type="string"),
 *     @OA\Property(property="image", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Review extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'country',
        'rating',
        'comment',
        'image',
        'status',
    ];
}