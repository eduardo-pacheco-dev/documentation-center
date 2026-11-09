<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteCommentRequest;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteComment;

class NokiaSiteCommentController extends Controller
{
    /**
     * Store a comment about the given site.
     */
    public function store(StoreNokiaSiteCommentRequest $request, NokiaSite $site): RedirectResponse
    {
        $site->comments()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Comentário publicado com sucesso.');
    }

    /**
     * Remove the given comment.
     */
    public function destroy(Request $request, NokiaSite $site, NokiaSiteComment $comment): RedirectResponse
    {
        abort_unless($comment->nokia_site_id === $site->getKey(), 404);

        $user = $request->user();

        abort_unless($user->is_admin || $comment->user_id === $user->getKey(), 403);

        $comment->delete();

        return back()->with('status', 'Comentário removido com sucesso.');
    }
}
