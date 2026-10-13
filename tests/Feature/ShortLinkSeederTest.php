<?php

use App\Enums\ShortLinkType;
use App\Models\ShortLink;
use App\Models\User;
use Database\Seeders\ShortLinkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds upload and download links for every user', function () {
    $owner = User::factory()->create();

    $this->seed(ShortLinkSeeder::class);

    $links = ShortLink::query()->whereBelongsTo($owner)->get();

    expect($links)->not->toBeEmpty();

    $download = $links->firstWhere('type', ShortLinkType::Download);
    expect($download)->not->toBeNull()
        ->and($download->documents()->count())->toBeGreaterThan(0);

    $upload = $links->firstWhere('type', ShortLinkType::Upload);
    expect($upload)->not->toBeNull()
        ->and($upload->receivedDocuments()->count())->toBeGreaterThan(0);
});

it('seeds a redirect link with a destination', function () {
    User::factory()->create();

    $this->seed(ShortLinkSeeder::class);

    $redirect = ShortLink::query()->where('type', ShortLinkType::Redirect)->first();

    expect($redirect)->not->toBeNull()
        ->and($redirect->redirect_url)->toBe('https://example.com/portal');
});

it('does not duplicate links when seeded twice', function () {
    User::factory()->create();

    $this->seed(ShortLinkSeeder::class);
    $count = ShortLink::query()->count();

    $this->seed(ShortLinkSeeder::class);

    expect(ShortLink::query()->count())->toBe($count);
});
