<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\FollowUp\Http\Requests\StoreFollowUpUpdateRequest;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpUpdate;

class FollowUpUpdateController extends Controller
{
    /**
     * Add an update to the follow-up timeline.
     */
    public function store(StoreFollowUpUpdateRequest $request, FollowUp $followUp): RedirectResponse
    {
        $followUp->updates()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Atualização registrada com sucesso.');
    }

    /**
     * Remove an update from the timeline.
     */
    public function destroy(Request $request, FollowUp $followUp, FollowUpUpdate $update): RedirectResponse
    {
        abort_unless($update->follow_up_id === $followUp->getKey(), 404);

        $user = $request->user();

        abort_unless($user->is_admin || $update->user_id === $user->getKey(), 403);

        $update->delete();

        return back()->with('status', 'Atualização removida com sucesso.');
    }
}
