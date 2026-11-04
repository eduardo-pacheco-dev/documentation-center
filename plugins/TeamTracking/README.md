# Team Tracking

Plugin independente para acompanhamento de equipes, membros e saúde do time.

## Funcionalidades

- CRUD de equipes com área, líder, status e data de início.
- Gestão de membros com papel (membro/líder).
- Registros de acompanhamento com data, status, saúde (1–5), resumo e próximos passos.
- Histórico de acompanhamentos por equipe.
- Filtros por nome/área e status.
- Widget **Equipes** no dashboard.
- Tabelas isoladas: `teams`, `team_user` e `team_check_ins`.

## Estrutura

- `Models` — `Team` e `TeamCheckIn`.
- `Enums` — status da equipe e do acompanhamento.
- `Http/Controllers` — equipes, membros e acompanhamentos.
- `Http/Requests` — validação.
- `Database` — migrations, factories e seeder.
- `Resources/views` — telas e widget.
- `Tests/TeamTrackingTest.php` — testes do plugin.

## Uso

```bash
php artisan plugin:enable team-tracking
php artisan db:seed --class="Plugins\TeamTracking\Database\Seeders\TeamTrackingSeeder"
```

Acesso: `/admin/teams`.
