<?php

use App\Models\User;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Plugins\FollowUp\Database\Seeders\FollowUpSeeder;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Mail\FollowUpMail;
use Plugins\FollowUp\Mail\FollowUpSummaryMail;
use Plugins\FollowUp\Mail\FollowUpTableMail;
use Plugins\FollowUp\Mail\FollowUpTestMail;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpSetting;
use Plugins\FollowUp\Models\FollowUpUpdate;
use Plugins\FollowUp\Providers\FollowUpServiceProvider;
use Plugins\FollowUp\Support\FollowUpExport;

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

    FollowUp::factory()->create([
        'title' => 'Retornar contato com o fornecedor',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    FollowUp::factory()->create([
        'title' => 'Relatório mensal',
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

test('the table shows the completion date of finished follow-ups', function () {
    $completed = FollowUp::factory()->completed()->create([
        'title' => 'Concluído com data de conclusão',
    ]);

    FollowUp::factory()->create(['title' => 'Pendente sem data de conclusão']);

    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups')
        ->assertOk()
        ->assertSee('Concluído em')
        ->assertSee($completed->completed_at->format('d/m/Y H:i'));
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
        ->assertSee('name="assigned_to"', false)
        ->assertSee('name="modal" value="create"', false);
});

test('a follow-up can be created with its initial timeline entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/follow-ups', [
        'title' => 'Acompanhar aprovação da proposta',
        'description' => 'Validar com o jurídico.',
        'status' => FollowUpStatus::InProgress->value,
        'priority' => FollowUpPriority::Urgent->value,
        'due_date' => now()->addDays(3)->toDateString(),
        'assigned_to' => $user->getKey(),
    ]);

    $followUp = FollowUp::query()->sole();

    $response->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp)
        ->title->toBe('Acompanhar aprovação da proposta')
        ->status->toBe(FollowUpStatus::InProgress)
        ->priority->toBe(FollowUpPriority::Urgent)
        ->assigned_to->toBe($user->getKey())
        ->created_by->toBe($user->getKey())
        ->completed_at->toBeNull();

    expect($followUp->updates()->count())->toBe(1);

    expect($followUp->updates()->first())
        ->body->toBe('Follow-up criado.')
        ->status_to->toBe(FollowUpStatus::InProgress);
});

test('a follow-up created as completed receives a completion date', function () {
    $this->actingAs(User::factory()->create())->post('/admin/follow-ups', [
        'title' => 'Follow-up concluído',
        'status' => FollowUpStatus::Completed->value,
        'priority' => FollowUpPriority::Medium->value,
    ]);

    expect(FollowUp::query()->sole()->completed_at)->not->toBeNull();
});

test('follow-up creation requires title, status and priority', function () {
    $this->actingAs(User::factory()->create())
        ->post('/admin/follow-ups', [])
        ->assertSessionHasErrors(['title', 'status', 'priority']);

    expect(FollowUp::query()->count())->toBe(0);
});

test('the follow-up details page renders its data', function () {
    $followUp = FollowUp::factory()->create([
        'title' => 'Detalhe importante',
        'description' => 'Descrição detalhada do acompanhamento.',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::High->value,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.follow-ups.show', $followUp))
        ->assertOk()
        ->assertSee('Detalhe importante')
        ->assertSee('Descrição detalhada do acompanhamento.')
        ->assertSee('Pendente')
        ->assertSee('Prioridade Alta');
});

test('a follow-up can be updated and status changes are recorded', function () {
    $followUp = FollowUp::factory()->create([
        'title' => 'Atualizar acompanhamento',
        'status' => FollowUpStatus::Pending->value,
        'priority' => FollowUpPriority::Medium->value,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->put(route('admin.follow-ups.update', $followUp), [
            'title' => 'Atualizar acompanhamento',
            'status' => FollowUpStatus::Completed->value,
            'priority' => FollowUpPriority::High->value,
        ]);

    $response->assertRedirect(route('admin.follow-ups.show', $followUp));

    expect($followUp->fresh())
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

    FollowUp::factory()->overdue()->create([
        'title' => 'Follow-up atrasado do usuário',
        'assigned_to' => $user->getKey(),
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
    FollowUp::factory()->create([
        'title' => 'Follow-up exportado pendente',
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
        ->and($text)
        ->toContain('Follow-up exportado pendente')
        ->toContain('Alta')
        ->not->toContain('Follow-up exportado concluído');

    $spreadsheet->disconnectWorksheets();
    @unlink($path);
});

test('a single follow-up can be exported with its history', function () {
    $followUp = FollowUp::factory()->create([
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

test('the table e-mail includes the rows and the excel attachment', function () {
    FollowUp::factory()->create(['title' => 'Linha enviada na tabela']);

    $mail = new FollowUpTableMail(
        FollowUp::query()->with(['assignee', 'creator'])->get(),
    );

    expect($mail->attachments())->toHaveCount(1)
        ->and($mail->render())
        ->toContain('Linha enviada na tabela')
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

    $this->artisan('follow-up:send-daily-summary')->assertSuccessful();

    Mail::assertQueued(FollowUpSummaryMail::class, 2);

    Mail::assertQueued(FollowUpSummaryMail::class, function (FollowUpSummaryMail $mail) use ($creator): bool {
        return $mail->hasTo('criador@example.com')
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
        ->assertSee('name="summary_times"', false)
        ->assertSee('Servidor de e-mail (SMTP)')
        ->assertSee('Enviar e-mail de teste');

    $this->actingAs($admin)
        ->put('/admin/follow-ups/settings', [
            'per_page' => 25,
            'due_soon_days' => 10,
            'widget_limit' => 8,
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Acompanhamento',
            'summary_times' => '09:00, 18:30',
            'mail_enabled' => '0',
        ])
        ->assertRedirect(route('admin.follow-ups.settings'))
        ->assertSessionHas('status');

    $settings = FollowUpSetting::current();

    expect($settings->per_page)->toBe(25)
        ->and($settings->due_soon_days)->toBe(10)
        ->and($settings->widget_limit)->toBe(8)
        ->and($settings->email_summary_enabled)->toBeTrue()
        ->and($settings->email_subject_prefix)->toBe('Acompanhamento')
        ->and($settings->summaryTimes())->toBe(['09:00', '18:30']);
});

test('regular users cannot access the follow-up settings', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/follow-ups/settings')
        ->assertForbidden();
});

test('invalid summary times are rejected', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put('/admin/follow-ups/settings', [
            'per_page' => 15,
            'due_soon_days' => 7,
            'widget_limit' => 5,
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_times' => '25:00',
            'mail_enabled' => '0',
        ])
        ->assertSessionHasErrors('summary_times');
});

test('the smtp credentials are saved encrypted and kept when the password is blank', function () {
    $admin = User::factory()->admin()->create();

    $payload = [
        'per_page' => 15,
        'due_soon_days' => 7,
        'widget_limit' => 5,
        'email_summary_enabled' => '1',
        'email_subject_prefix' => 'Follow-up',
        'summary_times' => '08:00, 17:00',
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
            'email_summary_enabled' => '1',
            'email_subject_prefix' => 'Follow-up',
            'summary_times' => '08:00',
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
