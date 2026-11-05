<?php

namespace App\Http\Controllers\Admin;

use App\Dashboard\Widget;
use App\Dashboard\WidgetRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDashboardWidgetLayoutRequest;
use App\Http\Requests\UpdateDashboardWidgetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $orderedWidgets = $this->applyOrder($availableWidgets, $user->dashboardWidgetOrder());
        $hiddenWidgets = $user->hiddenDashboardWidgets();
        $editing = $request->boolean('edit');

        $isHidden = fn (Widget $widget): bool => in_array($widget->id(), $hiddenWidgets, true);

        return view('admin.dashboard', [
            'greeting' => $this->greeting($user),
            'widgets' => $orderedWidgets->reject($isHidden)->values(),
            'editing' => $editing,
            'hiddenWidgets' => $editing
                ? $availableWidgets->filter($isHidden)->values()
                : collect(),
            'widgetSpans' => $user->dashboardWidgetSpans(),
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
     * Persist the widget order chosen through drag and drop.
     */
    public function updateWidgetOrder(
        UpdateDashboardWidgetLayoutRequest $request,
        WidgetRegistry $widgets,
    ): JsonResponse|RedirectResponse {
        $order = $this->normalizeOrder(
            $widgets->forRequest($request),
            (array) ($request->validated('order') ?? []),
        );

        $request->user()->update(['dashboard_widget_order' => $order]);

        if ($request->expectsJson()) {
            return response()->json(['order' => $order]);
        }

        return redirect()
            ->route('admin.dashboard', ['edit' => 1])
            ->with('status', 'Ordem dos widgets atualizada.');
    }

    /**
     * Resize a dashboard widget for the authenticated user.
     */
    public function updateWidgetLayout(
        UpdateDashboardWidgetLayoutRequest $request,
        WidgetRegistry $widgets,
        string $widget,
    ): RedirectResponse {
        $available = $widgets->forRequest($request);
        $widgetInstance = $available->firstWhere(fn (Widget $available): bool => $available->id() === $widget);

        abort_if($widgetInstance === null, 404);

        $user = $request->user();
        $spans = $user->dashboardWidgetSpans();
        $message = 'Widget atualizado.';

        if ($request->filled('span')) {
            $span = (int) $request->validated('span');

            if ($span === $widgetInstance->columnSpan()) {
                unset($spans[$widget]);
            } else {
                $spans[$widget] = $span;
            }

            $message = 'Largura do widget atualizada.';
        }

        $user->update(['dashboard_widget_spans' => $spans]);

        return redirect()
            ->route('admin.dashboard', ['edit' => 1])
            ->with('status', $message);
    }

    /**
     * Apply the order chosen by the user, appending new widgets at the end.
     *
     * @param  Collection<int, Widget>  $widgets
     * @param  list<string>  $order
     * @return Collection<int, Widget>
     */
    private function applyOrder(Collection $widgets, array $order): Collection
    {
        $positions = array_flip($this->normalizeOrder($widgets, $order));

        return $widgets
            ->sortBy(fn (Widget $widget): int => $positions[$widget->id()])
            ->values();
    }

    /**
     * Keep only available widgets, preserving the registry order for new ones.
     *
     * @param  Collection<int, Widget>  $widgets
     * @param  list<string>  $order
     * @return list<string>
     */
    private function normalizeOrder(Collection $widgets, array $order): array
    {
        $available = $widgets->map(fn (Widget $widget): string => $widget->id())->all();
        $ordered = array_values(array_intersect($order, $available));

        return array_merge($ordered, array_values(array_diff($available, $ordered)));
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
