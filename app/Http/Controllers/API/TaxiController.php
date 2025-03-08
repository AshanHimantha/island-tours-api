<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Taxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaxiController extends Controller
{
    /**
     * Display a listing of taxis.
     */
    public function index()
    {
        $taxis = Taxi::all();
        
        return response()->json(['data' => $taxis]);
    }

    /**
     * Store a newly created taxi in storage.
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
     */
    public function show(string $id)
    {
        $taxi = Taxi::findOrFail($id);
        
        return response()->json(['data' => $taxi]);
    }

    /**
     * Update the specified taxi in storage.
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
     * Remove the specified taxi from storage.
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