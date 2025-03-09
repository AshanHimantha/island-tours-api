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
     *     summary="Create a new tour plan",
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
}