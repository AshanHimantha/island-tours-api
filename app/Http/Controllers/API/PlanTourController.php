<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourPlan;
use App\Models\Taxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Tour Plans",
 *     description="API endpoints for tour planning"
 * )
 */
class PlanTourController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tour-plans",
     *     operationId="getTourPlans",
     *     tags={"Tour Plans"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of tour plans",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "canceled", "completed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TourPlan"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = TourPlan::query()->with(['tour', 'vehicle']);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tourPlans = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $tourPlans
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tour-plans",
     *     operationId="storeTourPlan",
     *     tags={"Tour Plans"},
     *     summary="Create a new tour plan (Public endpoint)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tour_id","tour_date","requester_name","requester_email","requester_id_passport","contact_number","adult_count","country","vehicle_id"},
     *             @OA\Property(property="tour_id", type="integer", example=1),
     *             @OA\Property(property="tour_date", type="string", format="date", example="2025-05-15"),
     *             @OA\Property(property="requester_name", type="string", example="John Doe"),
     *             @OA\Property(property="requester_email", type="string", example="john@example.com"),
     *             @OA\Property(property="requester_id_passport", type="string", example="AB123456"),
     *             @OA\Property(property="contact_number", type="string", example="+1234567890"),
     *             @OA\Property(property="whatsapp", type="string", example="+1234567890", nullable=true),
     *             @OA\Property(property="adult_count", type="integer", example=2),
     *             @OA\Property(property="kids_count", type="integer", example=1),
     *             @OA\Property(property="country", type="string", example="United States"),
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="pending", enum={"pending", "confirmed", "canceled", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tour plan created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour planned successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TourPlan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|exists:tours,id',
            'tour_date' => 'required|date',
            'requester_name' => 'required|string|max:255',
            'requester_email' => 'required|email',
            'requester_id_passport' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'adult_count' => 'required|integer|min:1',
            'kids_count' => 'nullable|integer|min:0',
            'country' => 'required|string|max:100',
            'vehicle_id' => 'required|exists:taxis,id',
            'status' => 'nullable|string|in:pending,confirmed,canceled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tourPlan = TourPlan::create($request->all());
        $tourPlan->load(['tour', 'vehicle']);

        return response()->json([
            'success' => true,
            'message' => 'Tour planned successfully',
            'data' => $tourPlan
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tour-plans/{id}",
     *     operationId="getTourPlanById",
     *     tags={"Tour Plans"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get tour plan details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TourPlan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour plan not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tour plan not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $tourPlan = TourPlan::with(['tour', 'vehicle'])->find($id);

        if (!$tourPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Tour plan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tourPlan
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/tour-plans/{id}",
     *     operationId="updateTourPlan",
     *     tags={"Tour Plans"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update an existing tour plan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tour_id", type="integer"),
     *             @OA\Property(property="tour_date", type="string", format="date"),
     *             @OA\Property(property="requester_name", type="string"),
     *             @OA\Property(property="requester_email", type="string"),
     *             @OA\Property(property="requester_id_passport", type="string"),
     *             @OA\Property(property="contact_number", type="string"),
     *             @OA\Property(property="whatsapp", type="string"),
     *             @OA\Property(property="adult_count", type="integer"),
     *             @OA\Property(property="kids_count", type="integer"),
     *             @OA\Property(property="country", type="string"),
     *             @OA\Property(property="vehicle_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "canceled", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour plan updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour plan updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TourPlan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour plan not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $tourPlan = TourPlan::find($id);

        if (!$tourPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Tour plan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tour_id' => 'sometimes|exists:tours,id',
            'tour_date' => 'sometimes|date',
            'requester_name' => 'sometimes|string|max:255',
            'requester_email' => 'sometimes|email',
            'requester_id_passport' => 'sometimes|string|max:255',
            'contact_number' => 'sometimes|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'adult_count' => 'sometimes|integer|min:1',
            'kids_count' => 'nullable|integer|min:0',
            'country' => 'sometimes|string|max:100',
            'vehicle_id' => 'sometimes|exists:taxis,id',
            'status' => 'sometimes|string|in:pending,confirmed,canceled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tourPlan->update($request->all());
        $tourPlan->load(['tour', 'vehicle']);

        return response()->json([
            'success' => true,
            'message' => 'Tour plan updated successfully',
            'data' => $tourPlan
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/tour-plans/{id}",
     *     operationId="deleteTourPlan",
     *     tags={"Tour Plans"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a tour plan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour plan deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tour plan deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tour plan not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $tourPlan = TourPlan::find($id);

        if (!$tourPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Tour plan not found'
            ], 404);
        }

        $tourPlan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tour plan deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tour-plans/search",
     *     operationId="searchTourPlans",
     *     tags={"Tour Plans"},
     *     security={{"bearerAuth":{}}},
     *     summary="Search tour plans with advanced filters",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by requester name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="identification_number",
     *         in="query",
     *         description="Filter by passport/ID number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="contact_number",
     *         in="query",
     *         description="Filter by contact number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="whatsapp_number",
     *         in="query",
     *         description="Filter by whatsapp number",
     *         required=false,
     *         @OA\Schema(type="string", nullable=true)
     *     ),
     *     @OA\Parameter(
     *         name="adult_count",
     *         in="query",
     *         description="Filter by number of adults",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="kids_count",
     *         in="query",
     *         description="Filter by number of kids",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter by tour date from (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter by tour date to (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "completed", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TourPlan"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = TourPlan::query()->with(['tour', 'vehicle']);
        
        // Filter by requester name
        if ($request->has('name') && !empty($request->name)) {
            $query->where('requester_name', 'like', '%' . $request->name . '%');
        }
        
        // Filter by identification number (passport/ID)
        if ($request->has('identification_number') && !empty($request->identification_number)) {
            $query->where('requester_id_passport', 'like', '%' . $request->identification_number . '%');
        }
        
        // Filter by contact number
        if ($request->has('contact_number') && !empty($request->contact_number)) {
            $query->where('contact_number', 'like', '%' . $request->contact_number . '%');
        }
        
        // Filter by WhatsApp number
        if ($request->has('whatsapp_number') && !empty($request->whatsapp_number)) {
            $query->where('whatsapp', 'like', '%' . $request->whatsapp_number . '%');
        }
        
        // Filter by adult count
        if ($request->has('adult_count') && is_numeric($request->adult_count)) {
            $query->where('adult_count', $request->adult_count);
        }
        
        // Filter by kids count
        if ($request->has('kids_count') && is_numeric($request->kids_count)) {
            $query->where('kids_count', $request->kids_count);
        }
        
        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('tour_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('tour_date', '<=', $request->date_to);
        }
        
        // Filter by status (handle both canceled and cancelled spellings)
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            if ($status === 'cancelled') {
                $status = 'canceled';
            }
            $query->where('status', $status);
        }
        
        $tourPlans = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $tourPlans
        ]);
    }
}