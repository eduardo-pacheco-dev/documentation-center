<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\FollowUp\Http\Requests\StoreFollowUpAssigneeRequest;
use Plugins\FollowUp\Models\FollowUpAssignee;

class FollowUpAssigneeController extends Controller
{
    /**
     * Register a person available for assignment.
     */
    public function store(StoreFollowUpAssigneeRequest $request): RedirectResponse
    {
        FollowUpAssignee::query()->create($request->validated());

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Responsável adicionado com sucesso.');
    }

    /**
     * Remove a person from the available list.
     */
    public function destroy(FollowUpAssignee $assignee): RedirectResponse
    {
        $assignee->delete();

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Responsável removido com sucesso.');
    }
}
