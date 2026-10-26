<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

/**
 * @return array<int, array<string, mixed>>
 */
function publicFormSteps(): array
{
    return [
        [
            'id' => 'S1',
            'title' => 'Identificação',
            'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => true, 'value' => '', 'status' => 'pendente'],
                ['id' => 'F1-2', 'type' => 'number', 'label' => 'Código SMP', 'required' => false, 'value' => '', 'status' => 'pendente'],
                ['id' => 'F1-3', 'type' => 'date', 'label' => 'Data da vistoria', 'required' => true, 'value' => '', 'status' => 'pendente'],
            ],
        ],
        [
            'id' => 'S2',
            'title' => 'Conformidade',
            'fields' => [
                ['id' => 'F2-1', 'type' => 'radio', 'label' => 'Parecer', 'required' => true, 'options' => ['Conforme', 'Não conforme'], 'value' => '', 'status' => 'pendente'],
                ['id' => 'F2-2', 'type' => 'checkbox', 'label' => 'Itens verificados', 'required' => false, 'options' => ['Aterramento', 'Cabos'], 'value' => '', 'status' => 'pendente'],
                ['id' => 'F2-3', 'type' => 'scale', 'label' => 'Grau de conservação', 'required' => false, 'scaleMin' => 1, 'scaleMax' => 5, 'value' => '', 'status' => 'pendente'],
                ['id' => 'F2-4', 'type' => 'dropdown', 'label' => 'Tipo de estrutura', 'required' => false, 'options' => ['Torre', 'Poste'], 'value' => '', 'status' => 'pendente'],
                ['id' => 'F2-5', 'type' => 'image', 'label' => 'Foto do painel', 'required' => false, 'images' => [], 'status' => 'pendente'],
            ],
        ],
    ];
}

it('shows the public form filling page without authentication', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('Formulário externo')
        ->assertSee($form->code)
        ->assertSee('Área registrada')
        ->assertSee('Enviar respostas')
        ->assertSee('name="answers[F1-1]"', false);
});

it('rejects a public form link without a valid signature', function () {
    $form = Form::factory()->create();

    $this->get(route('public.forms.fill', $form))->assertForbidden();
});

it('stores the answers submitted through the public link', function () {
    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $response = $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-2' => '12345',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-2' => ['Aterramento', 'Cabos'],
            'F2-3' => '4',
            'F2-4' => 'Torre',
        ],
    ]);

    $response->assertRedirect();

    expect($response->headers->get('Location'))
        ->toContain('/formulario/'.$form->getKey());

    $form->refresh();

    expect($form->status)->toBe('Preenchido');
    expect($form->steps[0]['fields'][0]['value'])->toBe('Galpão A');
    expect($form->steps[0]['fields'][0]['status'])->toBe('enviado');
    expect($form->steps[0]['fields'][2]['value'])->toBe('2026-10-25');
    expect($form->steps[1]['fields'][0]['value'])->toBe('Conforme');
    expect($form->steps[1]['fields'][1]['value'])->toBe('Aterramento, Cabos');
    expect($form->steps[1]['fields'][2]['value'])->toBe('4');
    expect($form->steps[1]['fields'][3]['value'])->toBe('Torre');
});

it('requires the required fields before submitting', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => ['F1-2' => '12345'],
    ])->assertSessionHasErrors(['answers.F1-1', 'answers.F1-3', 'answers.F2-1']);

    expect($form->fresh()->steps)->toBe(publicFormSteps());
});

it('rejects choices that are not part of the field options', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Talvez',
            'F2-2' => ['Aterramento', 'Item inválido'],
            'F2-3' => '9',
            'F2-4' => 'Torre',
        ],
    ])->assertSessionHasErrors([
        'answers.F2-1',
        'answers.F2-2.1',
        'answers.F2-3',
    ]);
});

it('stores the images uploaded through the public link', function () {
    Storage::fake('public');

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-5' => [UploadedFile::fake()->image('painel.jpg')],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $form->refresh();
    $images = $form->steps[1]['fields'][4]['images'];

    expect($images)->toHaveCount(1);
    expect($images[0])->toContain('/storage/forms/'.$form->getKey().'/');
    expect($form->steps[1]['fields'][4]['status'])->toBe('enviado');

    Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($images[0]));
});

it('stores the files uploaded through the public link', function () {
    Storage::fake('public');

    $steps = publicFormSteps();
    $steps[1]['fields'][] = ['id' => 'F2-6', 'type' => 'file', 'label' => 'Laudo técnico', 'required' => false, 'files' => [], 'status' => 'pendente'];

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => $steps]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-6' => [UploadedFile::fake()->create('laudo.pdf', 120, 'application/pdf')],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $form->refresh();
    $field = $form->steps[1]['fields'][5];

    expect($field['files'])->toHaveCount(1);
    expect($field['files'][0])->toContain('/storage/forms/'.$form->getKey().'/');
    expect($field['fileNames'])->toBe(['laudo.pdf']);
    expect($field['status'])->toBe('enviado');

    Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($field['files'][0]));
});

it('requires a file when the file field is mandatory and none exists', function () {
    $steps = publicFormSteps();
    $steps[1]['fields'][] = ['id' => 'F2-6', 'type' => 'file', 'label' => 'Laudo técnico', 'required' => true, 'files' => [], 'status' => 'pendente'];

    $form = Form::factory()->create(['steps' => $steps]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertSessionHasErrors('answers.F2-6');
});

it('requires an image when the image field is mandatory and none exists', function () {
    $steps = publicFormSteps();
    $steps[1]['fields'][4]['required'] = true;

    $form = Form::factory()->create(['steps' => $steps]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertSessionHasErrors('answers.F2-5');
});

it('keeps an approved form status after a new public submission', function () {
    $form = Form::factory()->create(['status' => 'Aprovado', 'steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão B',
            'F1-3' => '2026-10-26',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    expect($form->fresh()->status)->toBe('Aprovado');
});

it('does not expose the public form through an unsigned post route', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->post(route('public.forms.submit', $form), [
        'answers' => ['F1-1' => 'Galpão A'],
    ])->assertForbidden();

    expect($form->fresh()->steps)->toBe(publicFormSteps());
});
