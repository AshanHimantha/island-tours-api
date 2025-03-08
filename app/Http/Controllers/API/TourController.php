<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Tours",
 *     description="API Endpoints for managing tours"
 * )
 */
class TourController extends Controller
{
    /**
     * Display a listing of tours.
     * 
     * @OA\Get(
     *     path="/api/tours",
     *     tags={"Tours"},
     *     summary="Get list of tours",
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Filter tours by location",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter tours by status",
     *         @OA\Schema(type="string", enum={"available", "unavailable", "booked", "seasonal"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Tour")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Tour::query();
        
        // Filter by location if provided
        if ($request->has('location')) {
            $query->where('location', $request->location);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tours = $query->get();
        
        return response()->json(['data' => $tours]);
    }

    /**
     * Store a newly created tour in storage.
     * 
     * @OA\Post(
     *     path="/api/tours",
     *     tags={"Tours"},
     *     summary="Create new tour",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title","itinerary","include","exclude","per_adult_price","location","status","display_image"},
     *                 @OA\Property(property="title", type="string", example="Island Discovery Tour"),
     *                 @OA\Property(
     *                     property="itinerary", 
     *                     type="array", 
     *                     @OA\Items(type="string"),
     *                     example={"Day 1: Beach tour", "Day 2: Mountain hiking"}
     *                 ),
     *                 @OA\Property(
     *                     property="include", 
     *                     type="array", 
     *                     @OA\Items(type="string"),
     *                     example={"Hotel pickup", "Lunch", "Guide"}
     *                 ),
     *                 @OA\Property(
     *                     property="exclude", 
     *                     type="array", 
     *                     @OA\Items(type="string"),
     *                     example={"Drinks", "Tips"}
     *                 ),
     *                 @OA\Property(property="per_adult_price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="location", type="string", example="East Coast"),
     *                 @OA\Property(property="status", type="string", enum={"available", "unavailable", "booked", "seasonal"}, example="available"),
     *                 @OA\Property(property="display_image", type="string", format="binary"),
     *                 @OA\Property(property="image1", type="string", format="binary"),
     *                 @OA\Property(property="image2", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tour created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tour created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Tour")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'itinerary' => 'required',
            'include' => 'required',
            'exclude' => 'required',
            'per_adult_price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'status' => 'required|string|in:available,unavailable,booked,seasonal',
            'display_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['display_image', 'image1', 'image2']);
        
        // Convert arrays to JSON for storage
        $data['itinerary'] = json_encode($request->itinerary);
        $data['include'] = json_encode($request->include);
        $data['exclude'] = json_encode($request->exclude);
        
        // Handle display image
        if ($request->hasFile('display_image')) {
            $path = $request->file('display_image')->store('tours', 'public');
            $data['display_image'] = $path;
        }
        
        // Handle additional images
        foreach (['image1', 'image2'] as $image) {
            if ($request->hasFile($image)) {
                $path = $request->file($image)->store('tours', 'public');
                $data[$image] = $path;
            }
        }

        $tour = Tour::create($data);

        // Convert JSON back to arrays for the response
        $tour->itinerary = json_decode($tour->itinerary);
        $tour->include = json_decode($tour->include);
        $tour->exclude = json_decode($tour->exclude);

        return response()->json(['message' => 'Tour created successfully', 'data' => $tour], 201);
    }

    /**
     * Display the specified tour.
     * 
     * @OA\Get(
     *     path="/api/tours/{id}",
     *     tags={"Tours"},
     *     summary="Get tour by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tour ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tour"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $tour = Tour::findOrFail($id);
        
        // Convert JSON back to arrays for the response
        $tour->itinerary = json_decode($tour->itinerary);
        $tour->include = json_decode($tour->include);
        $tour->exclude = json_decode($tour->exclude);
        
        return response()->json(['data' => $tour]);
    }

    /**
     * Update the specified tour in storage.
     * 
     * @OA\Post(
     *     path="/api/tours/{id}",
     *     tags={"Tours"},
     *     summary="Update existing tour",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tour ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(
     *                     property="itinerary", 
     *                     type="array", 
     *                     @OA\Items(type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="include", 
     *                     type="array", 
     *                     @OA\Items(type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="exclude", 
     *                     type="array", 
     *                     @OA\Items(type="string")
     *                 ),
     *                 @OA\Property(property="per_adult_price", type="number", format="float"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="status", type="string", enum={"available", "unavailable", "booked", "seasonal"}),
     *                 @OA\Property(property="display_image", type="string", format="binary"),
     *                 @OA\Property(property="image1", type="string", format="binary"),
     *                 @OA\Property(property="image2", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", default="PUT", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tour updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Tour")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $tour = Tour::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'itinerary' => 'sometimes',
            'include' => 'sometimes',
            'exclude' => 'sometimes',
            'per_adult_price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:available,unavailable,booked,seasonal',
            'display_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['display_image', 'image1', 'image2']);
        
        // Convert arrays to JSON for storage
        if ($request->has('itinerary')) {
            $data['itinerary'] = json_encode($request->itinerary);
        }
        
        if ($request->has('include')) {
            $data['include'] = json_encode($request->include);
        }
        
        if ($request->has('exclude')) {
            $data['exclude'] = json_encode($request->exclude);
        }
        
        // Handle display image update
        if ($request->hasFile('display_image')) {
            // Delete old image if exists
            if ($tour->display_image && Storage::disk('public')->exists($tour->display_image)) {
                Storage::disk('public')->delete($tour->display_image);
            }
            
            $path = $request->file('display_image')->store('tours', 'public');
            $data['display_image'] = $path;
        }
        
        // Handle additional images update
        foreach (['image1', 'image2'] as $image) {
            if ($request->hasFile($image)) {
                // Delete old image if exists
                if ($tour->$image && Storage::disk('public')->exists($tour->$image)) {
                    Storage::disk('public')->delete($tour->$image);
                }
                
                $path = $request->file($image)->store('tours', 'public');
                $data[$image] = $path;
            }
        }

        $tour->update($data);
        
        // Refresh model to get updated data
        $tour->refresh();
        
        // Convert JSON back to arrays for the response
        $tour->itinerary = json_decode($tour->itinerary);
        $tour->include = json_decode($tour->include);
        $tour->exclude = json_decode($tour->exclude);

        return response()->json(['message' => 'Tour updated successfully', 'data' => $tour]);
    }

    /**
     * Update the status of a tour.
     * 
     * @OA\Put(
     *     path="/api/tours/{id}/status",
     *     tags={"Tours"},
     *     summary="Update tour status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tour ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"available", "unavailable", "booked", "seasonal"},
     *                 example="available"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tour status updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Tour")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateStatus(Request $request, string $id)
    {
        $tour = Tour::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:available,unavailable,booked,seasonal',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tour->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Tour status updated successfully',
            'data' => $tour
        ]);
    }

    /**
     * Remove the specified tour from storage.
     * 
     * @OA\Delete(
     *     path="/api/tours/{id}",
     *     tags={"Tours"},
     *     summary="Delete tour",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tour ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tour deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $tour = Tour::findOrFail($id);
        
        // Delete images if they exist
        $images = ['display_image', 'image1', 'image2'];
        foreach ($images as $image) {
            if ($tour->$image && Storage::disk('public')->exists($tour->$image)) {
                Storage::disk('public')->delete($tour->$image);
            }
        }
        
        $tour->delete();
        
        return response()->json(['message' => 'Tour deleted successfully']);
    }
}
