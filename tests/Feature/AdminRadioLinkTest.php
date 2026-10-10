<?php

use App\Models\Erb;
use App\Models\RadioLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Two ERBs owned by the given user, ready to compose a radio link.
 *
 * @return array{endA: Erb, endB: Erb}
 */
function radioLinkEndpoints(User $user): array
{
    return [
        'endA' => Erb::factory()->create(['user_id' => $user->getKey()]),
        'endB' => Erb::factory()->create(['user_id' => $user->getKey()]),
    ];
}

/**
 * A valid store payload for the given endpoints.
 *
 * @return array<string, mixed>
 */
function validRadioLinkPayload(Erb $endA, Erb $endB, string $code = 'RL-00001'): array
{
    return [
        'code' => $code,
        'erb_a_id' => $endA->getKey(),
        'erb_b_id' => $endB->getKey(),
        'equipment_a' => 'Ericsson MINI-LINK 6363',
        'equipment_b' => 'Cambium PTP 820',
        'frequency' => '23.500',
        'bandwidth' => '40',
        'capacity' => '1000',
        'polarization' => 'vertical',
        'status' => 'active',
        'notes' => 'Enlace de teste.',
    ];
}

it('requires an authenticated user to open the radio links area', function () {
    $this->get('/admin/radio-links')->assertRedirect('/login');
});

it('lists only the radio links the user owns', function () {
    $owner = User::factory()->create();
    $endpoints = radioLinkEndpoints($owner);

    RadioLink::factory()->create([
        'user_id' => $owner->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00001',
    ]);
    RadioLink::factory()->create(['code' => 'RL-09999']);

    $this->actingAs($owner)
        ->get('/admin/radio-links')
        ->assertOk()
        ->assertSee('RL-00001')
        ->assertSee('RL-00001')
        ->assertDontSee('RL-09999');
});

it('filters the radio link list by search term and status', function () {
    $owner = User::factory()->create();
    $endpoints = radioLinkEndpoints($owner);

    Erb::whereKey($endpoints['endA']->getKey())->update(['name' => 'Alpha Norte']);
    Erb::whereKey($endpoints['endB']->getKey())->update(['name' => 'Beta Sul']);

    RadioLink::factory()->create([
        'user_id' => $owner->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00001',
        'status' => 'active',
    ]);

    $more = radioLinkEndpoints($owner);
    RadioLink::factory()->maintenance()->create([
        'user_id' => $owner->getKey(),
        'erb_a_id' => $more['endA']->getKey(),
        'erb_b_id' => $more['endB']->getKey(),
        'code' => 'RL-00002',
        'frequency' => '38.000',
    ]);

    $this->actingAs($owner)
        ->get('/admin/radio-links?search=Alpha')
        ->assertOk()
        ->assertSee('RL-00001')
        ->assertDontSee('RL-00002');

    $this->actingAs($owner)
        ->get('/admin/radio-links?status=maintenance')
        ->assertOk()
        ->assertSee('RL-00002')
        ->assertDontSee('RL-00001');
});

it('renders the radio link list in the alternate view modes', function () {
    $owner = User::factory()->create();
    $endpoints = radioLinkEndpoints($owner);

    RadioLink::factory()->create([
        'user_id' => $owner->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00001',
    ]);

    $this->actingAs($owner)->get('/admin/radio-links?view=cards')->assertOk()->assertSee('RL-00001');
    $this->actingAs($owner)->get('/admin/radio-links?view=compact')->assertOk()->assertSee('RL-00001');
});

it('creates a radio link owned by the authenticated user', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);

    $this->actingAs($user)->get('/admin/radio-links/create')->assertOk();

    $response = $this->actingAs($user)
        ->post('/admin/radio-links', validRadioLinkPayload($endpoints['endA'], $endpoints['endB']));

    $radioLink = RadioLink::query()->firstOrFail();

    $response->assertRedirect(route('admin.radio-links.show', $radioLink));

    expect($radioLink->user_id)->toBe($user->getKey())
        ->and($radioLink->code)->toBe('RL-00001')
        ->and($radioLink->erb_a_id)->toBe($endpoints['endA']->getKey())
        ->and($radioLink->erb_b_id)->toBe($endpoints['endB']->getKey())
        ->and($radioLink->equipment_a)->toBe('Ericsson MINI-LINK 6363')
        ->and($radioLink->frequency)->toBe('23.500')
        ->and($radioLink->capacity)->toBe('1000.00')
        ->and($radioLink->polarization->value)->toBe('vertical')
        ->and($radioLink->status->value)->toBe('active');

    $this->actingAs($user)
        ->get(route('admin.radio-links.show', $radioLink))
        ->assertOk()
        ->assertSee('RL-00001')
        ->assertSee('Ericsson MINI-LINK 6363')
        ->assertSee('23,500');
});

it('defaults the radio link status to planned when it is not sent', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);

    $this->actingAs($user)
        ->post('/admin/radio-links', [
            'code' => 'RL-00007',
            'erb_a_id' => $endpoints['endA']->getKey(),
            'erb_b_id' => $endpoints['endB']->getKey(),
        ])
        ->assertRedirect();

    expect(RadioLink::query()->firstOrFail()->status->value)->toBe('planned');
});

it('rejects a radio link without a code or endpoints', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/radio-links', [])
        ->assertSessionHasErrors(['code', 'erb_a_id', 'erb_b_id']);

    expect(RadioLink::query()->count())->toBe(0);
});

it('rejects a duplicate code owned by the same user', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);

    RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00042',
    ]);

    $more = radioLinkEndpoints($user);

    $this->actingAs($user)
        ->post('/admin/radio-links', [
            'code' => 'RL-00042',
            'erb_a_id' => $more['endA']->getKey(),
            'erb_b_id' => $more['endB']->getKey(),
        ])
        ->assertSessionHasErrors('code');
});

it('rejects endpoints that belong to another user', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);
    $foreign = radioLinkEndpoints(User::factory()->create());

    $this->actingAs($user)
        ->post('/admin/radio-links', [
            'code' => 'RL-00010',
            'erb_a_id' => $foreign['endA']->getKey(),
            'erb_b_id' => $foreign['endB']->getKey(),
        ])
        ->assertSessionHasErrors('erb_a_id');

    $this->actingAs($user)
        ->post('/admin/radio-links', [
            'code' => 'RL-00011',
            'erb_a_id' => $endpoints['endA']->getKey(),
            'erb_b_id' => $foreign['endB']->getKey(),
        ])
        ->assertSessionHasErrors('erb_b_id');
});

it('rejects a radio link with equal endpoints', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);

    $this->actingAs($user)
        ->post('/admin/radio-links', [
            'code' => 'RL-00012',
            'erb_a_id' => $endpoints['endA']->getKey(),
            'erb_b_id' => $endpoints['endA']->getKey(),
        ])
        ->assertSessionHasErrors('erb_a_id');
});

it('forbids a stranger from managing the radio link', function () {
    $stranger = User::factory()->create();
    $owner = User::factory()->create();
    $endpoints = radioLinkEndpoints($owner);
    $radioLink = RadioLink::factory()->create([
        'user_id' => $owner->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00042',
    ]);

    $this->actingAs($stranger)->get("/admin/radio-links/{$radioLink->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/radio-links/{$radioLink->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->put("/admin/radio-links/{$radioLink->getKey()}", validRadioLinkPayload($endpoints['endA'], $endpoints['endB']))->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/radio-links/{$radioLink->getKey()}")->assertForbidden();
});

it('updates the radio link details', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);
    $radioLink = RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00042',
    ]);

    $this->actingAs($user)->get("/admin/radio-links/{$radioLink->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/radio-links/{$radioLink->getKey()}", [
            ...validRadioLinkPayload($endpoints['endA'], $endpoints['endB']),
            'code' => 'RL-00042',
            'status' => 'inactive',
            'notes' => 'Enlace desativado.',
        ])
        ->assertSessionHas('status');

    $radioLink->refresh();

    expect($radioLink->status->value)->toBe('inactive')
        ->and($radioLink->notes)->toBe('Enlace desativado.')
        ->and($radioLink->frequency)->toBe('23.500');
});

it('soft deletes the radio link', function () {
    $user = User::factory()->create();
    $endpoints = radioLinkEndpoints($user);
    $radioLink = RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endpoints['endA']->getKey(),
        'erb_b_id' => $endpoints['endB']->getKey(),
        'code' => 'RL-00042',
    ]);

    $this->actingAs($user)
        ->delete("/admin/radio-links/{$radioLink->getKey()}")
        ->assertRedirect(route('admin.radio-links.index'));

    expect($radioLink->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/radio-links')->assertOk()->assertDontSee('RL-00042');
});

it('computes the link distance from the endpoint coordinates', function () {
    $user = User::factory()->create();

    $endA = Erb::factory()->create([
        'user_id' => $user->getKey(),
        'latitude' => '0.0000000',
        'longitude' => '0.0000000',
    ]);
    $endB = Erb::factory()->create([
        'user_id' => $user->getKey(),
        'latitude' => '0.0000000',
        'longitude' => '1.0000000',
    ]);

    $radioLink = RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endA->getKey(),
        'erb_b_id' => $endB->getKey(),
        'code' => 'RL-00042',
    ]);

    expect($radioLink->distanceKm())->toBeBetween(110, 113);
});

it('lists its radio links on the ERB detail page', function () {
    $user = User::factory()->create();

    $endA = Erb::factory()->create(['user_id' => $user->getKey()]);
    $endB = Erb::factory()->create(['user_id' => $user->getKey()]);
    $endC = Erb::factory()->create(['user_id' => $user->getKey()]);

    RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endA->getKey(),
        'erb_b_id' => $endB->getKey(),
        'code' => 'RL-00001',
    ]);
    RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endB->getKey(),
        'erb_b_id' => $endC->getKey(),
        'code' => 'RL-00002',
    ]);

    $this->actingAs($user)
        ->get("/admin/erbs/{$endB->getKey()}")
        ->assertOk()
        ->assertSee('RL-00001')
        ->assertSee('RL-00002');
});
