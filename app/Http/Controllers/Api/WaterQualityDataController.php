<?php

namespace App\Http\Controllers\Api;

use App\Events\WaterQualityCreated;
use App\Http\Controllers\Controller;
use App\Models\WaterQualityData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WaterQualityDataController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate request input (excluding api_key since it's now in the header)
            $request->validate([
                'user_id' => 'required|exists:users,user_id',
                'recorded_at' => 'required|date',
                'temperature' => 'required|numeric',
                'salinity' => 'required|numeric',
                'dissolved_oxygen' => 'required|numeric',
                'ph_level' => 'required|numeric',
            ]);

            // Extract API key from Authorization header
            $authorizationHeader = $request->header('Authorization');

            if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
                return response()->json(['error' => 'Authorization header missing or invalid'], Response::HTTP_UNAUTHORIZED);
            }

            $apiKey = $matches[1];

            $user = User::where('user_id', $request->user_id)
                        ->where('api_key', $apiKey)
                        ->first();

            if (!$user) {
                return response()->json(['error' =>  'Invalid API key', 'user' => $user], Response::HTTP_UNAUTHORIZED);
            }

            // Check temperature condition
            if ($request->temperature != 85.0000) {
                $waterQualityData = WaterQualityData::create($request->all());

                // Broadcast event with the created data
                broadcast(new WaterQualityCreated($waterQualityData));
                Log::info('WaterQualityCreated event triggered.');

                return response()->json(['success' => true], Response::HTTP_CREATED);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
