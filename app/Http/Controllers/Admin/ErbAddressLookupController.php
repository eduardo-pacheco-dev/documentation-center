<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Erb;
use App\Services\Erbs\ErbAddressLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ErbAddressLookupController extends Controller
{
    /**
     * Resolve an address from a Brazilian ZIP code.
     */
    public function zip(Request $request, ErbAddressLookup $lookup): JsonResponse
    {
        $this->authorize('create', Erb::class);

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
     * Resolve an address from coordinates.
     */
    public function coordinates(Request $request, ErbAddressLookup $lookup): JsonResponse
    {
        $this->authorize('create', Erb::class);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $address = $lookup->fromCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        if ($address === null) {
            return response()->json(['message' => 'Não foi possível localizar o endereço.'], 404);
        }

        return response()->json($address);
    }
}
