<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Taxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Taxis",
 *     description="API Endpoints for managing taxis"
 * )
 */
class TaxiController extends Controller
{
    /**
     * Display a listing of taxis.
     * 
     * @OA\Get(
     *     path="/api/taxis",
     *     tags={"Taxis"},
     *     summary="Get list of taxis",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter taxis by status",
     *         @OA\Schema(type="string", enum={"active", "inactive", "maintenance", "in_tour"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Taxi")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Taxi::query();
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $taxis = $query->get();
        
        return response()->json(['data' => $taxis]);
    }

    /**
     * Store a newly created taxi in storage.
     * 
     * @OA\Post(
     *     path="/api/taxis",
     *     tags={"Taxis"},
     *     summary="Create new taxi",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title","engine_capacity","kmpl","fuel_type","gear_type","passenger_count","cost_per_day","description","status","display_image"},
     *                 @OA\Property(property="title", type="string", example="Toyota Corolla"),
     *                 @OA\Property(property="engine_capacity", type="string", example="1800cc"),
     *                 @OA\Property(property="kmpl", type="number", format="float", example=14.5),
     *                 @OA\Property(property="fuel_type", type="string", example="Petrol"),
     *                 @OA\Property(property="gear_type", type="string", example="Automatic"),
     *                 @OA\Property(property="passenger_count", type="integer", example=5),
     *                 @OA\Property(property="cost_per_day", type="number", format="float", example=50.00),
     *                 @OA\Property(property="description", type="string", example="Comfortable sedan for family trips"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance", "in_tour"}, example="active"),
     *                 @OA\Property(property="display_image", type="string", format="binary"),
     *                 @OA\Property(property="image1", type="string", format="binary"),
     *                 @OA\Property(property="image2", type="string", format="binary"),
     *                 @OA\Property(property="image3", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Taxi created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Taxi created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Taxi")
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
            'engine_capacity' => 'required|string|max:50',
            'kmpl' => 'required|numeric',
            'fuel_type' => 'required|string|max:50',
            'gear_type' => 'required|string|max:50',
            'passenger_count' => 'required|integer|min:1',
            'cost_per_day' => 'required|numeric',
            'description' => 'required|string',
            'status' => 'required|string|in:active,inactive,maintenance,in_tour',
            'display_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Handle display image
        if ($request->hasFile('display_image')) {
            $path = $request->file('display_image')->store('taxis', 'public');
            $data['display_image'] = $path;
        }
        
        // Handle additional images
        foreach (['image1', 'image2', 'image3'] as $image) {
            if ($request->hasFile($image)) {
                $path = $request->file($image)->store('taxis', 'public');
                $data[$image] = $path;
            }
        }

        $taxi = Taxi::create($data);

        return response()->json(['message' => 'Taxi created successfully', 'data' => $taxi], 201);
    }

    /**
     * Display the specified taxi.
     * 
     * @OA\Get(
     *     path="/api/taxis/{id}",
     *     tags={"Taxis"},
     *     summary="Get taxi by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Taxi"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Taxi not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $taxi = Taxi::findOrFail($id);
        
        return response()->json(['data' => $taxi]);
    }

    /**
     * Update the specified taxi in storage.
     * 
     * @OA\Post(
     *     path="/api/taxis/{id}",
     *     tags={"Taxis"},
     *     summary="Update existing taxi",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="engine_capacity", type="string"),
     *                 @OA\Property(property="kmpl", type="number", format="float"),
     *                 @OA\Property(property="fuel_type", type="string"),
     *                 @OA\Property(property="gear_type", type="string"),
     *                 @OA\Property(property="passenger_count", type="integer"),
     *                 @OA\Property(property="cost_per_day", type="number", format="float"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance", "in_tour"}),
     *                 @OA\Property(property="display_image", type="string", format="binary"),
     *                 @OA\Property(property="image1", type="string", format="binary"),
     *                 @OA\Property(property="image2", type="string", format="binary"),
     *                 @OA\Property(property="image3", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", default="PUT", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Taxi updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Taxi updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Taxi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Taxi not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $taxi = Taxi::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'engine_capacity' => 'sometimes|string|max:50',
            'kmpl' => 'sometimes|numeric',
            'fuel_type' => 'sometimes|string|max:50',
            'gear_type' => 'sometimes|string|max:50',
            'passenger_count' => 'sometimes|integer|min:1',
            'cost_per_day' => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string|in:active,inactive,maintenance,in_tour',
            'display_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['display_image', 'image1', 'image2', 'image3']);
        
        // Handle display image update
        if ($request->hasFile('display_image')) {
            // Delete old image if exists
            if ($taxi->display_image && Storage::disk('public')->exists($taxi->display_image)) {
                Storage::disk('public')->delete($taxi->display_image);
            }
            
            $path = $request->file('display_image')->store('taxis', 'public');
            $data['display_image'] = $path;
        }
        
        // Handle additional images update
        foreach (['image1', 'image2', 'image3'] as $image) {
            if ($request->hasFile($image)) {
                // Delete old image if exists
                if ($taxi->$image && Storage::disk('public')->exists($taxi->$image)) {
                    Storage::disk('public')->delete($taxi->$image);
                }
                
                $path = $request->file($image)->store('taxis', 'public');
                $data[$image] = $path;
            }
        }

        $taxi->update($data);

        return response()->json(['message' => 'Taxi updated successfully', 'data' => $taxi]);
    }

    /**
     * Update the status of a taxi.
     * 
     * @OA\Put(
     *     path="/api/taxis/{id}/status",
     *     tags={"Taxis"},
     *     summary="Update taxi status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"active", "inactive", "maintenance", "in_tour"},
     *                 example="active"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Taxi status updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Taxi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Taxi not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateStatus(Request $request, string $id)
    {
        $taxi = Taxi::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,inactive,maintenance,in_tour',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $taxi->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Taxi status updated successfully',
            'data' => $taxi
        ]);
    }

    /**
     * Remove the specified taxi from storage.
     * 
     * @OA\Delete(
     *     path="/api/taxis/{id}",
     *     tags={"Taxis"},
     *     summary="Delete taxi",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Taxi deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Taxi deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Taxi not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $taxi = Taxi::findOrFail($id);
        
        // Delete associated images
        $imagePaths = [
            $taxi->display_image,
            $taxi->image1,
            $taxi->image2,
            $taxi->image3
        ];
        
        foreach ($imagePaths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        
        $taxi->delete();

        return response()->json(['message' => 'Taxi deleted successfully']);
    }
}