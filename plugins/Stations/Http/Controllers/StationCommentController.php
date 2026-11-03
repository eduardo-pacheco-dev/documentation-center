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
     * Store a comment about the station.
     */
    public function store(StoreStationCommentRequest $request, Station $station): RedirectResponse
    {
        $station->comments()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Comentário publicado com sucesso.');
    }

    /**
     * Remove the given comment.
     */
    public function destroy(Request $request, Station $station, StationComment $comment): RedirectResponse
    {
        abort_unless($comment->station_id === $station->getKey(), 404);

        $user = $request->user();

        abort_unless($user->is_admin || $comment->user_id === $user->getKey(), 403);

        $comment->delete();

        return back()->with('status', 'Comentário removido com sucesso.');
    }
}
