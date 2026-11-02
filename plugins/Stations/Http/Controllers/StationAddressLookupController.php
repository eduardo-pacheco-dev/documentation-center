<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Services\StationAddressLookup;

class StationAddressLookupController extends Controller
{
    /**
     * Look up an address by ZIP code.
     */
    public function zip(Request $request, StationAddressLookup $lookup): JsonResponse
    {
        $this->authorize('create', Station::class);

        $validated = $request->validate([
            'zip' => ['required', 'string', 'max:9'],
        ]);

        $address = $lookup->fromZip($validated['zip']);

        if ($address === null) {
            return response()->json(['message' => 'CEP não encontrado.'], 404);
        }

        return response()->json($address);
    }

    /**
     * Look up an address by coordinates.
     */
    public function coordinates(Request $request, StationAddressLookup $lookup): JsonResponse
    {
        $this->authorize('create', Station::class);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $address = $lookup->fromCoordinates((float) $validated['latitude'], (float) $validated['longitude']);

        if ($address === null) {
            return response()->json(['message' => 'Não foi possível localizar o endereço.'], 404);
        }

        return response()->json($address);
    }
}
