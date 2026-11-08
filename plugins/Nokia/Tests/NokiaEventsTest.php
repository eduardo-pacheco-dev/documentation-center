<?php

use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\Nokia\Events\NokiaChecklistStatusChanged;
use Plugins\Nokia\Events\NokiaSiteImported;
use Plugins\Nokia\Events\NokiaSiteRejected;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Providers\NokiaServiceProvider;
use Plugins\Nokia\Support\NokiaSiteImporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginActivator::class);
    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('nokia');
    $plugin->enable();

    app()->register(NokiaServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('importing sites dispatches an event for each created site', function () {
    Event::fake([NokiaSiteImported::class]);

    $file = UploadedFile::fake()->createWithContent(
        'sites.csv',
        "site_id,end_id,oc\nSITE-A,END-A,OC-A\nSITE-B,END-B,OC-B\n",
    );

    app(NokiaSiteImporter::class)->import($file);

    Event::assertDispatchedTimes(NokiaSiteImported::class, 2);
    Event::assertDispatched(
        NokiaSiteImported::class,
        fn (NokiaSiteImported $event): bool => $event->site->site_id === 'SITE-A',
    );
});

test('changing a checklist status dispatches the status event', function () {
    Event::fake([NokiaChecklistStatusChanged::class]);

    $site = NokiaSite::factory()->create([
        'checklist_fam_entrega_status' => NokiaSite::STATUS_PENDING,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.nokia.sites.checklist.update', [
            'site' => $site,
            'field' => 'checklist_fam_entrega',
        ]), ['status' => NokiaSite::STATUS_ANALYSIS])
        ->assertRedirect();

    Event::assertDispatched(
        NokiaChecklistStatusChanged::class,
        fn (NokiaChecklistStatusChanged $event): bool => $event->site->is($site)
            && $event->field === 'checklist_fam_entrega'
            && $event->previousStatus === NokiaSite::STATUS_PENDING
            && $event->status === NokiaSite::STATUS_ANALYSIS,
    );
});

test('registering a rejection dispatches an event for each rejected checklist', function () {
    Event::fake([NokiaSiteRejected::class]);

    $site = NokiaSite::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.nokia.sites.rejections.store', $site), [
            'fam' => ['checklist_fam_entrega', 'checklist_fam_instalacao'],
            'rejection_reason' => 'Documentação incompleta.',
            'rejection_return_days' => 7,
            'rejection_requires_field_return' => false,
        ])
        ->assertRedirect();

    Event::assertDispatchedTimes(NokiaSiteRejected::class, 2);
    Event::assertDispatched(
        NokiaSiteRejected::class,
        fn (NokiaSiteRejected $event): bool => $event->site->is($site)
            && $event->rejection->checklist_field === 'checklist_fam_entrega',
    );
});
