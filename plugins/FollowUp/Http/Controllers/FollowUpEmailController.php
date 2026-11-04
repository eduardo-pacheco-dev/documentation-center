<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Plugins\FollowUp\Http\Requests\SendFollowUpEmailRequest;
use Plugins\FollowUp\Mail\FollowUpMail;
use Plugins\FollowUp\Models\FollowUp;

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
}
