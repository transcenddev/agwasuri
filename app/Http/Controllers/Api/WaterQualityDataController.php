<?php

namespace App\Http\Controllers\Api;

use App\Events\WaterQualityCreated;
use App\Http\Controllers\Controller;
use App\Models\WaterQualityData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;


class WaterQualityDataController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'recorded_at' => 'required|date',
                'temperature' => 'required|numeric',
                'salinity' => 'required|numeric',
                'dissolved_oxygen' => 'required|numeric',
                'ph_level' => 'required|numeric',
            ]);

            WaterQualityData::create($request->all());

            // event(new WaterQualityCreated());
            broadcast(new WaterQualityCreated());
            \Log::info('WaterQualityCreated event triggered.');

            return response()->json(['success' => true], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
