<?php

use App\Models\User;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\FollowUp\Database\Seeders\FollowUpSeeder;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Mail\FollowUpMail;
use Plugins\FollowUp\Mail\FollowUpSummaryMail;
use Plugins\FollowUp\Mail\FollowUpTableMail;
use Plugins\FollowUp\Mail\FollowUpTestMail;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpAssignee;
use Plugins\FollowUp\Models\FollowUpClient;
use Plugins\FollowUp\Models\FollowUpProject;
use Plugins\FollowUp\Models\FollowUpSetting;
use Plugins\FollowUp\Models\FollowUpUpdate;
use Plugins\FollowUp\Providers\FollowUpServiceProvider;
use Plugins\FollowUp\Support\FollowUpExport;
use Plugins\FollowUp\Support\FollowUpImporter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('follow-up');
    $plugin->enable();

    app()->register(FollowUpServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function () {
    File::delete($this->statusesPath);
});

test('guests are redirected from the follow-ups area', function () {
    $this->get('/admin/follow-ups')->assertRedirect('/login');
});

test('the follow-ups plugin appears in the navigation', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.follow-ups.index'));
});

test('follow-ups can be listed, searched and filtered', function () {
    $user = User::factory()->create();

    $fornecedor = FollowUp::factory()->create([
        'title' => 'Retornar contato com o fornecedor',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Relatório mensal',
        'code' => 'REL-2026-042',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Low->value,
    ]);

    $this->actingAs($user)
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Retornar contato com o fornecedor')
        ->assertSee('Relatório mensal');

    $this->actingAs($user)
        ->get('/admin/follow-ups?search=fornecedor')
        ->assertOk()
        ->assertSee('Retornar contato com o fornecedor')
        ->assertDontSee('Relatório mensal');

    $this->actingAs($user)
        ->get('/admin/follow-ups?search=REL-2026')
        ->assertOk()
        ->assertSee('Relatório mensal')
        ->assertDontSee(route('admin.follow-ups.show', $fornecedor));

    $this->actingAs($user)
        ->get('/admin/follow-ups?status=pendente&priority=alta')
        ->assertOk()
        ->assertSee('Retornar contato com o fornecedor')
        ->assertDontSee('Relatório mensal');
});

test('follow-ups can be sorted by the table columns', function () {
    FollowUp::factory()->create(['title' => 'Alfa acompanhamento']);
    FollowUp::factory()->create(['title' => 'Beta acompanhamento']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/follow-ups?sort=title&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['Alfa acompanhamento', 'Beta acompanhamento']);

    $this->actingAs($user)
        ->get('/admin/follow-ups?sort=title&direction=desc')
        ->assertOk()
        ->assertSeeInOrder(['Beta acompanhamento', 'Alfa acompanhamento']);
});

test('priority sorting follows the declared priority order', function () {
    FollowUp::factory()->create([
        'title' => 'Prioridade alta',
        'priority' => FollowUpPriority::High->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Prioridade baixa',
        'priority' => FollowUpPriority::Low->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups?sort=priority&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['Prioridade baixa', 'Prioridade alta']);
});

test('follow-ups without a due date stay last when sorting by due date', function () {
    FollowUp::factory()->create([
        'title' => 'Com prazo',
        'due_date' => now()->addDay(),
    ]);

    FollowUp::factory()->create([
        'title' => 'Sem prazo definido',
        'due_date' => null,
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups?sort=due_date&direction=desc')
        ->assertOk()
        ->assertSeeInOrder(['Com prazo', 'Sem prazo definido']);
});

test('follow-ups can be displayed in alternative view modes', function () {
    FollowUp::factory()->create(['title' => 'Follow-up em outros modos']);

    $user = User::factory()->create();

    foreach (['cards', 'compact', 'kanban'] as $view) {
        $this->actingAs($user)
            ->get('/admin/follow-ups?view='.$view)
            ->assertOk()
            ->assertSee('Follow-up em outros modos');
    }

    $this->actingAs($user)
        ->get('/admin/follow-ups?view=invalid')
        ->assertOk()
        ->assertSee('<table', false);
});

test('the kanban view groups follow-ups by status', function () {
    FollowUp::factory()->create([
        'title' => 'Pendente no quadro',
        'status' => FollowUpStatus::Pending->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Concluído no quadro',
        'status' => FollowUpStatus::Completed->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups?view=kanban')
        ->assertOk()
        ->assertSeeInOrder(['Pendente no quadro', 'Concluído no quadro']);
});

test('the optional code has its own column on the table', function () {
    FollowUp::factory()->create(['title' => 'Com código', 'code' => 'FU-COD-01']);
    FollowUp::factory()->create(['title' => 'Sem código', 'code' => null]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('sort=code', false)
        ->assertSee('Código')
        ->assertSee('FU-COD-01');
});

test('the form exposes the optional code field', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups?create=1')
        ->assertOk()
        ->assertSee('name="code"', false)
        ->assertSee('(opcional)');
});

test('the table shows the completion date of finished follow-ups', function () {
    $completed = FollowUp::factory()->completed()->create([
        'title' => 'Concluído com data de conclusão',
    ]);

    FollowUp::factory()->create(['title' => 'Pendente sem data de conclusão']);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Concluído em')
        ->assertSee($completed->completed_at->format('d/m/Y'));
});

test('the index offers a manual e-mail composer', function () {
    $creator = User::factory()->create(['email' => 'criador-composer@example.com']);

    FollowUp::factory()->create([
        'title' => 'Compor e-mail pela listagem',
        'created_by' => $creator->getKey(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('open-email-composer')
        ->assertSee('criador-composer@example.com')
        ->assertSee('Compor e-mail pela listagem')
        ->assertSee('Todos os follow-ups da listagem (1)');
});

test('the e-mail composer offers manual and scheduled tabs', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Envio manual')
        ->assertSee('Agendado')
        ->assertSee('Salvar agendamento')
        ->assertSee('Agendamento ativo')
        ->assertSee('Período do resumo')
        ->assertSee('name="summary_period"', false)
        ->assertSee('Dias da semana')
        ->assertSee('Segunda')
        ->assertSee('Domingo')
        ->assertSee('name="summary_days[]"', false)
        ->assertSee('Destinatários (Para)')
        ->assertSee('Cópias (CC)');
});

test('the summary days default to every weekday', function () {
    expect(FollowUpSetting::current()->summaryDays())->toBe([0, 1, 2, 3, 4, 5, 6]);
});

test('the summary days are normalized and ignore invalid values', function () {
    expect(FollowUpSetting::parseSummaryDays('5,1,3,3,9,-1,abc'))->toBe([1, 3, 5]);
});

test('the schedule requires at least one weekday', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings/schedule', [
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_times' => '08:00',
            'summary_days' => [],
        ])
        ->assertSessionHasErrors('summary_days');
});

test('the index exposes secondary actions in the action menu', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Abrir menu de ações')
        ->assertSee('follow-up-page-actions', false)
        ->assertSee('Exportar')
        ->assertSee('Enviar por e-mail')
        ->assertSee('Configuração');

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Abrir menu de ações')
        ->assertSee('Exportar')
        ->assertSee('Enviar por e-mail')
        ->assertDontSee('Configuração');
});

test('the periodic schedule can be updated from the composer', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings/schedule', [
            'email_summary_enabled' => '0',
            'email_subject_prefix' => 'Acompanhamento',
            'summary_period' => FollowUpSetting::SUMMARY_PERIOD_LAST_30_DAYS,
            'summary_times' => '07:30, 19:45',
            'summary_days' => ['1', '3', '5'],
            'summary_recipients' => 'gestao@example.com, financeiro@example.com',
            'summary_cc_recipients' => 'cc@example.com',
        ])
        ->assertRedirect(route('admin.follow-ups.index'))
        ->assertSessionHas('status');

    $settings = FollowUpSetting::current();

    expect($settings->email_summary_enabled)->toBeFalse()
        ->and($settings->email_subject_prefix)->toBe('Acompanhamento')
        ->and($settings->summaryPeriod())->toBe(FollowUpSetting::SUMMARY_PERIOD_LAST_30_DAYS)
        ->and($settings->summaryTimes())->toBe(['07:30', '19:45'])
        ->and($settings->summaryDays())->toBe([1, 3, 5])
        ->and($settings->summaryRecipients())->toBe(['gestao@example.com', 'financeiro@example.com'])
        ->and($settings->summaryCcRecipients())->toBe(['cc@example.com']);
});

test('the summary can be limited to the last thirty days by due date', function () {
    Mail::fake();

    $creator = User::factory()->create(['email' => 'criador@example.com']);

    FollowUp::factory()->create([
        'title' => 'Dentro dos últimos 30 dias',
        'status' => FollowUpStatus::Pending->value,
        'due_date' => today()->subDays(5)->toDateString(),
        'created_by' => $creator->getKey(),
    ]);

    FollowUp::factory()->create([
        'title' => 'Fora dos últimos 30 dias',
        'status' => FollowUpStatus::Pending->value,
        'due_date' => today()->subDays(40)->toDateString(),
        'created_by' => $creator->getKey(),
    ]);

    FollowUp::factory()->create([
        'title' => 'Com prazo futuro',
        'status' => FollowUpStatus::Pending->value,
        'due_date' => today()->addDays(5)->toDateString(),
        'created_by' => $creator->getKey(),
    ]);

    FollowUpSetting::current()->update([
        'summary_recipients' => 'gestao@example.com',
        'summary_period' => FollowUpSetting::SUMMARY_PERIOD_LAST_30_DAYS,
    ]);

    $this->artisan('follow-up:send-daily-summary')->assertSuccessful();

    Mail::assertSent(FollowUpTableMail::class, function (FollowUpTableMail $mail): bool {
        return $mail->followUps->pluck('title')->all() === ['Dentro dos últimos 30 dias'];
    });
});

test('invalid summary recipients are rejected', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings/schedule', [
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_times' => '08:00',
            'summary_days' => [1, 2, 3, 4, 5],
            'summary_recipients' => 'nao-e-um-email',
        ])
        ->assertSessionHasErrors('summary_recipients');
});

test('configured copy e-mails are added to the scheduled summary', function () {
    Mail::fake();

    FollowUp::factory()->create(['status' => FollowUpStatus::Pending->value]);

    FollowUpSetting::current()->update([
        'summary_recipients' => 'destino@example.com',
        'summary_cc_recipients' => 'cc1@example.com, cc2@example.com',
    ]);

    $this->artisan('follow-up:send-daily-summary')->assertSuccessful();

    Mail::assertSent(FollowUpTableMail::class, function (FollowUpTableMail $mail): bool {
        return $mail->hasTo('destino@example.com')
            && $mail->hasCc('cc1@example.com')
            && $mail->hasCc('cc2@example.com');
    });
});

test('the general summary is sent to the configured recipients', function () {
    Mail::fake();

    $creator = User::factory()->create(['email' => 'criador@example.com']);

    FollowUp::factory()->create([
        'created_by' => $creator->getKey(),
        'status' => FollowUpStatus::Pending->value,
    ]);

    FollowUpSetting::current()->update([
        'summary_recipients' => 'gestao@example.com, financeiro@example.com',
        'summary_cc_recipients' => 'copia@example.com',
    ]);

    config(['follow-up.email.subject_prefix' => 'Acompanhamento']);

    $this->artisan('follow-up:send-daily-summary')->assertSuccessful();

    Mail::assertSent(FollowUpTableMail::class, function (FollowUpTableMail $mail): bool {
        return $mail->hasTo('gestao@example.com')
            && $mail->hasTo('financeiro@example.com')
            && $mail->hasCc('copia@example.com')
            && $mail->followUps->count() === 1
            && str_contains($mail->envelope()->subject, 'Acompanhamento — Resumo');
    });

    Mail::assertNothingQueued();
});

test('regular users cannot change the periodic schedule', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/follow-ups/settings/schedule', [
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_times' => '08:00',
            'summary_days' => [1, 2, 3, 4, 5],
        ])
        ->assertForbidden();
});

test('the index renders an actions menu for each follow-up', function () {
    $followUp = FollowUp::factory()->create(['title' => 'Acompanhar proposta']);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Abrir menu de ações do follow-up Acompanhar proposta')
        ->assertSee(route('admin.follow-ups.show', $followUp))
        ->assertSee(route('admin.follow-ups.edit', $followUp))
        ->assertSee(route('admin.follow-ups.export-one', $followUp))
        ->assertSee(route('admin.follow-ups.destroy', $followUp));
});

test('the create page redirects to the listing with the modal open', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups/create')
        ->assertRedirect(route('admin.follow-ups.index', ['create' => 1]));
});

test('the index renders the follow-up creation modal', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups?create=1')
        ->assertOk()
        ->assertSee('open-follow-up-create')
        ->assertSee('name="title"', false)
        ->assertSee('name="status"', false)
        ->assertSee('name="priority"', false)
        ->assertSee('name="due_date"', false)
        ->assertSee('name="client_id"', false)
        ->assertSee('name="project_id"', false)
        ->assertSee('name="completed_at"', false)
        ->assertSee('name="assignee_id"', false)
        ->assertSee('name="modal" value="create"', false);
});

test('a follow-up can be created with its initial timeline entry', function () {
    $user = User::factory()->create();
    $assignee = FollowUpAssignee::factory()->create();
    $client = FollowUpClient::factory()->create(['name' => 'AFL Engenharia']);
    $project = FollowUpProject::factory()->create([
        'client_id' => $client->getKey(),
        'name' => 'Expansão 5G',
    ]);

    $response = $this->actingAs($user)->post('/admin/follow-ups', [
        'title' => 'Acompanhar aprovação da proposta',
        'code' => 'FU-2026-001',
        'description' => 'Validar com o jurídico.',
        'client_id' => $client->getKey(),
        'project_id' => $project->getKey(),
        'status' => FollowUpStatus::InProgress->value,
        'priority' => FollowUpPriority::Urgent->value,
        'due_date' => now()->addDays(3)->toDateString(),
        'assignee_id' => $assignee->getKey(),
    ]);

    $followUp = FollowUp::query()->sole();

    $response->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp)
        ->title->toBe('Acompanhar aprovação da proposta')
        ->code->toBe('FU-2026-001')
        ->client_id->toBe($client->getKey())
        ->project_id->toBe($project->getKey())
        ->status->toBe(FollowUpStatus::InProgress)
        ->priority->toBe(FollowUpPriority::Urgent)
        ->assignee_id->toBe($assignee->getKey())
        ->created_by->toBe($user->getKey())
        ->completed_at->toBeNull();

    expect($followUp->updates()->count())->toBe(1);

    expect($followUp->updates()->first())
        ->body->toBe('Follow-up criado.')
        ->status_to->toBe(FollowUpStatus::InProgress);
});

test('a follow-up created as completed receives the current date by default', function () {
    $this->actingAs(User::factory()->create())->post('/admin/follow-ups', [
        'title' => 'Follow-up concluído',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Medium->value,
    ]);

    expect(FollowUp::query()->sole()->completed_at->toDateString())->toBe(now()->toDateString());
});

test('the completion date can be chosen when creating a completed follow-up', function () {
    $this->actingAs(User::factory()->create())->post('/admin/follow-ups', [
        'title' => 'Follow-up concluído com data escolhida',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Medium->value,
        'completed_at' => '2026-10-15',
    ]);

    expect(FollowUp::query()->sole()->completed_at->toDateString())->toBe('2026-10-15');
});

test('the completion date can be chosen when updating a follow-up', function () {
    $followUp = FollowUp::factory()->create(['status' => FollowUpStatus::Pending->value]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.follow-ups.update', $followUp), [
            'title' => $followUp->title,
            'status' => FollowUpStatus::Completed->value,
            'priority' => FollowUpPriority::High->value,
            'completed_at' => '2026-09-01',
        ])
        ->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp->fresh()->completed_at->toDateString())->toBe('2026-09-01');
});

test('follow-up creation requires title, status and priority', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups', [])
        ->assertSessionHasErrors(['title', 'status', 'priority']);

    expect(FollowUp::query()->count())->toBe(0);
});

test('the follow-up details page renders its data', function () {
    $client = FollowUpClient::factory()->create(['name' => 'Nokia']);
    $project = FollowUpProject::factory()->create([
        'client_id' => $client->getKey(),
        'name' => 'Modernização de rede',
    ]);

    $followUp = FollowUp::factory()->create([
        'title' => 'Detalhe importante',
        'description' => 'Descrição detalhada do acompanhamento.',
        'client_id' => $client->getKey(),
        'project_id' => $project->getKey(),
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.follow-ups.show', $followUp))
        ->assertOk()
        ->assertSee('Detalhe importante')
        ->assertSee('Descrição detalhada do acompanhamento.')
        ->assertSee('Nokia')
        ->assertSee('Modernização de rede')
        ->assertSee('Pendente')
        ->assertSee('Prioridade Alta');
});

test('a follow-up can be updated and status changes are recorded', function () {
    $client = FollowUpClient::factory()->create(['name' => 'Cliente Atualizado']);
    $project = FollowUpProject::factory()->create([
        'client_id' => $client->getKey(),
        'name' => 'Projeto Atualizado',
    ]);

    $followUp = FollowUp::factory()->create([
        'title' => 'Atualizar acompanhamento',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::Medium->value,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->put(route('admin.follow-ups.update', $followUp), [
            'title' => 'Atualizar acompanhamento',
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'status' => FollowUpStatus::Completed->value,
            'priority' => FollowUpPriority::High->value,
        ]);

    $response->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp->fresh())
        ->client_id->toBe($client->getKey())
        ->project_id->toBe($project->getKey())
        ->status->toBe(FollowUpStatus::Completed)
        ->priority->toBe(FollowUpPriority::High)
        ->completed_at->not->toBeNull();

    expect($followUp->updates()->first())
        ->body->toBe('Status alterado de Pendente para Concluído.')
        ->status_from->toBe(FollowUpStatus::Pending)
        ->status_to->toBe(FollowUpStatus::Completed);
});

test('a follow-up can be deleted', function () {
    $followUp = FollowUp::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.follow-ups.destroy', $followUp))
        ->assertRedirect(route('admin.follow-ups.index'));

    expect(FollowUp::query()->find($followUp->getKey()))->toBeNull();
});

test('updates can be added and removed by their author', function () {
    $author = User::factory()->create();
    $followUp = FollowUp::factory()->create();

    $this->actingAs($author)
        ->post(route('admin.follow-ups.updates.store', $followUp), [
            'body' => 'Ligação realizada com o fornecedor.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $update = FollowUpUpdate::query()->sole();

    expect($update)
        ->follow_up_id->toBe($followUp->getKey())
        ->user_id->toBe($author->getKey())
        ->body->toBe('Ligação realizada com o fornecedor.');

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.follow-ups.updates.destroy', [$followUp, $update]))
        ->assertForbidden();

    $this->actingAs($author)
        ->delete(route('admin.follow-ups.updates.destroy', [$followUp, $update]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(FollowUpUpdate::query()->find($update->getKey()))->toBeNull();
});

test('the dashboard shows the signed in user follow-ups', function () {
    $user = User::factory()->create();
    $assignee = FollowUpAssignee::factory()->create(['email' => $user->email]);

    FollowUp::factory()->overdue()->create([
        'title' => 'Follow-up atrasado do usuário',
        'assignee_id' => $assignee->getKey(),
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Meus follow-ups')
        ->assertSee('Follow-up atrasado do usuário')
        ->assertSee('1 atrasado');
});

test('the seeder creates sample follow-ups once', function () {
    User::factory()->count(3)->create();

    $this->seed(FollowUpSeeder::class);
    $this->seed(FollowUpSeeder::class);

    expect(FollowUp::query()->count())->toBe(8)
        ->and(FollowUpUpdate::query()->count())->toBeGreaterThanOrEqual(8);

    expect(FollowUp::query()->where('status', FollowUpStatus::Completed->value)->count())->toBe(2)
        ->and(FollowUp::query()->where('status', FollowUpStatus::Cancelled->value)->count())->toBe(1);
});

test('follow-ups can be exported to excel with the current filters', function () {
    $client = FollowUpClient::factory()->create(['name' => 'Cliente Exportado']);
    $project = FollowUpProject::factory()->create([
        'client_id' => $client->getKey(),
        'name' => 'Projeto Exportado',
    ]);

    FollowUp::factory()->create([
        'code' => 'FU-EXP-001',
        'title' => 'Follow-up exportado pendente',
        'client_id' => $client->getKey(),
        'project_id' => $project->getKey(),
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Follow-up exportado concluído',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Low->value,
    ]);

    $fileName = app(FollowUpExport::class)->allFileName();

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.follow-ups.export', ['status' => FollowUpStatus::Pending->value]))
        ->assertOk()
        ->assertDownload($fileName);

    $path = sys_get_temp_dir().'/follow-ups-export-'.Str::random(8).'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = IOFactory::load($path);
    $text = collect($spreadsheet->getSheetByName('Follow-ups')->toArray())->flatten()->filter()->implode(' | ');

    expect($spreadsheet->getSheetNames())->toBe(['Follow-ups'])
        ->and($spreadsheet->getSheetByName('Follow-ups')->getCell('A1')->getValue())->toBe('Código')
        ->and($text)
        ->toContain('FU-EXP-001')
        ->toContain('Follow-up exportado pendente')
        ->toContain('Cliente Exportado')
        ->toContain('Projeto Exportado')
        ->toContain('Alta')
        ->not->toContain('Follow-up exportado concluído');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('a single follow-up can be exported with its history', function () {
    $followUp = FollowUp::factory()->create([
        'code' => 'FU-IND-001',
        'title' => 'Follow-up individual exportado',
        'status' => FollowUpStatus::InProgress->value,
        'priority' => FollowUpPriority::Urgent->value,
    ]);

    $followUp->updates()->create([
        'user_id' => User::factory()->create()->getKey(),
        'body' => 'Atualização exportada no histórico.',
    ]);

    $fileName = app(FollowUpExport::class)->singleFileName($followUp);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.follow-ups.export-one', $followUp))
        ->assertOk()
        ->assertDownload($fileName);

    $path = sys_get_temp_dir().'/follow-up-export-'.Str::random(8).'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = IOFactory::load($path);

    expect($spreadsheet->getSheetNames())
        ->toBe(['Follow-up', 'Histórico']);

    $followUpText = collect($spreadsheet->getSheetByName('Follow-up')->toArray())->flatten()->filter()->implode(' | ');
    $historyText = collect($spreadsheet->getSheetByName('Histórico')->toArray())->flatten()->filter()->implode(' | ');

    expect($followUpText)
        ->toContain('FU-IND-001')
        ->toContain('Follow-up individual exportado')
        ->toContain('Em andamento')
        ->toContain('Urgente');

    expect($historyText)->toContain('Atualização exportada no histórico.');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('a follow-up can be sent by e-mail', function () {
    Mail::fake();

    $followUp = FollowUp::factory()->create(['title' => 'Enviar por e-mail']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.follow-ups.email.send', $followUp), [
            'recipients' => 'primeiro@example.com, segundo@example.com',
            'subject' => 'Assunto personalizado',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    Mail::assertSent(FollowUpMail::class, function (FollowUpMail $mail): bool {
        return $mail->hasTo('primeiro@example.com')
            && $mail->hasTo('segundo@example.com')
            && $mail->envelope()->subject === 'Assunto personalizado';
    });
});

test('delivery failures are reported in a toast when sending a follow-up', function () {
    Mail::shouldReceive('to')
        ->once()
        ->andThrow(new RuntimeException('SMTP recusou a conexão'));

    $followUp = FollowUp::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.follow-ups.email.send', $followUp), [
            'recipients' => 'destino@example.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Falha ao enviar o e-mail: SMTP recusou a conexão');
});

test('the whole filtered table can be sent by e-mail', function () {
    Mail::fake();

    FollowUp::factory()->create([
        'title' => 'Pendente na tabela',
        'status' => FollowUpStatus::Pending->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Concluído na tabela',
        'status' => FollowUpStatus::Completed->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/email', [
            'recipients' => 'tabela@example.com',
            'subject' => 'Tabela filtrada',
            'status' => FollowUpStatus::Pending->value,
        ])
        ->assertRedirect(route('admin.follow-ups.index', ['status' => FollowUpStatus::Pending->value]))
        ->assertSessionHas('status');

    Mail::assertSent(FollowUpTableMail::class, function (FollowUpTableMail $mail): bool {
        return $mail->hasTo('tabela@example.com')
            && $mail->envelope()->subject === 'Tabela filtrada'
            && $mail->followUps->count() === 1
            && $mail->followUps->first()->title === 'Pendente na tabela';
    });
});

test('delivery failures are reported in a toast when sending the table', function () {
    Mail::shouldReceive('to')
        ->once()
        ->andThrow(new RuntimeException('SMTP indisponível'));

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/email', [
            'recipients' => 'destino@example.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Falha ao enviar a tabela de follow-ups: SMTP indisponível');
});

test('the table e-mail includes the rows, the completion date and the excel attachment', function () {
    FollowUp::factory()->create([
        'title' => 'Linha enviada na tabela',
        'status' => FollowUpStatus::Completed->value,
        'completed_at' => '2026-10-20',
    ]);

    $mail = new FollowUpTableMail(
        FollowUp::query()->with(['assignee', 'creator'])->get(),
    );

    expect($mail->attachments())->toHaveCount(1)
        ->and($mail->render())
        ->toContain('Linha enviada na tabela')
        ->toContain('Concluído em')
        ->toContain('20/10/2026')
        ->toContain('1 follow-up(s)');
});

test('invalid e-mail recipients are rejected', function () {
    Mail::fake();

    $followUp = FollowUp::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.follow-ups.email.send', $followUp), [
            'recipients' => 'nao-e-um-email',
        ])
        ->assertSessionHasErrors('recipients');

    Mail::assertNothingQueued();
});

test('the follow-up e-mail includes the summary, history and excel attachment', function () {
    $followUp = FollowUp::factory()->create(['title' => 'Follow-up no corpo do e-mail']);

    $followUp->updates()->create([
        'user_id' => User::factory()->create()->getKey(),
        'body' => 'Andamento registrado no corpo do e-mail.',
    ]);

    $mail = new FollowUpMail($followUp);

    expect($mail->attachments())->toHaveCount(1)
        ->and($mail->render())
        ->toContain('Follow-up no corpo do e-mail')
        ->toContain('Andamento registrado no corpo do e-mail.');
});

test('the daily summary command queues one e-mail per creator', function () {
    Mail::fake();

    $creator = User::factory()->create(['email' => 'criador@example.com']);
    $other = User::factory()->create(['email' => 'outro@example.com']);

    FollowUp::factory()->count(2)->create([
        'created_by' => $creator->getKey(),
        'status' => FollowUpStatus::Pending->value,
    ]);

    FollowUp::factory()->create([
        'created_by' => $other->getKey(),
        'status' => FollowUpStatus::InProgress->value,
    ]);

    FollowUp::factory()->completed()->create([
        'created_by' => $creator->getKey(),
    ]);

    FollowUpSetting::current()->update(['summary_cc_recipients' => 'copia@example.com']);

    $this->artisan('follow-up:send-daily-summary')->assertSuccessful();

    Mail::assertQueued(FollowUpSummaryMail::class, 2);

    Mail::assertQueued(FollowUpSummaryMail::class, function (FollowUpSummaryMail $mail) use ($creator): bool {
        return $mail->hasTo('criador@example.com')
            && $mail->hasCc('copia@example.com')
            && $mail->user->is($creator)
            && $mail->followUps->count() === 2;
    });

    Mail::assertQueued(FollowUpSummaryMail::class, fn (FollowUpSummaryMail $mail): bool => $mail->hasTo('outro@example.com'));

    $summary = Mail::queued(FollowUpSummaryMail::class)
        ->first(fn (FollowUpSummaryMail $mail): bool => $mail->hasTo('criador@example.com'));

    expect($summary->attachments())->toHaveCount(1)
        ->and($summary->render())
        ->toContain('Você tem 2 follow-up(s) em aberto');
});

test('an admin can view and update the follow-up settings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/follow-ups/settings')
        ->assertOk()
        ->assertSee('Configurações do Follow-up')
        ->assertSee('name="per_page"', false)
        ->assertDontSee('name="summary_times"', false)
        ->assertSee('Servidor de e-mail (SMTP)')
        ->assertSee('Enviar e-mail de teste')
        ->assertSee('Responsáveis')
        ->assertSee('Adicionar responsável');

    $this->actingAs($admin)
        ->put('/admin/follow-ups/settings', [
            'per_page' => 25,
            'due_soon_days' => 10,
            'widget_limit' => 8,
            'mail_enabled' => '0',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    $settings = FollowUpSetting::current();

    expect($settings->per_page)->toBe(25)
        ->and($settings->due_soon_days)->toBe(10)
        ->and($settings->widget_limit)->toBe(8);
});

test('an admin can register a custom assignee', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/follow-ups/settings/assignees', [
            'name' => 'Maria Externa',
            'email' => 'maria@externo.test',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    $assignee = FollowUpAssignee::query()->sole();

    expect($assignee->name)->toBe('Maria Externa')
        ->and($assignee->email)->toBe('maria@externo.test');

    $this->actingAs($admin)
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Maria Externa');
});

test('an assignee can be removed from the settings page', function () {
    $admin = User::factory()->admin()->create();
    $assignee = FollowUpAssignee::factory()->create(['name' => 'Responsavel Removivel']);

    $followUp = FollowUp::factory()->create(['assignee_id' => $assignee->getKey()]);

    $this->actingAs($admin)
        ->delete(route('admin.follow-ups.settings.assignees.destroy', $assignee))
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    expect(FollowUpAssignee::query()->find($assignee->getKey()))->toBeNull()
        ->and($followUp->fresh()->assignee_id)->toBeNull();
});

test('an admin can register clients and their projects', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/follow-ups/settings/clients', ['client_name' => 'AFL Engenharia'])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    $client = FollowUpClient::query()->sole();

    $this->actingAs($admin)
        ->post('/admin/follow-ups/settings/projects', [
            'client_id' => $client->getKey(),
            'project_name' => 'Expansão 5G',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    $this->actingAs($admin)
        ->get('/admin/follow-ups/settings')
        ->assertOk()
        ->assertSee('Clientes e projetos')
        ->assertSee('AFL Engenharia')
        ->assertSee('Expansão 5G');

    expect($client->projects()->sole()->name)->toBe('Expansão 5G');
});

test('clients and projects can be updated and removed', function () {
    $admin = User::factory()->admin()->create();
    $client = FollowUpClient::factory()->create(['name' => 'Cliente Antigo']);
    $otherClient = FollowUpClient::factory()->create(['name' => 'Cliente Novo']);
    $project = FollowUpProject::factory()->create([
        'client_id' => $client->getKey(),
        'name' => 'Projeto Antigo',
    ]);
    $followUp = FollowUp::factory()->create([
        'client_id' => $client->getKey(),
        'project_id' => $project->getKey(),
    ]);

    $this->actingAs($admin)
        ->put(route('admin.follow-ups.settings.clients.update', $client), [
            'client_name' => 'Cliente Renomeado',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'));

    $this->actingAs($admin)
        ->put(route('admin.follow-ups.settings.projects.update', $project), [
            'client_id' => $otherClient->getKey(),
            'project_name' => 'Projeto Movido',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'));

    expect($client->fresh()->name)->toBe('Cliente Renomeado')
        ->and($project->fresh()->name)->toBe('Projeto Movido')
        ->and($project->fresh()->client_id)->toBe($otherClient->getKey())
        ->and($followUp->fresh()->client_id)->toBe($otherClient->getKey());

    $this->actingAs($admin)
        ->delete(route('admin.follow-ups.settings.projects.destroy', $project))
        ->assertRedirect(route('admin.follow-ups.settings'));

    $this->actingAs($admin)
        ->delete(route('admin.follow-ups.settings.clients.destroy', $client))
        ->assertRedirect(route('admin.follow-ups.settings'));

    $this->actingAs($admin)
        ->delete(route('admin.follow-ups.settings.clients.destroy', $otherClient))
        ->assertRedirect(route('admin.follow-ups.settings'));

    expect(FollowUpProject::query()->find($project->getKey()))->toBeNull()
        ->and(FollowUpClient::query()->find($client->getKey()))->toBeNull()
        ->and(FollowUpClient::query()->find($otherClient->getKey()))->toBeNull()
        ->and($followUp->fresh()->client_id)->toBeNull()
        ->and($followUp->fresh()->project_id)->toBeNull();
});

test('clients cannot be duplicated', function () {
    FollowUpClient::factory()->create(['name' => 'AFL Engenharia']);

    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/follow-ups/settings/clients', ['client_name' => 'AFL Engenharia'])
        ->assertSessionHasErrors('client_name');

    expect(FollowUpClient::query()->count())->toBe(1);
});

test('regular users cannot manage clients and projects', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/settings/clients', ['client_name' => 'Cliente'])
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/settings/projects', [
            'client_id' => 1,
            'project_name' => 'Projeto',
        ])
        ->assertForbidden();
});

test('the follow-up form lists the registered clients and projects', function () {
    $client = FollowUpClient::factory()->create(['name' => 'Cliente do Formulário']);
    FollowUpProject::factory()->create([
        'client_id' => $client->getKey(),
        'name' => 'Projeto do Formulário',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups?create=1')
        ->assertOk()
        ->assertSee('Cliente do Formulário')
        ->assertSee('Projeto do Formulário');
});

test('a follow-up rejects a project from another client', function () {
    $client = FollowUpClient::factory()->create();
    $otherClient = FollowUpClient::factory()->create();
    $project = FollowUpProject::factory()->create(['client_id' => $otherClient->getKey()]);

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups', [
            'title' => 'Follow-up inválido',
            'client_id' => $client->getKey(),
            'project_id' => $project->getKey(),
            'status' => FollowUpStatus::Pending->value,
            'priority' => FollowUpPriority::Medium->value,
        ])
        ->assertSessionHasErrors('project_id');
});

test('assignee registration requires a name', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/follow-ups/settings/assignees', [
            'name' => '',
            'email' => 'nao-e-um-email',
        ])
        ->assertSessionHasErrors(['name', 'email']);
});

test('regular users cannot access the follow-up settings', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups/settings')
        ->assertForbidden();
});

test('invalid summary period is rejected', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings/schedule', [
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_period' => 'invalid-period',
            'summary_times' => '08:00',
            'summary_days' => [1, 2, 3, 4, 5],
        ])
        ->assertSessionHasErrors('summary_period');
});

test('invalid summary times are rejected', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings/schedule', [
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_times' => '25:00',
            'summary_days' => [1, 2, 3, 4, 5],
        ])
        ->assertSessionHasErrors('summary_times');
});

test('the smtp credentials are saved encrypted and kept when the password is blank', function () {
    $admin = User::factory()->admin()->create();

    $payload = [
        'per_page' => 15,
        'due_soon_days' => 7,
        'widget_limit' => 5,
        'mail_enabled' => '1',
        'mail_host' => 'smtp.exemplo.com',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'mail_username' => 'usuario-smtp',
        'mail_password' => 'segredo-smtp',
        'mail_from_address' => 'nao-responda@exemplo.com',
        'mail_from_name' => 'Documentation Center',
    ];

    $this->actingAs($admin)->put('/admin/follow-ups/settings', $payload)->assertRedirect();

    $settings = FollowUpSetting::current();

    expect($settings->mail_enabled)->toBeTrue()
        ->and($settings->mail_host)->toBe('smtp.exemplo.com')
        ->and($settings->mail_port)->toBe(587)
        ->and($settings->mail_username)->toBe('usuario-smtp')
        ->and($settings->mail_password)->toBe('segredo-smtp');

    $storedPassword = FollowUpSetting::query()->toBase()->value('mail_password');

    expect($storedPassword)->not->toBe('segredo-smtp');

    $payload['mail_password'] = '';

    $this->actingAs($admin)->put('/admin/follow-ups/settings', $payload)->assertRedirect();

    expect(FollowUpSetting::current()->mail_password)->toBe('segredo-smtp');
});

test('enabling smtp requires a host and port', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings', [
            'per_page' => 15,
            'due_soon_days' => 7,
            'widget_limit' => 5,
            'mail_enabled' => '1',
        ])
        ->assertSessionHasErrors(['mail_host', 'mail_port']);
});

test('an admin can send a test e-mail', function () {
    Mail::fake();

    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/follow-ups/settings/test-email', [
            'recipient' => 'teste@example.com',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    Mail::assertSent(FollowUpTestMail::class, fn (FollowUpTestMail $mail): bool => $mail->hasTo('teste@example.com'));
});

test('the test e-mail requires a valid recipient', function () {
    Mail::fake();

    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/follow-ups/settings/test-email', [
            'recipient' => 'nao-e-um-email',
        ])
        ->assertSessionHasErrors('recipient');

    Mail::assertNothingSent();
});

test('failures while sending the test e-mail are reported to the user', function () {
    Mail::shouldReceive('to')
        ->once()
        ->andThrow(new RuntimeException('SMTP indisponível'));

    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/follow-ups/settings/test-email', [
            'recipient' => 'teste@example.com',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('error', 'Não foi possível enviar o e-mail de teste: SMTP indisponível');
});

test('the daily summary is scheduled twice a day', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event): bool => str_contains((string) $event->command, 'follow-up:send-daily-summary'));

    expect($events)->toHaveCount(2);
});

test('the follow-up import page renders the instructions', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups/import')
        ->assertOk()
        ->assertSee('Importar follow-ups')
        ->assertSee('Baixar modelo Excel')
        ->assertSee('500 follow-ups')
        ->assertSee('Título');
});

test('follow-ups can be imported from csv with defaults and assignees', function () {
    $assignee = FollowUpAssignee::factory()->create([
        'name' => 'Ana Importação',
        'email' => 'ana.import@example.com',
    ]);

    $user = User::factory()->create();

    $csv = implode("\n", [
        'Código;Título;Descrição;Status;Prioridade;Prazo;Concluído em;Responsável',
        'FU-IMP-1;Follow-up importado;Descrição importada;Em andamento;Alta;03/11/2026;;ana.import@example.com',
        implode(';', ['', 'Follow-up padrão', '', '', '', '', '', '']),
    ]);

    $this->actingAs($user)
        ->post('/admin/follow-ups/import', [
            'file' => UploadedFile::fake()->createWithContent('follow-ups.csv', $csv),
        ])
        ->assertRedirect(route('admin.follow-ups.index'))
        ->assertSessionHasNoErrors();

    $imported = FollowUp::query()->orderBy('id')->get();

    expect($imported)->toHaveCount(2);

    expect($imported[0])
        ->code->toBe('FU-IMP-1')
        ->title->toBe('Follow-up importado')
        ->status->toBe(FollowUpStatus::InProgress)
        ->priority->toBe(FollowUpPriority::High)
        ->due_date->format('Y-m-d')->toBe('2026-11-03')
        ->assignee_id->toBe($assignee->getKey())
        ->created_by->toBe($user->getKey());

    expect($imported[1])
        ->title->toBe('Follow-up padrão')
        ->status->toBe(FollowUpStatus::Pending)
        ->priority->toBe(FollowUpPriority::Medium)
        ->assignee_id->toBeNull();

    expect(FollowUpUpdate::query()->where('body', 'Follow-up importado.')->count())->toBe(2);
});

test('follow-ups can be imported with client and project', function () {
    $user = User::factory()->create();

    $csv = implode("\n", [
        'Título;Cliente;Projeto',
        'Follow-up com cliente;AFL Engenharia;Expansão 5G',
    ]);

    $this->actingAs($user)
        ->post('/admin/follow-ups/import', [
            'file' => UploadedFile::fake()->createWithContent('follow-ups.csv', $csv),
        ])
        ->assertRedirect(route('admin.follow-ups.index'))
        ->assertSessionHasNoErrors();

    $followUp = FollowUp::query()->sole();

    expect($followUp)
        ->client->name->toBe('AFL Engenharia')
        ->project->name->toBe('Expansão 5G');
});

test('follow-ups can be imported from an excel template', function () {
    $template = __DIR__.'/../Resources/templates/follow-ups-import.xlsx';
    $spreadsheet = IOFactory::load($template);
    $sheet = $spreadsheet->getSheet(0);

    $sheet->fromArray([
        'FU-XLSX-1',
        'Follow-up vindo do Excel',
        'Descrição via planilha',
        'Pendente',
        'Urgente',
        '10/11/2026',
        '',
        '',
    ], null, 'A2');

    $path = sys_get_temp_dir().'/follow-ups-import-'.Str::random(8).'.xlsx';
    (new Xlsx($spreadsheet))->save($path);
    $spreadsheet->disconnectWorksheets();

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/import', [
            'file' => new UploadedFile(
                $path,
                'follow-ups.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true,
            ),
        ])
        ->assertRedirect(route('admin.follow-ups.index'))
        ->assertSessionHasNoErrors();

    @unlink($path);

    expect(FollowUp::query()->sole())
        ->title->toBe('Follow-up vindo do Excel')
        ->priority->toBe(FollowUpPriority::Urgent)
        ->due_date->format('Y-m-d')->toBe('2026-11-10');
});

test('follow-up imports are limited to five hundred rows and are atomic', function () {
    $rows = ['Título'];

    for ($index = 1; $index <= FollowUpImporter::MAX_ROWS + 1; $index++) {
        $rows[] = 'Follow-up '.$index;
    }

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/import', [
            'file' => UploadedFile::fake()->createWithContent('follow-ups.csv', implode("\n", $rows)),
        ])
        ->assertSessionHasErrors('file');

    expect(FollowUp::query()->count())->toBe(0);

    $invalid = implode("\n", [
        'Título;Código',
        'Follow-up válido;FU-OK',
        ';FU-SEM-TITULO',
    ]);

    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups/import', [
            'file' => UploadedFile::fake()->createWithContent('follow-ups.csv', $invalid),
        ])
        ->assertSessionHasErrors('file');

    expect(FollowUp::query()->count())->toBe(0);
});

test('the follow-up import template can be downloaded', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups/import/template')
        ->assertOk()
        ->assertDownload('modelo-importacao-follow-ups.xlsx');

    $path = __DIR__.'/../Resources/templates/follow-ups-import.xlsx';

    expect(is_file($path))->toBeTrue();

    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getSheet(0);

    expect($sheet->getTitle())->toBe('Follow-ups')
        ->and($sheet->getCell('A1')->getValue())->toBe('Código')
        ->and($sheet->getCell('B1')->getValue())->toBe('Título *')
        ->and($spreadsheet->getSheetNames())->toContain('Instruções');

    $spreadsheet->disconnectWorksheets();
});
