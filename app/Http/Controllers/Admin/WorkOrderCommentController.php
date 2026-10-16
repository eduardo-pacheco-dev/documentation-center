<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderCommentRequest;
use App\Models\Comment;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;

class WorkOrderCommentController extends Controller
{
    /**
     * Add a comment to the work order.
     */
    public function store(StoreWorkOrderCommentRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $workOrder->comments()->create([
            'user_id' => $request->user()->getKey(),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Comentário adicionado com sucesso.');
    }

    /**
     * Remove a comment from the work order.
     */
    public function destroy(WorkOrder $workOrder, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        abort_unless($comment->work_order_id === $workOrder->getKey(), 404);

        $comment->delete();

        return back()->with('status', 'Comentário removido com sucesso.');
    }
}
