<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\Stations\Http\Requests\StoreStationCommentRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationComment;

class StationCommentController extends Controller
{
    /**
     * Add a comment to the given station.
     */
    public function store(StoreStationCommentRequest $request, Station $station): RedirectResponse
    {
        $station->comments()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Comentário adicionado.');
    }

    /**
     * Remove a comment from the given station.
     */
    public function destroy(Request $request, Station $station, StationComment $comment): RedirectResponse
    {
        $this->authorize('update', $station);

        abort_unless($comment->station_id === $station->getKey(), 404);
        abort_unless($comment->user_id === $request->user()->getKey(), 403);

        $comment->delete();

        return back()->with('status', 'Comentário removido.');
    }
}
