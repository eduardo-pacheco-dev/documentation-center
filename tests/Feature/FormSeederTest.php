<?php

use App\Models\Form;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\FormResponseSeeder;
use Database\Seeders\FormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds forms with an empty structure for every user', function () {
    $user = User::factory()->create();
    Project::factory()->for($user)->count(3)->create();

    $this->seed(FormSeeder::class);

    $forms = Form::query()->get();

    expect($forms)->toHaveCount(3);

    $pending = $forms->firstWhere('status', 'Pendente');

    expect($pending)->not->toBeNull();
    expect($pending->steps[0]['fields'][0]['value'])->toBe('');
    expect($forms->first()->steps)->toHaveCount(3);
});

it('fills the responses with answers and lorem ipsum images', function () {
    $user = User::factory()->create();
    Project::factory()->for($user)->count(3)->create();

    $this->seed(FormSeeder::class);
    $this->seed(FormResponseSeeder::class);

    $forms = Form::query()->get();

    $approved = $forms->firstWhere('status', 'Aprovado');
    expect($approved)->not->toBeNull();
    expect($approved->steps[0]['fields'][0]['value'])->not->toBe('');

    $imageFields = collect($approved->steps)
        ->flatMap(fn (array $step) => $step['fields'] ?? [])
        ->where('type', 'image');

    expect($imageFields)->not->toBeEmpty();
    expect($imageFields->first()['images'])->not->toBeEmpty();
    expect($imageFields->first()['images'][0])->toContain('picsum.photos');
    expect($imageFields->first()['status'])->toBe('aprovado');

    $completed = $forms->firstWhere('status', 'Preenchido');
    expect($completed->steps[0]['fields'][0]['value'])->not->toBe('');
    expect(collect($completed->steps)->flatMap(fn (array $step) => $step['fields'])->firstWhere('type', 'image')['status'])->toBe('enviado');

    $pending = $forms->firstWhere('status', 'Pendente');
    expect($pending->steps[0]['fields'][0]['value'])->toBe('');
});
