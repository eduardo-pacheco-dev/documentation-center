# Follow Up

Plugin independente para gerenciar acompanhamentos internos.

## Funcionalidades

- Cadastro, edição, listagem e exclusão de follow-ups.
- Status: pendente, em andamento, concluído e cancelado.
- Prioridades: baixa, média, alta e urgente.
- Responsável, prazo e controle de conclusão.
- Histórico de atualizações com registro automático de mudanças de status.
- Busca por título/descrição e filtros por status, prioridade e responsável.
- Widget **Meus follow-ups** no dashboard.
- Dados isolados em tabelas próprias: `follow_ups` e `follow_up_updates`.

## Estrutura

- `plugin.json` — manifesto e navegação.
- `Providers/FollowUpServiceProvider.php` — rotas, views, componentes, migrations, config e widget.
- `Routes/web.php` — CRUD em `/admin/follow-ups`.
- `Models` — `FollowUp` e `FollowUpUpdate`.
- `Enums` — status e prioridade.
- `Http/Controllers` — CRUD e histórico.
- `Http/Requests` — validação.
- `Database/Migrations` — tabelas do plugin.
- `Database/Factories` e `Database/Seeders` — dados de teste.
- `Resources/views` — telas e widget.
- `Tests/FollowUpTest.php` — testes do plugin.

## Uso

```bash
php artisan plugin:enable follow-up
php artisan db:seed --class="Plugins\FollowUp\Database\Seeders\FollowUpSeeder"
```

## Testes

```bash
php artisan test --compact plugins/FollowUp/Tests/FollowUpTest.php
```
