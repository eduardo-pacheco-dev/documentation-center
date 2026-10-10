<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreErbCommentRequest;
use App\Models\Comment;
use App\Models\Erb;
use Illuminate\Http\RedirectResponse;

class ErbCommentController extends Controller
{
    /**
     * Add a comment to the ERB.
     */
    public function store(StoreErbCommentRequest $request, Erb $erb): RedirectResponse
    {
        $erb->comments()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Comentário adicionado com sucesso.');
    }

    /**
     * Remove a comment from the ERB.
     */
    public function destroy(Erb $erb, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $erb);

        abort_unless($comment->erb_id === $erb->getKey(), 404);

        $comment->delete();

        return back()->with('status', 'Comentário removido com sucesso.');
    }
}
