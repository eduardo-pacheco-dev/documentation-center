<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Plugins\Analytics\Models\AnalyticsAflRecord;
use Plugins\Analytics\Models\AnalyticsRecord;
use Plugins\Projects\Models\Project;

uses(RefreshDatabase::class);

function analyticsApi(User $user, string $path = 'summary', array $query = []): TestResponse
{
    return test()->actingAs($user, 'sanctum')->getJson('/api/v1/analytics/'.$path.'?'.http_build_query($query));
}

it('requires a sanctum token to access the analytics api', function () {
    $this->getJson('/api/v1/analytics/summary')->assertUnauthorized();
});

it('describes the available endpoints', function () {
    analyticsApi(User::factory()->create(), '', [])
        ->assertOk()
        ->assertJsonPath('data.authentication', 'Bearer token (Laravel Sanctum)')
        ->assertJsonStructure([
            'data' => ['endpoints' => [['method', 'path', 'description']], 'query'],
            'meta' => ['period' => ['from', 'to', 'days'], 'scope', 'generated_at'],
        ]);
});

it('returns the summary scoped to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Project::factory()->count(2)->create(['user_id' => $user->getKey()]);
    Project::factory()->count(5)->create(['user_id' => $other->getKey()]);

    analyticsApi($user, 'summary')
        ->assertOk()
        ->assertJsonPath('data.summary.projects.total', 2)
        ->assertJsonPath('meta.scope', 'user')
        ->assertJsonStructure([
            'data' => [
                'summary' => ['projects', 'work_orders', 'tasks', 'forms', 'documents', 'agenda'],
                'work_order_statuses',
                'project_statuses',
                'task_split',
                'tasks_by_list',
                'top_projects',
            ],
        ]);
});

it('returns the creation series', function () {
    analyticsApi(User::factory()->create(), 'series', ['period' => 7])
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['labels', 'projects', 'work_orders', 'documents', 'records'],
            'meta' => ['period' => ['from', 'to', 'days']],
        ])
        ->assertJsonPath('meta.period.days', 7);
});

it('restricts the global scope to administrators', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    Project::factory()->create(['user_id' => $admin->getKey()]);
    Project::factory()->count(3)->create(['user_id' => $other->getKey()]);

    analyticsApi($admin, 'summary', ['scope' => 'global'])
        ->assertOk()
        ->assertJsonPath('meta.scope', 'global')
        ->assertJsonPath('data.summary.projects.total', 4);

    analyticsApi(User::factory()->create(), 'summary', ['scope' => 'global'])
        ->assertForbidden();
});

it('validates the period query parameters', function () {
    analyticsApi(User::factory()->create(), 'summary', ['from' => '2026-02-30'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('from');
});

it('returns the fams dataset with aggregates and recent rows', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '364007',
        'os' => '417',
        'created_on' => now()->toDateString(),
        'process_stage' => 'Instalação',
        'status' => 'Aceito com Ressalvas',
        'supplier' => 'NOKIA',
    ]);

    AnalyticsRecord::query()->create([
        'user_id' => $other->getKey(),
        'oc' => '999999',
        'os' => '999',
        'created_on' => now()->toDateString(),
    ]);

    analyticsApi($user, 'fams', ['period' => 30])
        ->assertOk()
        ->assertJsonPath('data.summary.total', 1)
        ->assertJsonPath('data.by_status.labels', ['Aceito com Ressalvas'])
        ->assertJsonStructure(['data' => ['summary', 'by_status', 'by_stage', 'by_supplier', 'recent', 'changes']]);
});

it('paginates and searches the fams records', function () {
    $user = User::factory()->create();

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '364007',
        'os' => '417',
        'node_name' => '7NLAMGV28',
    ]);

    AnalyticsRecord::query()->create([
        'user_id' => $user->getKey(),
        'oc' => '362293',
        'os' => '8171',
        'node_name' => '7NLNVIT01',
    ]);

    analyticsApi($user, 'fams/records', ['per_page' => 1])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.pagination.total', 2)
        ->assertJsonPath('meta.pagination.per_page', 1);

    analyticsApi($user, 'fams/records', ['search' => '7NLNVIT01'])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.os', '8171');
});

it('returns the fams record history', function () {
    $user = User::factory()->create();

    foreach (['Pendente', 'Aprovado'] as $status) {
        AnalyticsRecord::query()->create([
            'user_id' => $user->getKey(),
            'oc' => '364007',
            'os' => '417',
            'status' => $status,
        ]);
    }

    analyticsApi($user, 'fams/records/364007/417/history')
        ->assertOk()
        ->assertJsonCount(2, 'data.versions')
        ->assertJsonPath('data.versions.0.status', 'Pendente')
        ->assertJsonPath('data.versions.1.status', 'Aprovado');
});

it('returns the afl dataset with aggregates and recent rows', function () {
    $user = User::factory()->create();

    AnalyticsAflRecord::query()->create([
        'user_id' => $user->getKey(),
        'code' => 'AFL20260620',
        'general_status' => 'Pendente OS/PO',
        'project' => 'CT_REUSO',
        'billing_status' => 'Não aplicável',
    ]);

    analyticsApi($user, 'afl', ['period' => 30])
        ->assertOk()
        ->assertJsonPath('data.summary.total', 1)
        ->assertJsonPath('data.by_status.labels', ['Pendente OS/PO'])
        ->assertJsonStructure(['data' => ['summary', 'by_status', 'by_billing_status', 'by_project', 'recent', 'changes']]);
});

it('paginates the afl records and returns their history', function () {
    $user = User::factory()->create();

    foreach (['Pendente', 'Concluída'] as $status) {
        AnalyticsAflRecord::query()->create([
            'user_id' => $user->getKey(),
            'code' => 'AFL20260620',
            'general_status' => $status,
            'project' => 'CT_MW',
        ]);
    }

    analyticsApi($user, 'afl/records')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'AFL20260620')
        ->assertJsonPath('data.0.versions', 2)
        ->assertJsonPath('meta.pagination.total', 1);

    analyticsApi($user, 'afl/records/AFL20260620/history')
        ->assertOk()
        ->assertJsonCount(2, 'data.versions')
        ->assertJsonPath('data.versions.0.general_status', 'Pendente')
        ->assertJsonPath('data.versions.1.general_status', 'Concluída');
});

it('returns the stored snapshots', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->post('/admin/analytics/snapshot', ['period' => 30]);

    analyticsApi($user, 'snapshots')
        ->assertOk()
        ->assertJsonStructure(['data' => ['snapshots' => [['captured_on', 'metrics']]]])
        ->assertJsonCount(1, 'data.snapshots');
});
