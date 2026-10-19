<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderCommentRequest;
use App\Models\Comment;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class WorkOrderCommentController extends Controller
{
    /**
     * Store a comment, with its images, on the work order.
     */
    public function store(StoreWorkOrderCommentRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $workOrder): void {
            $comment = $workOrder->comments()->create([
                'user_id' => $request->user()->getKey(),
                'body' => $request->validated('body') ?? '',
            ]);

            foreach ($request->file('images') ?? [] as $file) {
                $comment->documents()->create([
                    'user_id' => $request->user()->getKey(),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $file->store('documents'),
                    'disk' => 'local',
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        });

        return back()->with('status', 'Comentário adicionado com sucesso.');
    }

    /**
     * Remove a comment and its images from the work order.
     */
    public function destroy(WorkOrder $workOrder, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        abort_unless($comment->work_order_id === $workOrder->getKey(), 404);

        DB::transaction(function () use ($comment): void {
            $comment->documents()->delete();
            $comment->delete();
        });

        return back()->with('status', 'Comentário removido com sucesso.');
    }
}
