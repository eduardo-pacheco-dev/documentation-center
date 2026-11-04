<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Plugins\FollowUp\Http\Requests\SendFollowUpTestEmailRequest;
use Plugins\FollowUp\Http\Requests\UpdateFollowUpSettingsRequest;
use Plugins\FollowUp\Mail\FollowUpTestMail;
use Plugins\FollowUp\Models\FollowUpSetting;
use Throwable;

class FollowUpSettingsController extends Controller
{
    /**
     * Show the plugin settings page.
     */
    public function edit(): View
    {
        return view('follow-up::settings', [
            'settings' => FollowUpSetting::current(),
        ]);
    }

    /**
     * Update the plugin settings.
     */
    public function update(UpdateFollowUpSettingsRequest $request): RedirectResponse
    {
        $settings = FollowUpSetting::current();

        $data = $request->validated();
        $data['summary_times'] = implode(',', $request->summaryTimes());
        $data['mail_password'] = $request->filled('mail_password')
            ? $data['mail_password']
            : $settings->mail_password;

        $settings->update($data);

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Configurações do Follow-up atualizadas com sucesso.');
    }

    /**
     * Send a test message using the currently saved mail settings.
     */
    public function sendTestEmail(SendFollowUpTestEmailRequest $request): RedirectResponse
    {
        $recipient = $request->string('recipient')->trim()->toString();

        try {
            Mail::to($recipient)->send(new FollowUpTestMail);
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.follow-ups.settings')
                ->with('error', 'Não foi possível enviar o e-mail de teste: '.$exception->getMessage());
        }

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', "E-mail de teste enviado para {$recipient}.");
    }
}
