<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormEvaluationRequest;
use App\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FormEvaluationController extends Controller
{
    /**
     * Show the response evaluation page for a form.
     */
    public function create(Form $form): View
    {
        $this->authorize('evaluate', $form);

        $form->load('project:id,code,name', 'user:id,name');

        return view('admin.forms.evaluate', [
            'form' => $form,
            'steps' => $form->steps ?? [],
            'evaluations' => $form->evaluations()->with('user:id,name')->latest()->get(),
        ]);
    }

    /**
     * Record an evaluation of the form response.
     */
    public function store(StoreFormEvaluationRequest $request, Form $form): RedirectResponse
    {
        $attributes = $request->validated();

        $steps = $this->applyImageDecisions($form->steps ?? [], $request->input('image_decisions_json'));

        $form->update([
            'status' => $attributes['status'],
            'steps' => $this->applyDecisions($steps, $attributes['decisions'] ?? []),
        ]);

        $form->evaluations()->create([
            'user_id' => $request->user()->getKey(),
            'status' => $attributes['status'],
            'notes' => $attributes['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.forms.evaluate', $form)
            ->with('status', 'Avaliação registrada com sucesso.');
    }

    /**
     * Apply the per-image decisions (approve/reject) to the stored form structure.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array<int, array<string, mixed>>
     */
    private function applyImageDecisions(array $steps, ?string $json): array
    {
        $decisions = json_decode((string) $json, true);

        if (! is_array($decisions)) {
            return $steps;
        }

        foreach ($steps as $stepIndex => $step) {
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                $fieldId = $field['id'] ?? null;
                $images = $field['images'] ?? [];

                if ($fieldId === null || $images === [] || ! is_array($decisions[$fieldId] ?? null)) {
                    continue;
                }

                $map = [];

                foreach ($decisions[$fieldId] as $imageIndex => $decision) {
                    if (array_key_exists($imageIndex, $images) && in_array($decision, ['aprovado', 'reprovado'], true)) {
                        $map[(int) $imageIndex] = $decision;
                    }
                }

                if ($map !== []) {
                    $steps[$stepIndex]['fields'][$fieldIndex]['imageDecisions'] = $map;
                }
            }
        }

        return $steps;
    }

    /**
     * Apply the per-field decisions to the stored form structure.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @param  array<string, string|null>  $decisions
     * @return array<int, array<string, mixed>>
     */
    private function applyDecisions(array $steps, array $decisions): array
    {
        if ($decisions === []) {
            return $steps;
        }

        foreach ($steps as $stepIndex => $step) {
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                $fieldId = $field['id'] ?? null;
                $decision = $fieldId !== null ? ($decisions[$fieldId] ?? null) : null;

                if (filled($decision)) {
                    $steps[$stepIndex]['fields'][$fieldIndex]['status'] = $decision;
                }
            }
        }

        return $steps;
    }
}
