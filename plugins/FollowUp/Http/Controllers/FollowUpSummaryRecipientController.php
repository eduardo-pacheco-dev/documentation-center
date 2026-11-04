<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\FollowUp\Http\Requests\StoreFollowUpSummaryRecipientRequest;
use Plugins\FollowUp\Models\FollowUpSummaryRecipient;

class FollowUpSummaryRecipientController extends Controller
{
    /**
     * Register an address that receives a copy of the scheduled summary.
     */
    public function store(StoreFollowUpSummaryRecipientRequest $request): RedirectResponse
    {
        FollowUpSummaryRecipient::query()->create([
            'name' => $request->validated('summary_name'),
            'email' => $request->validated('summary_email'),
        ]);

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Cópia do resumo agendado adicionada com sucesso.');
    }

    /**
     * Remove an address from the copy list.
     */
    public function destroy(FollowUpSummaryRecipient $summaryRecipient): RedirectResponse
    {
        $summaryRecipient->delete();

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Cópia do resumo agendado removida com sucesso.');
    }
}
