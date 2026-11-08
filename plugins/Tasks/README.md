# Tasks

Plugin `tasks` for this application.

## Structure

- `plugin.json` — manifest (alias, version, providers, dependencies).
- `Providers/TasksServiceProvider.php` — registers routes, views, components, translations, migrations, config and policies.
- `Routes/web.php` — task workspace served under `/admin/tasks` and task lists under `/admin/todo-lists`.
- `Resources/views` — views available as `tasks::index` and components as `x-tasks::partials.*`.
- `Resources/lang` — translations available as `tasks::general.name`.
- `Config/config.php` — plugin configuration available as `config('tasks.key')`.
- `Database/Migrations` — plugin-owned tables (`todo_lists`, `todo_tasks`, `todo_steps`).

## Independence

The plugin owns its models, controllers, requests, policies, migrations and
views. It does not depend on any other plugin or on core user relations.
Ownership is scoped through the `ownedBy` query scopes on `TodoList` and
`TodoTask`.

## Usage

```bash
php artisan plugin:enable tasks
php artisan plugin:migrate tasks
php artisan plugin:list
php artisan plugin:disable tasks
```
