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
     *     tags={"Reviews"},
     *     summary="Get list of reviews",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"published", "pending", "rejected"})
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

    /**
     * Store a newly created review in storage.
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
     * Display the specified review.
     */
    public function show(string $id)
    {
        $review = Review::findOrFail($id);
        
        return response()->json(['data' => $review]);
    }

    /**
     * Update the specified review in storage.
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
     * Update the status of a review.
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
     * Remove the specified review from storage.
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
     * Get featured reviews (highest rated and published).
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