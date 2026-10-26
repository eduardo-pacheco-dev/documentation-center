<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * @return array<int, array<string, mixed>>
 */
function backfillImageSteps(string $imageUrl): array
{
    return [[
        'id' => 'S1',
        'title' => 'Registro Fotográfico',
        'fields' => [
            ['id' => 'F1-1', 'type' => 'image', 'label' => 'Foto do site', 'images' => [$imageUrl], 'status' => 'enviado'],
        ],
    ]];
}

it('generates thumbnails for images already stored on forms and responses', function () {
    Storage::fake('public');

    $path = 'forms/1/foto.jpg';

    Storage::disk('public')->put(
        $path,
        UploadedFile::fake()->image('foto.jpg', 1200, 800)->getContent(),
    );

    $url = '/storage/'.$path;

    $form = Form::factory()->create(['steps' => backfillImageSteps($url)]);

    $response = $form->responses()->create([
        'status' => 'Preenchido',
        'steps' => backfillImageSteps($url),
        'submitted_at' => now(),
    ]);

    $this->artisan('forms:backfill-image-thumbnails')
        ->expectsOutputToContain('2 miniatura(s) gerada(s).')
        ->assertSuccessful();

    $field = $form->fresh()->steps[0]['fields'][0];
    $responseField = $response->fresh()->steps[0]['fields'][0];

    expect($field['imageThumbnails'])->toHaveCount(1);
    expect($field['imageThumbnails'][0])->toStartWith('/storage/forms/1/thumb-');
    expect($responseField['imageThumbnails'])->toBe($field['imageThumbnails']);

    Storage::disk('public')->assertExists('forms/1/thumb-foto.jpg');

    [$width, $height] = getimagesize(Storage::disk('public')->path('forms/1/thumb-foto.jpg'));

    expect($width)->toBe(400);
    expect($height)->toBe(267);
});

it('keeps the original image when the stored file is missing', function () {
    Storage::fake('public');

    $url = '/storage/forms/1/inexistente.jpg';
    $form = Form::factory()->create(['steps' => backfillImageSteps($url)]);

    $this->artisan('forms:backfill-image-thumbnails')
        ->expectsOutputToContain('0 miniatura(s) gerada(s).')
        ->expectsOutputToContain('1 imagem(ns) não encontrada(s) no disco foram ignoradas.')
        ->assertSuccessful();

    expect($form->fresh()->steps)->toBe(backfillImageSteps($url));
});
