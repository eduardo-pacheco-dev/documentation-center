<?php

use App\Models\Form;
use App\Models\FormResponse;
use Illuminate\Contracts\Filesystem\Filesystem;
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
        ->assertSee('name="answers[F1-1]"', false)
        ->assertSee('name="camera_answers[F2-5][]"', false)
        ->assertSee('capture="environment"', false)
        ->assertSee('data-image-thumbnails', false)
        ->assertSee('data-image-empty', false)
        ->assertSee('data-image-limit-message', false)
        ->assertSee('15 MB por imagem')
        ->assertSee('Vazio');
});

it('shows a clear message when the upload exceeds the server limit', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->get(URL::signedRoute('public.forms.fill', [
        'form' => $form->getKey(),
        'upload_error' => 1,
    ]))
        ->assertOk()
        ->assertSee('maiores do que o servidor aceita');
});

it('redirects with an upload error when the request exceeds the server limit', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->call('POST', URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [], [], [], [
        'CONTENT_LENGTH' => 1024 * 1024 * 512,
    ])
        ->assertRedirect(URL::signedRoute('public.forms.fill', [
            'form' => $form->getKey(),
            'upload_error' => 1,
        ]));
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
    expect($form->responses()->count())->toBe(0);
});

it('keeps uploaded photos visible when the submission is incomplete', function () {
    Storage::fake('public');

    $steps = [[
        'id' => 'S1',
        'title' => 'Fotos antes da Instalação',
        'fields' => [
            ['id' => 'F1-1', 'type' => 'image', 'label' => 'Foto dos 4 lados do site', 'required' => true, 'images' => [], 'status' => 'pendente'],
            ['id' => 'F1-2', 'type' => 'text', 'label' => 'Volumes entregues', 'required' => true, 'value' => '', 'status' => 'pendente'],
            ['id' => 'F1-3', 'type' => 'file', 'label' => 'Inserir arquivo PDF da Nota Fiscal', 'required' => true, 'files' => [], 'status' => 'pendente'],
        ],
    ]];

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => $steps]);

    $this->from(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
            'answers' => [
                'F1-1' => [UploadedFile::fake()->image('foto-site.jpg')],
            ],
        ])
        ->assertRedirect(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertSessionHasErrors(['answers.F1-2', 'answers.F1-3']);

    $response = $form->responses()->first();

    expect($response)->not->toBeNull();
    expect($response->status)->toBe('Em preenchimento');
    expect($response->submitted_at)->toBeNull();
    expect($form->fresh()->status)->toBe('Pendente');

    $field = collect($response->steps[0]['fields'])->firstWhere('id', 'F1-1');

    expect($field['images'])->toHaveCount(1);
    expect($field['images'][0])->toStartWith('/storage/forms/'.$form->getKey().'/');
    Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($field['images'][0]));

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee($field['images'][0], false);
});

it('finishes an incomplete submission without uploading the photos again', function () {
    Storage::fake('public');

    $steps = [[
        'id' => 'S1',
        'title' => 'Fotos antes da Instalação',
        'fields' => [
            ['id' => 'F1-1', 'type' => 'image', 'label' => 'Foto dos 4 lados do site', 'required' => true, 'images' => [], 'status' => 'pendente'],
            ['id' => 'F1-2', 'type' => 'text', 'label' => 'Volumes entregues', 'required' => true, 'value' => '', 'status' => 'pendente'],
            ['id' => 'F1-3', 'type' => 'file', 'label' => 'Inserir arquivo PDF da Nota Fiscal', 'required' => true, 'files' => [], 'status' => 'pendente'],
        ],
    ]];

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => $steps]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => [UploadedFile::fake()->image('foto-site.jpg')],
        ],
    ])->assertSessionHasErrors(['answers.F1-2', 'answers.F1-3']);

    $draft = $form->responses()->firstOrFail();

    $this->withSession(['public.forms.draft.'.$form->getKey() => $draft->getKey()])
        ->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
            'answers' => [
                'F1-2' => '2 volumes',
                'F1-3' => [UploadedFile::fake()->create('nota-fiscal.pdf', 120, 'application/pdf')],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $form->refresh();
    $response = $form->responses()->firstOrFail();

    expect($form->responses()->count())->toBe(1);
    expect($response->is($draft))->toBeTrue();
    expect($response->status)->toBe('Preenchido');
    expect($response->submitted_at)->not->toBeNull();
    expect($form->status)->toBe('Preenchido');

    $field = collect($response->steps[0]['fields'])->firstWhere('id', 'F1-1');

    expect($field['images'])->toHaveCount(1);

    $file = collect($response->steps[0]['fields'])->firstWhere('id', 'F1-3');

    expect($file['files'])->toHaveCount(1);
    expect($file['fileNames'])->toBe(['nota-fiscal.pdf']);
});

it('saves newly selected images automatically as a draft', function () {
    Storage::fake('public');

    $form = Form::factory()->create([
        'status' => 'Pendente',
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $response = $this->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F2-5' => [UploadedFile::fake()->image('painel.jpg')],
        ],
    ], ['Accept' => 'application/json']);

    $response->assertOk()
        ->assertJsonPath('message', 'Imagens salvas automaticamente.')
        ->assertJsonStructure(['message', 'images' => ['F2-5'], 'saved_at']);

    $images = $response->json('images.F2-5');

    expect($images)->toHaveCount(1);
    expect($images[0])->toStartWith('/storage/forms/'.$form->getKey().'/');
    Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($images[0]));

    $draft = $form->responses()->firstOrFail();

    expect($draft->status)->toBe('Em preenchimento');
    expect($draft->submitted_at)->toBeNull();
    expect($draft->steps[0]['fields'][0]['value'])->toBe('Galpão A');
    expect(session('public.forms.draft.'.$form->getKey()))->toBe($draft->getKey());

    $field = collect($draft->steps[1]['fields'])->firstWhere('id', 'F2-5');

    expect($field['images'])->toBe($images);
    expect($field['status'])->toBe('enviado');
});

it('appends automatically saved images to the same draft', function () {
    Storage::fake('public');

    $form = Form::factory()->create([
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
        'answers' => ['F2-5' => [UploadedFile::fake()->image('frente.jpg')]],
    ], ['Accept' => 'application/json'])->assertOk();

    $draft = $form->responses()->firstOrFail();

    $this->withSession(['public.forms.draft.'.$form->getKey() => $draft->getKey()])
        ->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
            'answers' => ['F2-5' => [UploadedFile::fake()->image('lateral.jpg')]],
        ], ['Accept' => 'application/json'])
        ->assertOk();

    $draft->refresh();
    $field = collect($draft->steps[1]['fields'])->firstWhere('id', 'F2-5');

    expect($field['images'])->toHaveCount(2);
    expect($form->responses()->count())->toBe(1);
});

it('limits automatically saved images to five per field across saves', function () {
    Storage::fake('public');

    $form = Form::factory()->create([
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
        'answers' => ['F2-5' => [
            UploadedFile::fake()->image('foto-1.jpg'),
            UploadedFile::fake()->image('foto-2.jpg'),
            UploadedFile::fake()->image('foto-3.jpg'),
            UploadedFile::fake()->image('foto-4.jpg'),
        ]],
    ], ['Accept' => 'application/json'])->assertOk();

    $draft = $form->responses()->firstOrFail();

    $this->withSession(['public.forms.draft.'.$form->getKey() => $draft->getKey()])
        ->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
            'answers' => ['F2-5' => [
                UploadedFile::fake()->image('foto-5.jpg'),
                UploadedFile::fake()->image('foto-6.jpg'),
            ]],
        ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonPath('message', 'O campo "Foto do painel" aceita no máximo 5 imagens.');

    $field = collect($draft->fresh()->steps[1]['fields'])->firstWhere('id', 'F2-5');

    expect($field['images'])->toHaveCount(4);
});

it('rejects an invalid image in the automatic save', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
        'answers' => [
            'F2-5' => [UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf')],
        ],
    ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('answers.F2-5.0');

    expect($form->responses()->count())->toBe(0);
});

it('does not create a draft when the automatic save has no images', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), [
        'answers' => ['F1-1' => 'Galpão A'],
    ], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('message', 'Nenhuma imagem nova para salvar.');

    expect($form->responses()->count())->toBe(0);
});

it('does not expose the automatic save through an unsigned route', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->post(route('public.forms.autosave', $form), [
        'answers' => ['F2-5' => [UploadedFile::fake()->image('painel.jpg')]],
    ])->assertForbidden();

    expect($form->responses()->count())->toBe(0);
});

it('adds the automatic save to the public form', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('data-form-autosave-url', false)
        ->assertSee('data-draft-autosave-status', false)
        ->assertSee(URL::signedRoute('public.forms.autosave', ['form' => $form->getKey()]), false);
});

it('shows a public submission on the evaluation page', function () {
    Storage::fake('public');

    $steps = [[
        'id' => 'S1',
        'title' => 'Fotos antes da Instalação',
        'fields' => [
            ['id' => 'F1-1', 'type' => 'image', 'label' => 'Foto dos 4 lados do site', 'required' => true, 'images' => [], 'status' => 'pendente'],
            ['id' => 'F1-2', 'type' => 'text', 'label' => 'Volumes entregues', 'required' => true, 'value' => '', 'status' => 'pendente'],
        ],
    ]];

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => $steps]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => [UploadedFile::fake()->image('foto-site.jpg')],
            'F1-2' => '2 volumes',
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $image = $form->fresh()->steps[0]['fields'][0]['images'][0];

    $this->actingAs($form->user)
        ->get(route('admin.forms.evaluate', $form))
        ->assertOk()
        ->assertSee($image, false)
        ->assertSee('2 volumes');
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
    expect($images[0])->toStartWith('/storage/');
    expect($images[0])->toContain('/storage/forms/'.$form->getKey().'/');
    expect($form->steps[1]['fields'][4]['status'])->toBe('enviado');

    Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($images[0]));
});

it('stores multiple gallery images selected at once', function () {
    Storage::fake('public');

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-5' => [
                UploadedFile::fake()->image('frente.jpg'),
                UploadedFile::fake()->image('lateral.jpg'),
            ],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $images = $form->fresh()->steps[1]['fields'][4]['images'];

    expect($images)->toHaveCount(2);
    expect($form->fresh()->steps[1]['fields'][4]['status'])->toBe('enviado');
});

it('accepts photos larger than five megabytes', function () {
    Storage::fake('public');

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-5' => [UploadedFile::fake()->image('celular.jpg')->size(6144)],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($form->fresh()->steps[1]['fields'][4]['images'])->toHaveCount(1);
});

it('stores an image captured with the camera through the public link', function () {
    Storage::fake('public');

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
        'camera_answers' => [
            'F2-5' => [UploadedFile::fake()->image('camera.jpg')],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $form->refresh();
    $images = $form->steps[1]['fields'][4]['images'];

    expect($images)->toHaveCount(1);
    expect($images[0])->toStartWith('/storage/');
    expect($images[0])->toContain('/storage/forms/'.$form->getKey().'/');
    expect($form->steps[1]['fields'][4]['status'])->toBe('enviado');

    Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($images[0]));
});

it('stores gallery and camera images uploaded together', function () {
    Storage::fake('public');

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-5' => [UploadedFile::fake()->image('galeria.jpg')],
        ],
        'camera_answers' => [
            'F2-5' => [UploadedFile::fake()->image('camera.jpg')],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $form->refresh();
    $images = $form->steps[1]['fields'][4]['images'];

    expect($images)->toHaveCount(2);
    expect($form->steps[1]['fields'][4]['status'])->toBe('enviado');
});

it('requires an image when the image field is mandatory and only camera files are provided', function () {
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

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
        'camera_answers' => [
            'F2-5' => [UploadedFile::fake()->image('camera.jpg')],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($form->fresh()->steps[1]['fields'][4]['images'])->toHaveCount(1);
});

it('limits the combined gallery and camera images to five per field', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-5' => [
                UploadedFile::fake()->image('galeria-1.jpg'),
                UploadedFile::fake()->image('galeria-2.jpg'),
                UploadedFile::fake()->image('galeria-3.jpg'),
            ],
        ],
        'camera_answers' => [
            'F2-5' => [
                UploadedFile::fake()->image('camera-1.jpg'),
                UploadedFile::fake()->image('camera-2.jpg'),
                UploadedFile::fake()->image('camera-3.jpg'),
            ],
        ],
    ])->assertSessionHasErrors('answers.F2-5');

    expect($form->fresh()->steps[1]['fields'][4]['images'])->toBeEmpty();
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

it('stores multiple documents uploaded through the public link', function () {
    Storage::fake('public');

    $steps = publicFormSteps();
    $steps[1]['fields'][] = ['id' => 'F2-6', 'type' => 'file', 'label' => 'Laudo técnico', 'required' => false, 'files' => [], 'status' => 'pendente'];

    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => $steps]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
            'F2-6' => [
                UploadedFile::fake()->create('laudo.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('fotos.zip', 80, 'application/zip'),
            ],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $field = $form->fresh()->steps[1]['fields'][5];

    expect($field['files'])->toHaveCount(2);
    expect($field['fileNames'])->toBe(['laudo.pdf', 'fotos.zip']);
    expect($field['status'])->toBe('enviado');

    foreach ($field['files'] as $file) {
        Storage::disk('public')->assertExists('forms/'.$form->getKey().'/'.basename($file));
    }
});

it('fails the submission when an upload cannot be stored', function () {
    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);

    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('putFile')->times(3)->andReturn(false);

    Storage::set('public', $disk);

    try {
        $this->from(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
            ->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
                'answers' => [
                    'F1-1' => 'Galpão A',
                    'F1-3' => '2026-10-25',
                    'F2-1' => 'Conforme',
                    'F2-5' => [UploadedFile::fake()->image('painel.jpg')],
                ],
            ])
            ->assertRedirect(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
            ->assertSessionHasErrors('answers.F2-5');

        $form->refresh();

        expect($form->status)->toBe('Pendente');
        expect($form->responses()->count())->toBe(0);
        expect($form->steps[1]['fields'][4]['images'])->toBeEmpty();
    } finally {
        Storage::forgetDisk('public');
    }
});

it('retries an upload that fails once before storing it', function () {
    $form = Form::factory()->create(['status' => 'Pendente', 'steps' => publicFormSteps()]);
    $path = 'forms/'.$form->getKey().'/painel.jpg';

    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('putFile')->twice()->andReturn(false, $path);
    $disk->shouldReceive('exists')->once()->with($path)->andReturn(true);
    $disk->shouldReceive('url')->once()->with($path)->andReturn('/storage/'.$path);

    Storage::set('public', $disk);

    try {
        $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
            'answers' => [
                'F1-1' => 'Galpão A',
                'F1-3' => '2026-10-25',
                'F2-1' => 'Conforme',
                'F2-5' => [UploadedFile::fake()->image('painel.jpg')],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        expect($form->fresh()->steps[1]['fields'][4]['images'])->toBe(['/storage/'.$path]);
    } finally {
        Storage::forgetDisk('public');
    }
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

it('updates the same response when the form accepts a single response', function () {
    $form = Form::factory()->create([
        'status' => 'Pendente',
        'allows_multiple_responses' => false,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    $response = FormResponse::query()->firstOrFail();

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão B',
            'F1-3' => '2026-10-26',
            'F2-1' => 'Não conforme',
        ],
    ])->assertRedirect();

    $responses = FormResponse::query()->get();

    expect($responses)->toHaveCount(1);
    expect($responses->first()->is($response))->toBeTrue();
    expect($responses->first()->steps[0]['fields'][0]['value'])->toBe('Galpão B');
    expect($responses->first()->status)->toBe('Preenchido');

    expect($form->fresh()->steps[0]['fields'][0]['value'])->toBe('Galpão B');
});

it('creates a separate response for every submission when multiple responses are allowed', function () {
    $form = Form::factory()->create([
        'status' => 'Pendente',
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão B',
            'F1-3' => '2026-10-26',
            'F2-1' => 'Não conforme',
        ],
    ])->assertRedirect();

    $responses = FormResponse::query()->orderBy('id')->get();

    expect($responses)->toHaveCount(2);
    expect($responses[0]->steps[0]['fields'][0]['value'])->toBe('Galpão A');
    expect($responses[1]->steps[0]['fields'][0]['value'])->toBe('Galpão B');
    expect($responses[1]->submitted_at)->not->toBeNull();

    expect($form->fresh()->steps[0]['fields'][0]['value'])->toBe('Galpão B');
});

it('starts every submission with a blank structure when multiple responses are allowed', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => true,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertDontSee('Galpão A');
});

it('shows the submission progress in the form header', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('Progresso do envio')
        ->assertSee('0 de 8')
        ->assertSee('0%')
        ->assertSee('data-form-progress', false)
        ->assertSee('data-form-progress-form', false);
});

it('presents each section as a step in the public form', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('data-form-steps', false)
        ->assertSee('data-form-initial-step="0"', false)
        ->assertSee('data-form-step="0"', false)
        ->assertSee('data-form-step="1"', false)
        ->assertSee('data-form-step-prev', false)
        ->assertSee('data-form-step-next', false)
        ->assertSee('data-form-step-submit', false)
        ->assertSee('data-form-step-go="0"', false)
        ->assertSee('data-form-step-go="1"', false)
        ->assertSee('Ir para a etapa 1: Identificação', false)
        ->assertSee('Ir para a etapa 2: Conformidade', false)
        ->assertSee('Etapa')
        ->assertSee('Próximo')
        ->assertSee('Enviar respostas');
});

it('opens the step that contains validation errors', function () {
    $form = Form::factory()->create(['steps' => publicFormSteps()]);

    $this->from(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->followingRedirects()
        ->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
            'answers' => [
                'F1-1' => 'Galpão A',
                'F1-3' => '2026-10-25',
            ],
        ])
        ->assertOk()
        ->assertSee('data-form-initial-step="1"', false);
});

it('updates the submission progress with the answers already stored', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => false,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('3 de 8')
        ->assertSee('38%');
});

it('shows the evaluation status pill for each question on the public form', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => false,
        'steps' => publicFormSteps(),
    ]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSeeText('Pendente');

    $steps = publicFormSteps();
    $steps[0]['fields'][0]['status'] = 'aprovado';
    $steps[0]['fields'][1]['status'] = 'reprovado';

    $form->responses()->create([
        'status' => 'Preenchido',
        'steps' => $steps,
        'submitted_at' => now(),
    ]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSeeText('Aprovado')
        ->assertSeeText('Reprovado');
});

it('prefills the existing answer when the form accepts a single response', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => false,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('Galpão A')
        ->assertSee('Salvar')
        ->assertDontSee('Atualizar respostas');
});

it('follows the current structure while keeping the existing answers of a single response', function () {
    $form = Form::factory()->create([
        'allows_multiple_responses' => false,
        'steps' => publicFormSteps(),
    ]);

    $this->post(URL::signedRoute('public.forms.submit', ['form' => $form->getKey()]), [
        'answers' => [
            'F1-1' => 'Galpão A',
            'F1-3' => '2026-10-25',
            'F2-1' => 'Conforme',
        ],
    ])->assertRedirect();

    $steps = publicFormSteps();
    $steps[0]['fields'][] = ['id' => 'F1-4', 'type' => 'text', 'label' => 'Nova pergunta', 'required' => false, 'value' => '', 'status' => 'pendente'];

    $form->update(['steps' => $steps]);

    $this->get(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
        ->assertOk()
        ->assertSee('Galpão A')
        ->assertSee('Nova pergunta');
});
