<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\RequestTaxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Taxi Requests",
 *     description="API Endpoints for managing taxi requests"
 * )
 */
class RequestTaxiController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/api/taxi-requests",
     *     summary="Get list of all taxi requests",
     *     tags={"Taxi Requests"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="identification_number", type="string", example="ID123456789"),
     *                     @OA\Property(property="contact_number", type="string", example="+9412345678"),
     *                     @OA\Property(property="whatsapp_number", type="string", example="+9498765432"),
     *                     @OA\Property(property="adult_count", type="integer", example=2),
     *                     @OA\Property(property="kids_count", type="integer", example=1),
     *                     @OA\Property(property="date_from", type="string", format="date", example="2025-03-15"),
     *                     @OA\Property(property="date_to", type="string", format="date", example="2025-03-20"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-09T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-09T10:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $requests = RequestTaxi::all();
        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/api/taxi-requests",
     *     summary="Create a new taxi request",
     *     tags={"Taxi Requests"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "identification_number", "contact_number", "adult_count", "date_from", "date_to"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Customer name"),
     *             @OA\Property(property="identification_number", type="string", example="ID123456789", description="NIC or Passport Number"),
     *             @OA\Property(property="contact_number", type="string", example="+9412345678", description="Customer phone number"),
     *             @OA\Property(property="whatsapp_number", type="string", example="+9498765432", description="Customer WhatsApp number"),
     *             @OA\Property(property="adult_count", type="integer", example=2, description="Number of adults"),
     *             @OA\Property(property="kids_count", type="integer", example=1, description="Number of children"),
     *             @OA\Property(property="date_from", type="string", format="date", example="2025-03-15", description="Start date"),
     *             @OA\Property(property="date_to", type="string", format="date", example="2025-03-20", description="End date"),
     *             @OA\Property(property="taxi_id", type="integer", example=1, description="ID of the taxi to request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Taxi request created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="identification_number", type="string", example="ID123456789"),
     *                 @OA\Property(property="contact_number", type="string", example="+9412345678"),
     *                 @OA\Property(property="whatsapp_number", type="string", example="+9498765432"),
     *                 @OA\Property(property="adult_count", type="integer", example=2),
     *                 @OA\Property(property="kids_count", type="integer", example=1),
     *                 @OA\Property(property="date_from", type="string", format="date", example="2025-03-15"),
     *                 @OA\Property(property="date_to", type="string", format="date", example="2025-03-20"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-09T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-09T10:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Taxi request created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"name": {"The name field is required."}})
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'identification_number' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'adult_count' => 'required|integer|min:1',
            'kids_count' => 'nullable|integer|min:0',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'taxi_id' => 'required|integer|exists:taxis,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $taxiRequest = RequestTaxi::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $taxiRequest,
            'message' => 'Taxi request created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/api/taxi-requests/{id}",
     *     summary="Get specific taxi request details",
     *     tags={"Taxi Requests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi request ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="identification_number", type="string", example="ID123456789"),
     *                 @OA\Property(property="contact_number", type="string", example="+9412345678"),
     *                 @OA\Property(property="whatsapp_number", type="string", example="+9498765432"),
     *                 @OA\Property(property="adult_count", type="integer", example=2),
     *                 @OA\Property(property="kids_count", type="integer", example=1),
     *                 @OA\Property(property="date_from", type="string", format="date", example="2025-03-15"),
     *                 @OA\Property(property="date_to", type="string", format="date", example="2025-03-20"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-09T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-09T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     )
     * )
     */
    public function show(RequestTaxi $requestTaxi)
    {
        return response()->json([
            'status' => 'success',
            'data' => $requestTaxi
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/api/taxi-requests/{id}",
     *     summary="Update existing taxi request",
     *     tags={"Taxi Requests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi request ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="identification_number", type="string", example="ID123456789"),
     *             @OA\Property(property="contact_number", type="string", example="+9412345678"),
     *             @OA\Property(property="whatsapp_number", type="string", example="+9498765432"),
     *             @OA\Property(property="adult_count", type="integer", example=2),
     *             @OA\Property(property="kids_count", type="integer", example=1),
     *             @OA\Property(property="date_from", type="string", format="date", example="2025-03-15"),
     *             @OA\Property(property="date_to", type="string", format="date", example="2025-03-20"),
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "completed", "cancelled"}, example="confirmed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Taxi request updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="identification_number", type="string", example="ID123456789"),
     *                 @OA\Property(property="contact_number", type="string", example="+9412345678"),
     *                 @OA\Property(property="whatsapp_number", type="string", example="+9498765432"),
     *                 @OA\Property(property="adult_count", type="integer", example=2),
     *                 @OA\Property(property="kids_count", type="integer", example=1),
     *                 @OA\Property(property="date_from", type="string", format="date", example="2025-03-15"),
     *                 @OA\Property(property="date_to", type="string", format="date", example="2025-03-20"),
     *                 @OA\Property(property="status", type="string", example="confirmed"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-03-09T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-03-09T11:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Taxi request updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     )
     * )
     */
    public function update(Request $request, RequestTaxi $requestTaxi)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'identification_number' => 'sometimes|string|max:255',
            'contact_number' => 'sometimes|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'adult_count' => 'sometimes|integer|min:1',
            'kids_count' => 'nullable|integer|min:0',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'taxi_id' => 'sometimes|integer|exists:taxis,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $requestTaxi->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $requestTaxi,
            'message' => 'Taxi request updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/api/taxi-requests/{id}",
     *     summary="Delete a taxi request",
     *     tags={"Taxi Requests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Taxi request ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Taxi request deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Taxi request deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     )
     * )
     */
    public function destroy(RequestTaxi $requestTaxi)
    {
        $requestTaxi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Taxi request deleted successfully'
        ]);
    }
}