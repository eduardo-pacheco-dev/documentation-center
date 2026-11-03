<?php

namespace App\Http\Controllers\Admin;

use App\Dashboard\Widget;
use App\Dashboard\WidgetRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDashboardWidgetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the administration dashboard.
     */
    public function index(Request $request, WidgetRegistry $widgets): View
    {
        $user = $request->user();
        $availableWidgets = $widgets->forRequest($request);
        $hiddenWidgets = $user->hiddenDashboardWidgets();
        $editing = $request->boolean('edit');

        $isHidden = fn (Widget $widget): bool => in_array($widget->id(), $hiddenWidgets, true);

        return view('admin.dashboard', [
            'greeting' => $this->greeting($user),
            'widgets' => $availableWidgets->reject($isHidden)->values(),
            'editing' => $editing,
            'hiddenWidgets' => $editing
                ? $availableWidgets->filter($isHidden)->values()
                : collect(),
        ]);
    }

    /**
     * Show or hide a dashboard widget for the authenticated user.
     */
    public function updateWidget(
        UpdateDashboardWidgetRequest $request,
        WidgetRegistry $widgets,
        string $widget,
    ): RedirectResponse {
        $available = $widgets->forRequest($request)
            ->map(fn (Widget $available): string => $available->id());

        abort_unless($available->contains($widget), 404);

        $user = $request->user();
        $hidden = $user->hiddenDashboardWidgets();

        $hidden = $request->boolean('hidden')
            ? [...$hidden, $widget]
            : array_values(array_diff($hidden, [$widget]));

        $user->update([
            'dashboard_hidden_widgets' => array_values(array_unique($hidden)),
        ]);

        return redirect()
            ->route('admin.dashboard', ['edit' => 1])
            ->with(
                'status',
                $request->boolean('hidden')
                    ? 'Widget removido do painel.'
                    : 'Widget adicionado ao painel.',
            );
    }

    /**
     * Build a time based greeting for the user.
     */
    private function greeting(User $user): string
    {
        $greeting = match (true) {
            now()->hour < 12 => 'Bom dia',
            now()->hour < 18 => 'Boa tarde',
            default => 'Boa noite',
        };

        return $greeting.', '.str($user->name)->before(' ').'!';
    }
}
