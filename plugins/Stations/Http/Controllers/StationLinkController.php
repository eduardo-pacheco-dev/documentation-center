<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\Stations\Enums\StationLinkStatus;
use Plugins\Stations\Http\Requests\StoreStationLinkRequest;
use Plugins\Stations\Http\Requests\UpdateStationLinkRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationLink;

class StationLinkController extends Controller
{
    /**
     * Create a link between the given station and another one.
     */
    public function store(StoreStationLinkRequest $request, Station $station): RedirectResponse
    {
        $data = $request->validated();

        $station->linksAsEndA()->create([
            ...$data,
            'user_id' => $request->user()->getKey(),
            'code' => $data['code'] ?? $this->generateCode($request->user()),
            'status' => $data['status'] ?? StationLinkStatus::Planned->value,
        ]);

        return back()->with('status', 'Enlace criado com sucesso.');
    }

    /**
     * Update the given link.
     */
    public function update(UpdateStationLinkRequest $request, Station $station, StationLink $link): RedirectResponse
    {
        $link->update([
            ...$request->validated(),
            'status' => $request->validated('status') ?? $link->status?->value ?? StationLinkStatus::Planned->value,
        ]);

        return back()->with('status', 'Enlace atualizado com sucesso.');
    }

    /**
     * Remove the given link.
     */
    public function destroy(Request $request, Station $station, StationLink $link): RedirectResponse
    {
        $this->authorize('update', $station);

        abort_unless(
            $link->user_id === $request->user()->getKey()
                && in_array($station->getKey(), [$link->station_a_id, $link->station_b_id], true),
            404,
        );

        $link->delete();

        return back()->with('status', 'Enlace removido.');
    }

    /**
     * Generate a sequential link code for the user.
     */
    private function generateCode(User $user): string
    {
        $query = StationLink::query()->where('user_id', $user->getKey())->withTrashed();
        $sequence = (clone $query)->count() + 1;

        do {
            $code = 'LNK-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ((clone $query)->where('code', $code)->exists());

        return $code;
    }
}
