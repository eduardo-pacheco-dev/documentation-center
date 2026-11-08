# Agenda

Plugin `agenda` for this application.

## Structure

- `plugin.json` — manifest (alias, version, providers, dependencies).
- `Providers/AgendaServiceProvider.php` — registers routes, views, translations, migrations, config, command and policies.
- `Routes/web.php` — agenda served under `/admin/agenda`.
- `Resources/views` — views available as `agenda::index` and partials as `agenda::partials.*`.
- `Resources/lang` — translations available as `agenda::general.name`.
- `Config/config.php` — plugin configuration available as `config('agenda.key')`.
- `Database/Migrations` — plugin-owned tables (`agenda_calendars`, `agenda_events`, `agenda_event_*`).

## Independence

The plugin owns its enums, models, controllers, requests, policies, services,
migrations and views. It does not depend on other plugins and does not rely on
relations added to the core user model; ownership is scoped through the
`ownedBy` and `accessibleBy` query scopes on `AgendaCalendar` and `AgendaEvent`.

## Commands

```bash
php artisan agenda:dispatch-reminders --dry-run
php artisan agenda:dispatch-reminders
```

## Usage

```bash
php artisan plugin:enable agenda
php artisan plugin:migrate agenda
php artisan plugin:list
php artisan plugin:disable agenda
```
