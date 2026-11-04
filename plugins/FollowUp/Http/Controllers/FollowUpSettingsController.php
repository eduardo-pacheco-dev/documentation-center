<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Plugins\FollowUp\Http\Requests\UpdateFollowUpSettingsRequest;
use Plugins\FollowUp\Models\FollowUpSetting;

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
}
