<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AutoSaveFormEvaluationRequest;
use App\Http\Requests\Admin\StoreFormEvaluationRequest;
use App\Models\Form;
use App\Models\FormEvaluation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
        $this->commitEvaluation($form, $request->user(), $request->validated(), $request->input('image_decisions_json'));

        return redirect()
            ->route('admin.forms.evaluate', $form)
            ->with('status', 'Avaliação registrada com sucesso.');
    }

    /**
     * Persist the evaluation draft and record a new evaluation for every final change.
     */
    public function autosave(AutoSaveFormEvaluationRequest $request, Form $form): JsonResponse
    {
        $evaluation = $this->commitEvaluation($form, $request->user(), $request->validated(), $request->input('image_decisions_json'));

        if ($evaluation !== null) {
            $evaluation->setRelation('user', $request->user());
        }

        return response()->json([
            'saved_at' => now()->format('H:i'),
            'form_status' => $form->status,
            'evaluation_html' => $evaluation !== null
                ? view('admin.forms.partials.evaluation-item', ['evaluation' => $evaluation])->render()
                : null,
        ]);
    }

    /**
     * Apply the payload to the form and record an evaluation when the outcome is final.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function commitEvaluation(Form $form, User $user, array $attributes, ?string $imageDecisionsJson): ?FormEvaluation
    {
        $stepsBefore = $form->steps ?? [];
        $changes = $this->buildChanges($stepsBefore, $attributes, $imageDecisionsJson);

        $steps = $this->applyDecisions(
            $this->applyImageDecisions($stepsBefore, $imageDecisionsJson),
            $attributes['decisions'] ?? [],
        );

        $status = $attributes['status'] ?? null;
        $notes = $attributes['notes'] ?? null;
        $isFinal = $this->isFinal($status, $notes);

        $form->update([
            'status' => $isFinal ? $status : $form->status,
            'steps' => $steps,
        ]);

        if (! $isFinal) {
            return null;
        }

        return $form->evaluations()->create([
            'user_id' => $user->getKey(),
            'status' => $status,
            'notes' => $notes,
            'changes' => $changes,
        ]);
    }

    /**
     * Build the list of evaluated field and image changes contained in the payload.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @param  array<string, mixed>  $attributes
     * @return array<int, array<string, mixed>>
     */
    private function buildChanges(array $steps, array $attributes, ?string $imageDecisionsJson): array
    {
        $fields = collect($steps)
            ->flatMap(fn (array $step) => $step['fields'] ?? [])
            ->keyBy('id');

        $changes = [];

        foreach ($attributes['decisions'] ?? [] as $fieldId => $decision) {
            $field = $fields->get($fieldId);

            if ($field === null || blank($decision)) {
                continue;
            }

            $from = $field['status'] ?? null;

            if ($from === $decision) {
                continue;
            }

            $changes[] = [
                'type' => 'field',
                'id' => $fieldId,
                'label' => $field['label'] ?? $fieldId,
                'from' => $from,
                'to' => $decision,
            ];
        }

        $imageDecisions = json_decode((string) $imageDecisionsJson, true);

        if (is_array($imageDecisions)) {
            foreach ($imageDecisions as $fieldId => $decisions) {
                $field = $fields->get($fieldId);

                if ($field === null || ! is_array($decisions)) {
                    continue;
                }

                foreach ($decisions as $index => $decision) {
                    if (! in_array($decision, ['aprovado', 'reprovado'], true)) {
                        continue;
                    }

                    $from = $field['imageDecisions'][$index] ?? null;

                    if ($from === $decision) {
                        continue;
                    }

                    $changes[] = [
                        'type' => 'image',
                        'id' => $fieldId,
                        'label' => $field['label'] ?? $fieldId,
                        'index' => (int) $index,
                        'from' => $from,
                        'to' => $decision,
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Determine whether the payload has a final outcome that can be recorded.
     */
    private function isFinal(?string $status, ?string $notes): bool
    {
        return $status === 'Aprovado' || ($status === 'Reprovado' && filled($notes));
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
