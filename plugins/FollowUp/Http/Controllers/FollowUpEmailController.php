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
use Throwable;

class FollowUpEmailController extends Controller
{
    /**
     * Send the given follow-up by e-mail.
     */
    public function send(SendFollowUpEmailRequest $request, FollowUp $followUp): RedirectResponse
    {
        $subject = $request->string('subject')->trim()->toString();
        $recipients = $request->recipients();

        try {
            Mail::to($recipients)->send(new FollowUpMail($followUp, $subject !== '' ? $subject : null));
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', 'Falha ao enviar o e-mail: '.$exception->getMessage());
        }

        return back()->with(
            'status',
            'Follow-up enviado por e-mail para '.implode(', ', $recipients).'.',
        );
    }

    /**
     * Send the follow-up table matching the current filters.
     */
    public function sendTable(SendFollowUpEmailRequest $request): RedirectResponse
    {
        $subject = $request->string('subject')->trim()->toString();
        $recipients = $request->recipients();

        $followUps = FollowUpFilters::apply(
            FollowUp::query()->with(['assignee', 'creator']),
            $request,
        )
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        try {
            Mail::to($recipients)->send(new FollowUpTableMail($followUps, $subject !== '' ? $subject : null));
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.follow-ups.index', $request->only(['search', 'status', 'priority', 'assigned', 'view', 'sort', 'direction']))
                ->with('error', 'Falha ao enviar a tabela de follow-ups: '.$exception->getMessage());
        }

        return redirect()
            ->route('admin.follow-ups.index', $request->only(['search', 'status', 'priority', 'assigned', 'view', 'sort', 'direction']))
            ->with('status', 'Tabela de follow-ups enviada por e-mail para '.implode(', ', $recipients).'.');
    }
}
