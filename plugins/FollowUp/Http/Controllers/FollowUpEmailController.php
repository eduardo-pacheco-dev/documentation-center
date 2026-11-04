<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Plugins\FollowUp\Http\Requests\SendFollowUpEmailRequest;
use Plugins\FollowUp\Mail\FollowUpMail;
use Plugins\FollowUp\Mail\FollowUpTableMail;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Support\FollowUpFilters;

class FollowUpEmailController extends Controller
{
    /**
     * Queue the given follow-up to be sent by e-mail.
     */
    public function send(SendFollowUpEmailRequest $request, FollowUp $followUp): RedirectResponse
    {
        $subject = $request->string('subject')->trim()->toString();

        Mail::to($request->recipients())
            ->queue(new FollowUpMail($followUp, $subject !== '' ? $subject : null));

        return back()->with('status', 'E-mail do follow-up enfileirado para envio.');
    }

    /**
     * Queue the follow-up table matching the current filters.
     */
    public function sendTable(SendFollowUpEmailRequest $request): RedirectResponse
    {
        $subject = $request->string('subject')->trim()->toString();

        $followUps = FollowUpFilters::apply(
            FollowUp::query()->with(['assignee', 'creator']),
            $request,
        )
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        Mail::to($request->recipients())
            ->queue(new FollowUpTableMail($followUps, $subject !== '' ? $subject : null));

        return redirect()
            ->route('admin.follow-ups.index', $request->only(['search', 'status', 'priority', 'assigned', 'view', 'sort', 'direction']))
            ->with('status', 'E-mail da tabela de follow-ups enfileirado para envio.');
    }
}
