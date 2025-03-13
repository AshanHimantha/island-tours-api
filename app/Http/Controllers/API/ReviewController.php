<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Reviews",
 *     description="API Endpoints for Reviews"
 * )
 */
class ReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/reviews",
     *     operationId="getReviews",
     *     tags={"Reviews"},
     *     summary="Get list of reviews",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"published", "pending", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="rating",
     *         in="query",
     *         description="Filter by rating (1-5)",
     *         @OA\Schema(type="integer", minimum=1, maximum=5)
     *     ),
     *     @OA\Parameter(
     *         name="country",
     *         in="query",
     *         description="Filter by country",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Review")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Review::query();
        
        // Filter by rating if provided
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        // Filter by country if provided
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // By default, show only published reviews
            $query->where('status', 'published');
        }
        
        $reviews = $query->latest()->get();
        
        return response()->json(['data' => $reviews]);
    }


    public function getAllReviews()
{
    $reviews = Review::latest()->get();
    return response()->json(['data' => $reviews]);
}

    /**
     * @OA\Post(
     *     path="/api/reviews",
     *     operationId="storeReview",
     *     tags={"Reviews"},
     *     summary="Create a new review",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "country", "rating", "comment"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="country", type="string", example="United States"),
     *             @OA\Property(property="rating", type="integer", example=5),
     *             @OA\Property(property="comment", type="string", example="Great experience, highly recommended!"),
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="pending"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'status' => 'sometimes|string|in:published,pending,rejected',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image']);
        
        // Set default status to pending if not provided
        if (!$request->has('status')) {
            $data['status'] = 'pending';
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('reviews', 'public');
            $data['image'] = $path;
        }

        $review = Review::create($data);

        return response()->json(['message' => 'Review created successfully', 'data' => $review], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/reviews/{id}",
     *     operationId="getReviewById",
     *     tags={"Reviews"},
     *     summary="Get review details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $review = Review::findOrFail($id);
        
        return response()->json(['data' => $review]);
    }

    /**
     * @OA\Put(
     *     path="/api/reviews/{id}",
     *     operationId="updateReview",
     *     tags={"Reviews"},
     *     summary="Update an existing review",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="country", type="string", example="United States"),
     *             @OA\Property(property="rating", type="integer", example=5),
     *             @OA\Property(property="comment", type="string", example="Great experience, highly recommended!"),
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:100',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string',
            'status' => 'sometimes|string|in:published,pending,rejected',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image']);
        
        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($review->image && Storage::disk('public')->exists($review->image)) {
                Storage::disk('public')->delete($review->image);
            }
            
            $path = $request->file('image')->store('reviews', 'public');
            $data['image'] = $path;
        }

        $review->update($data);
        
        return response()->json(['message' => 'Review updated successfully', 'data' => $review]);
    }

    /**
     * @OA\Patch(
     *     path="/api/reviews/{id}/status",
     *     operationId="updateReviewStatus",
     *     tags={"Reviews"},
     *     summary="Update the status of a review",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review status updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function updateStatus(Request $request, string $id)
    {
        $review = Review::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:published,pending,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Review status updated successfully',
            'data' => $review
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/reviews/{id}",
     *     operationId="deleteReview",
     *     tags={"Reviews"},
     *     summary="Delete a review",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        
        // Delete image if exists
        if ($review->image && Storage::disk('public')->exists($review->image)) {
            Storage::disk('public')->delete($review->image);
        }
        
        $review->delete();
        
        return response()->json(['message' => 'Review deleted successfully']);
    }
    
    /**
     * @OA\Get(
     *     path="/api/reviews/featured",
     *     operationId="getFeaturedReviews",
     *     tags={"Reviews"},
     *     summary="Get featured reviews (4-5 stars and published)",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Review")
     *             )
     *         )
     *     )
     * )
     */
    public function featured()
    {
        $reviews = Review::where('rating', '>=', 4)
                         ->where('status', 'published')
                         ->latest()
                         ->take(5)
                         ->get();
        
        return response()->json(['data' => $reviews]);
    }
}