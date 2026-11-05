# AGENTS.md — Documentation Center

Guia para agentes de IA que trabalham neste repositório. Leia antes de planejar ou editar qualquer arquivo.

## Visão geral

O **Documentation Center** é uma aplicação interna de documentação e administração construída em Laravel 13, com painel em português e arquitetura de **plugins** inspirada no Akaunting. O core oferece autenticação, usuários, módulos, dashboard com widgets, configurações, notificações e armazenamento; os plugins adicionam domínios independentes (estações, follow-ups, analytics etc.).

- Aplicação local (Herd): `http://documentation-center.test`
- Não execute servidor web — o Herd já serve a aplicação
- Use `get-absolute-url` (Laravel Boost) antes de compartilhar URLs

## Stack

| Camada | Tecnologia |
| --- | --- |
| PHP | `^8.3` no `composer.json` (ambiente local: 8.6) |
| Framework | Laravel `^13.17` |
| Frontend | Blade + Tailwind CSS 4 + Alpine.js (CDN) + Vite 8 |
| Build | `npm run build` (produção) / `npm run dev` (HMR) |
| Testes | Pest 5 + Pest Laravel |
| Formatação | Laravel Pint |
| Banco | MySQL (`documentation_center`); testes usam SQLite em memória |
| Fila | `database` |
| Planilhas | `maatwebsite/excel` 4 |
| Storage | local + AWS S3, Azure Blob e Cloudflare R2 |
| Deploy | `deploy.sh` via GitHub Actions ao dar push na `main` |

Confirme versões instaladas antes de usar APIs sensíveis:

```bash
composer show --direct
cat package.json
php artisan config:show app.name
```

## Comandos essenciais

```bash
# Primeira instalação
composer setup

# Desenvolvimento (servidor + fila + Vite)
composer run dev

# Build dos assets
npm run build

# Testes
php artisan test --compact
php artisan test --compact tests/Feature/AlgumTest.php
vendor/bin/pest --filter=testName

# Formatação (obrigatório após alterar PHP)
vendor/bin/pint --dirty --format agent

# E2E (Cypress)
npm run test:e2e
```

Para criar arquivos, prefira `php artisan make:*` com `--no-interaction`:

```bash
php artisan make:controller NomeController --no-interaction
php artisan make:request StoreNomeRequest --no-interaction
php artisan make:test --pest NomeTest --no-interaction
php artisan make:model Nome -m --no-interaction
```

## Estrutura da aplicação

```text
app/
  Console/          Comandos Artisan (inclui plugin:*)
  Dashboard/        Widget base, registry e widgets do core
  Enums/            AdminModule (navegação e permissões)
  Http/             Controllers, Requests e Controllers/Api
  Models/           User, Setting, StorageSetting, DevDoc
  Notifications/    SystemNotification e PluginNotifier
  Plugins/          Núcleo do sistema de plugins + helpers
  Providers/        App, Plugin e Dashboard service providers
  Settings/         SystemSettings, StorageSettings, SettingsSectionRegistry
  View/             Componentes class-based (ex.: notificações)
plugins/            Plugins independentes (PSR-4 Plugins\)
resources/views/    Layouts, componentes x-ui.*, telas e widgets
routes/web.php      Rotas web e do painel
routes/api.php      API v1 (Sanctum)
database/           Migrations, factories e seeders do core
tests/Feature/      Testes do core
plugins/*/Tests/    Testes de cada plugin (também rodam na suíte)
```

## Navegação e módulos

- O menu é controlado por `App\Enums\AdminModule` (`Dashboard`, `Users`, `DevDocs`) combinado com `users.enabled_modules`, `module_order` e `module_sections`.
- O Dashboard é sempre habilitado; módulos administrativos só aparecem para `is_admin`.
- Plugins contribuem com itens de navegação via `navigation` no `plugin.json` e podem declarar um `module` para respeitar a preferência do usuário.

## Sistema de plugins

- Cada plugin fica em `plugins/<StudlyName>/` e é descrito por `plugin.json` (`alias`, `version`, `providers`, `files`, `navigation`, `requires`, `lifecycle`, `storage`).
- O `PluginServiceProvider` registra provedores, aliases, arquivos e traduções dos plugins habilitados.
- Estado habilitado/desabilitado fica em `storage/plugin_statuses.json` (fora do versionamento).
- Comandos:

```bash
php artisan plugin:list
php artisan plugin:make <alias>
php artisan plugin:enable <alias>
php artisan plugin:disable <alias>
php artisan plugin:install <zip>
php artisan plugin:remove <alias>
```

- Testes de plugin ficam em `plugins/<Nome>/Tests/` e são descobertos pelo `phpunit.xml`.

### Helpers disponíveis para plugins

```php
plugin();                                  // PluginRepository
plugin('alias');                           // Plugin
plugin_attribute('alias', 'version');      // atributo do manifesto
plugin_version('alias');                   // versão
plugin_path('alias', 'Resources/views');   // caminho no plugin
plugin_storage('alias');                   // sandbox de armazenamento isolado
plugin_notifier('alias');                  // envio de notificações
```

### Widgets de plugins

Estenda `App\Dashboard\Widget` e registre em `WidgetRegistry` no provider:

```php
public function id(): string;          // ex.: "analytics.overview"
public function title(): string;
public function view(): string;        // ex.: "analytics::widgets.overview"
public function data(Request $request): array;
public function order(): int;          // menor renderiza antes
public function columnSpan(): int;     // 1 a 3
public function visible(Request $request): bool;
public function description(): ?string;
public function icon(): ?string;
```

O usuário pode ocultar, reordenar por drag and drop e redimensionar widgets; a ordem e a largura ficam por usuário.

### Seções de configuração de plugins

Use `SettingsSectionRegistry` + `SettingsSection` para contribuir um card em `/settings`.

## Dashboard

- Core widgets em `app/Dashboard/Widgets`.
- Layout por usuário: `dashboard_hidden_widgets`, `dashboard_widget_order`, `dashboard_widget_spans`.
- Reordenação via drag and drop (Alpine) e persistência em `PUT /admin/widgets/order`.
- Redimensionamento em `PUT /admin/widgets/{widget}/layout`.

## Configurações

- **Aparência**: tema por usuário (`users.theme`).
- **Módulos**: quais módulos aparecem e em qual seção.
- **Notificações**: preferência de e-mail (`users.email_notifications`).
- **Sistema** (admin): fuso horário global (`settings`).
- **Armazenamento** (admin): provedor dos plugins em `storage_settings`, credenciais criptografadas.
- **Plugins** (admin): instalação, ativação e remoção.

## Notificações

- `App\Notifications\SystemNotification` grava no banco e, conforme a preferência do usuário, envia e-mail.
- `App\Notifications\PluginNotifier` permite que plugins enviem notificações:
  - `plugin_notifier('alias')->send($users, $title, $body, $url, $level)`
  - `plugin_notifier('alias')->toAdmins($title, $body, $url, $level)`
- Níveis: `info`, `success`, `warning`, `danger`.
- URLs aceitam apenas `http(s)` ou caminhos relativos iniciados por `/`.
- Central em `/notifications`, sino na sidebar e contador de não lidas.

## Armazenamento

- `App\Settings\StorageSettings` resolve o provedor ativo e configura os discos.
- Provedores: local (`plugins`), AWS S3, Azure Blob e Cloudflare R2.
- Novos arquivos usam o provedor ativo; se incompleto, cai para o disco local.
- `plugin_storage('alias')` isola os arquivos em `plugins/{alias}/...` e bloqueia travessia de diretório.
- Plugins podem sobrescrever o disco no manifesto: `"storage": { "disk": "s3" }`.

## Convenções de código

- Siga o padrão dos arquivos vizinhos; não introduza um segundo padrão para o mesmo problema.
- Use tipos explícitos em parâmetros e retornos; promoção de construtor; chaves em todas as estruturas de controle.
- Prefira PHPDoc a comentários inline; descreva arrays com array shapes.
- Use `route()` e rotas nomeadas; nunca hardcode URLs.
- Validação em Form Requests dedicados quando houver regra relevante; `authorize()` quando houver permissão.
- Controllers retornam `back()->with('status', ...)` após mutações.
- Blade: componentes anônimos em `resources/views/components`, layouts em `components/layouts`; reutilize `x-ui.*` antes de criar novos.
- UI segue o padrão do módulo de Tarefas/Dashboard: cards `rounded-3xl`, inputs `h-10 rounded-xl`, foco indigo, suporte a dark mode.
- Em Blade, `{{ }}` para escapar; `{!! !!}` apenas com conteúdo sanitizado.
- Use `@csrf` em formulários e `@method` para PUT/DELETE.
- Alpine: `x-bind:class` para expressões; `:attr` é avaliação PHP no Blade.

## Testes e qualidade

- Todo comportamento novo ou corrigido deve ter teste de regressão quando fizer sentido.
- Mudanças apenas de texto, estilo ou layout não exigem teste novo.
- Use factories e estados existentes (`User::factory()->admin()` etc.).
- Rode o escopo mais estreito primeiro: `php artisan test --compact <arquivo>`.
- Ao finalizar PHP: `vendor/bin/pint --dirty --format agent`.
- Não crie scripts de verificação ou use tinker quando houver teste cobrindo o comportamento.
- Não apague testes sem aprovação.
- A suíte completa atual tem cerca de 344 testes; rode `php artisan test --compact` antes de entregar quando possível.

## Git e entrega

**Nunca** execute `git commit`, `git push`, merge, tag, reset destrutivo ou reescrita de histórico sem autorização explícita do usuário na conversa atual.

- Trabalhe em branch `feature/...`; nunca commite direto na `main`.
- Antes de commitar: revise `git status`/`git diff`, rode Pint e os testes do escopo.
- Commits em inglês, imperativos e sem prefixo (ex.: `Add configurable storage providers for plugins`).
- Não commite segredos, `.env`, `storage/*` gerados, `vendor/`, `node_modules/`, `public/build/` ou `public/hot`.
- Push só com autorização explícita: `git push -u origin <branch>`.
- Push na `main` dispara o deploy automático (`.github/workflows/deploy.yml` → `deploy.sh`).

## Deploy

- `deploy.sh` faz fast-forward/reset para `origin/main`, instala dependências, compila assets, cacheia config/rotas/views, roda migrations e configura worker + scheduler via systemd.
- `storage`, `bootstrap/cache` e `plugins` precisam ser graváveis pelo usuário do PHP-FPM.
- O deploy exige MySQL/MariaDB (falha se `DB_CONNECTION=sqlite`).

## Regras do projeto

Antes de planejar ou editar, leia `.ai/rules/index.md` e os arquivos de regra cujos globs cobrem os caminhos em edição. Hoje:

- `routes/*.php` → `.ai/rules/routes.md` (parâmetros explícitos em `Route::resource`).

Se `.ai/rules` mudar, siga o índice atualizado.

## Banco de dados

- Não use SQL cru em tinker quando houver `database-query` do Boost.
- Consulte o schema com `database-schema` antes de migrations/models.
- Prefira factories e seeders existentes em testes.

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
