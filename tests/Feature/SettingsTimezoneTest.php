<?php

use App\Models\User;
use App\Settings\SystemSettings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot update the system timezone', function () {
    $this->put('/settings/system', ['timezone' => 'America/Sao_Paulo'])
        ->assertRedirect('/login');

    $this->assertDatabaseMissing('settings', ['key' => SystemSettings::TIMEZONE_KEY]);
});

test('only administrators can see the system timezone section', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings')
        ->assertOk()
        ->assertDontSee('Fuso horário do sistema');
});

test('administrators can see the system timezone section', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/settings')
        ->assertOk()
        ->assertSee('Fuso horário do sistema')
        ->assertSee('America/Sao_Paulo')
        ->assertSee('Salvar fuso horário');
});

test('administrators can save the system timezone', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from('/settings')
        ->put('/settings/system', ['timezone' => 'America/Sao_Paulo'])
        ->assertRedirect('/settings')
        ->assertSessionHas('status', 'Fuso horário do sistema atualizado com sucesso.');

    $this->assertDatabaseHas('settings', [
        'key' => SystemSettings::TIMEZONE_KEY,
        'value' => 'America/Sao_Paulo',
    ]);

    expect(config('app.timezone'))->toBe('America/Sao_Paulo')
        ->and(date_default_timezone_get())->toBe('America/Sao_Paulo')
        ->and(SystemSettings::timezone())->toBe('America/Sao_Paulo');
});

test('the system timezone is updated when saved twice', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put('/settings/system', ['timezone' => 'America/Sao_Paulo']);
    $this->actingAs($admin)->put('/settings/system', ['timezone' => 'Europe/Lisbon']);

    $this->assertDatabaseHas('settings', [
        'key' => SystemSettings::TIMEZONE_KEY,
        'value' => 'Europe/Lisbon',
    ]);

    expect(SystemSettings::timezone())->toBe('Europe/Lisbon');
});

test('non administrators cannot update the system timezone', function () {
    $this->actingAs(User::factory()->create())
        ->put('/settings/system', ['timezone' => 'America/Sao_Paulo'])
        ->assertForbidden();

    $this->assertDatabaseMissing('settings', ['key' => SystemSettings::TIMEZONE_KEY]);
});

test('the system timezone must be a valid identifier', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/settings/system', ['timezone' => 'Mars/Olympus'])
        ->assertSessionHasErrors('timezone');

    $this->assertDatabaseMissing('settings', ['key' => SystemSettings::TIMEZONE_KEY]);
});

test('the system timezone falls back to the application default when it is not configured', function () {
    expect(SystemSettings::timezone())->toBe(config('app.timezone'));
});

test('the configured timezone is used by the scheduler', function () {
    SystemSettings::setTimezone('America/Sao_Paulo');

    $schedule = app(Schedule::class);
    $timezone = (fn (): mixed => $this->timezone)->call($schedule);

    expect($timezone)->toBe('America/Sao_Paulo');
});
