<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRadioLinkCommentRequest;
use App\Models\Comment;
use App\Models\RadioLink;
use Illuminate\Http\RedirectResponse;

class RadioLinkCommentController extends Controller
{
    /**
     * Add a comment to the radio link.
     */
    public function store(StoreRadioLinkCommentRequest $request, RadioLink $radioLink): RedirectResponse
    {
        $radioLink->comments()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Comentário adicionado com sucesso.');
    }

    /**
     * Remove a comment from the radio link.
     */
    public function destroy(RadioLink $radioLink, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $radioLink);

        abort_unless($comment->radio_link_id === $radioLink->getKey(), 404);

        $comment->delete();

        return back()->with('status', 'Comentário removido com sucesso.');
    }
}
