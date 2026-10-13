<?php

use App\Models\Form;
use App\Models\FormEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<int, array<string, mixed>>
 */
function evaluationSteps(): array
{
    return [
        [
            'id' => 'S1',
            'title' => 'Identificação',
            'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => true, 'value' => 'Galpão A', 'status' => 'envio_pendente'],
                ['id' => 'F1-2', 'type' => 'image', 'label' => 'Foto', 'required' => false, 'images' => ['https://example.com/foto.jpg'], 'status' => 'enviado'],
            ],
        ],
    ];
}

it('requires an authenticated user to open the evaluation page', function () {
    $form = Form::factory()->create();

    $this->get(route('admin.forms.evaluate', $form))->assertRedirect('/login');
});

it('shows the evaluation page with answers and per-field controls', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.forms.evaluate', $form))
        ->assertOk()
        ->assertSee('Avaliação da resposta')
        ->assertSee('Área registrada')
        ->assertSee('Galpão A')
        ->assertSee('name="decisions[F1-1]"', false)
        ->assertSee('name="image_decisions_json"', false)
        ->assertSee('Registrar avaliação');
});

it('lets any authenticated user evaluate a form they do not own', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.forms.evaluate', $form))
        ->assertOk();
});

it('records an evaluation and applies the per-field decisions', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $this->actingAs($user)
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'notes' => 'Tudo conforme.',
            'decisions' => [
                'F1-1' => 'aprovado',
                'F1-2' => 'reprovado',
            ],
        ])
        ->assertRedirect(route('admin.forms.evaluate', $form));

    $form->refresh();

    expect($form->status)->toBe('Aprovado');
    expect($form->steps[0]['fields'][0]['status'])->toBe('aprovado');
    expect($form->steps[0]['fields'][1]['status'])->toBe('reprovado');

    $this->assertDatabaseHas('form_evaluations', [
        'form_id' => $form->getKey(),
        'user_id' => $user->getKey(),
        'status' => 'Aprovado',
        'notes' => 'Tudo conforme.',
    ]);
});

it('records per-image approval decisions', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $this->actingAs($user)
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'image_decisions_json' => json_encode(['F1-2' => ['0' => 'reprovado']]),
        ])
        ->assertRedirect(route('admin.forms.evaluate', $form));

    expect($form->fresh()->steps[0]['fields'][1]['imageDecisions'])->toBe([0 => 'reprovado']);
});

it('ignores image decisions for unknown fields or images', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'image_decisions_json' => json_encode([
                'ZZ-99' => ['0' => 'aprovado'],
                'F1-2' => ['5' => 'aprovado'],
            ]),
        ])
        ->assertRedirect();

    expect($form->fresh()->steps)->toBe(evaluationSteps());
});

it('rejects a malformed image decisions payload', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'image_decisions_json' => 'not-json',
        ])
        ->assertSessionHasErrors('image_decisions_json');
});

it('keeps the answers when applying the evaluation', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'decisions' => ['F1-1' => 'aprovado'],
        ])
        ->assertRedirect();

    $field = $form->fresh()->steps[0]['fields'][0];

    expect($field['value'])->toBe('Galpão A');
    expect($field['label'])->toBe('Área registrada');

    expect($form->fresh()->steps[0]['fields'][1]['status'])->toBe('enviado');
});

it('requires a justification when rejecting the form', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), ['status' => 'Reprovado'])
        ->assertSessionHasErrors('notes');

    $this->assertDatabaseCount('form_evaluations', 0);
});

it('records a rejection together with its justification', function () {
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Reprovado',
            'notes' => 'Fotos ilegíveis.',
        ])
        ->assertRedirect(route('admin.forms.evaluate', $form));

    expect($form->fresh()->status)->toBe('Reprovado');

    $this->assertDatabaseHas('form_evaluations', [
        'form_id' => $form->getKey(),
        'status' => 'Reprovado',
        'notes' => 'Fotos ilegíveis.',
    ]);
});

it('requires an overall status to record an evaluation', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'notes' => 'Sem status.',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseCount('form_evaluations', 0);
});

it('rejects an invalid overall status', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), ['status' => 'Talvez'])
        ->assertSessionHasErrors('status');
});

it('rejects an invalid per-field decision', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'decisions' => ['F1-1' => 'talvez'],
        ])
        ->assertSessionHasErrors('decisions.F1-1');
});

it('ignores decisions for fields that are not part of the form', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.forms.evaluate', $form), [
            'status' => 'Aprovado',
            'decisions' => ['ZZ-99' => 'aprovado'],
        ])
        ->assertRedirect();

    expect($form->fresh()->steps)->toBe(evaluationSteps());
});

it('shows the evaluation history', function () {
    $evaluator = User::factory()->create(['name' => 'Avaliador Chefe']);
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    FormEvaluation::factory()->for($form)->create([
        'user_id' => $evaluator->getKey(),
        'status' => 'Reprovado',
        'notes' => 'Foto ilegível.',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.forms.evaluate', $form))
        ->assertOk()
        ->assertSee('Histórico de avaliações')
        ->assertSee('Avaliador Chefe')
        ->assertSee('Foto ilegível.');
});

it('links to the evaluation page from the form detail', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.forms.show', $form))
        ->assertOk()
        ->assertSee(route('admin.forms.evaluate', $form), false);
});

it('requires an authenticated user to auto-save an evaluation', function () {
    $form = Form::factory()->create();

    $this->post(route('admin.forms.evaluations.autosave', $form), ['status' => 'Aprovado'])
        ->assertRedirect('/login');
});

it('auto-saves the evaluation draft before a final outcome is chosen', function () {
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('admin.forms.evaluations.autosave', $form), [
            'decisions' => ['F1-1' => 'aprovado'],
        ])
        ->assertOk()
        ->assertJsonPath('evaluation_html', null);

    $form->refresh();

    expect($form->status)->toBe('Preenchido');
    expect($form->steps[0]['fields'][0]['status'])->toBe('aprovado');

    $this->assertDatabaseCount('form_evaluations', 0);
});

it('records a new evaluation on every auto-save with a final outcome', function () {
    $user = User::factory()->create(['name' => 'Avaliador Chefe']);
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $first = $this->actingAs($user)->postJson(route('admin.forms.evaluations.autosave', $form), [
        'status' => 'Aprovado',
        'notes' => 'Tudo conforme.',
        'decisions' => ['F1-1' => 'aprovado'],
    ]);

    $first->assertOk()->assertJsonPath('form_status', 'Aprovado');

    expect($first->json('evaluation_html'))->toBeString()->toContain('Avaliador Chefe');

    $this->actingAs($user)->postJson(route('admin.forms.evaluations.autosave', $form), [
        'status' => 'Aprovado',
        'decisions' => ['F1-2' => 'reprovado'],
    ])->assertOk();

    $this->assertDatabaseCount('form_evaluations', 2);

    $form->refresh();

    expect($form->steps[0]['fields'][0]['status'])->toBe('aprovado');
    expect($form->steps[0]['fields'][1]['status'])->toBe('reprovado');
});

it('auto-saves per-image decisions', function () {
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('admin.forms.evaluations.autosave', $form), [
            'status' => 'Aprovado',
            'image_decisions_json' => json_encode(['F1-2' => ['0' => 'reprovado']]),
        ])
        ->assertOk();

    expect($form->fresh()->steps[0]['fields'][1]['imageDecisions'])->toBe([0 => 'reprovado']);
});

it('does not record a rejection auto-save without a justification', function () {
    $form = Form::factory()->create(['status' => 'Preenchido', 'steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('admin.forms.evaluations.autosave', $form), ['status' => 'Reprovado'])
        ->assertOk()
        ->assertJsonPath('evaluation_html', null);

    expect($form->fresh()->status)->toBe('Preenchido');

    $this->assertDatabaseCount('form_evaluations', 0);
});

it('rejects an invalid auto-save payload', function () {
    $form = Form::factory()->create(['steps' => evaluationSteps()]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('admin.forms.evaluations.autosave', $form), [
            'status' => 'Talvez',
            'image_decisions_json' => 'not-json',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status', 'image_decisions_json']);
});
