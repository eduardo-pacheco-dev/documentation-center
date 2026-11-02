---
name: Documentation Center
description: "Regras e contexto do projeto Documentation Center (Laravel 13). Use ao trabalhar em qualquer tarefa neste repositório: análise, correções, features de backend ou Blade, UI, testes, plugins, banco, deploy e comandos. Contém a política obrigatória de Git deste projeto, que exige autorização explícita do usuário antes de commit ou push."
metadata:
  author: documentation-center
---

# Documentation Center

## Regra obrigatória: Git somente com autorização

**Nunca execute `git commit`, `git push`, `git push --force`, criação/merge de pull request, tags, `git reset --hard`, `rebase` ou reescrita de histórico sem autorização explícita do usuário na conversa atual.**

- Autorização é por ação e por momento:
  - "faça o commit" autoriza **apenas** o commit daquele conjunto de mudanças.
  - Autorização de commit **não** autoriza push.
  - Autorização de push **não** autoriza novos commits nem alterações adicionais.
  - Uma autorização anterior **não** se estende para turnos futuros; peça de novo.
- Pedidos ambíguos ("salve isso", "termine", "pode seguir") **não** são autorização. Pergunte: "Quer que eu faça o commit?".
- Antes de commitar, mesmo autorizado:
  1. `git status` e `git diff` para revisar exatamente o que entra.
  2. Rodar `vendor/bin/pint --dirty --format agent`.
  3. Rodar os testes do escopo afetado (`php artisan test --compact <arquivo>`).
  4. Commitar apenas arquivos relacionados à tarefa, sem incluir mudanças alheias.
- Nunca use `--no-verify`, nunca force push, nunca commite direto em `main` (trabalhe em branch `feature/...`).
- Mensagens de commit: imperativas, em inglês, sem prefixo (ex.: `Add plugin installation from ZIP archives`), com corpo curto explicando o motivo.
- Nunca commite segredos ou artefatos: `.env`, `storage/*` gerados (incluindo `storage/plugin_statuses.json`), `vendor/`, `node_modules/`, `public/build/`, `public/hot`.
- Push só com autorização explícita: `git push -u origin <branch>` e informe a URL para abrir o PR; não abra PR sem autorização.
- Se o usuário pedir para "desfazer" um commit, confirme o método (revert x reset) antes de agir.

## Contexto do projeto

- Laravel 13, PHP 8.4, Blade + Tailwind 4 + Alpine (CDN no layout do dashboard), Pest 5.
- App em `/root/workstation/documentation-center`, servida por Nginx + php-fpm em `http://documentation.brazil.vps-kinghost.net`.
- Autenticação própria (`Auth\AuthenticatedSessionController`); administração via `users.is_admin` e middleware `admin`.
- Menu do painel é dirigido por `App\Enums\AdminModule` + coluna `users.enabled_modules`.
- Banco local em SQLite (`database/database.sqlite`).
- Sistema de plugins no estilo Akaunting: `plugins/`, manifesto `plugin.json`, PSR-4 `Plugins\`, helpers `plugin()`/`plugin_path()`/`plugin_version()`, comandos `plugin:list`, `plugin:enable`, `plugin:disable`, `plugin:make`, `plugin:install`, `plugin:remove`, e gestão em `/settings#plugins`.

## Padrões de código

- Leia `.ai/rules/index.md` e todas as regras cujos globs cobrem os arquivos em edição antes de escrever código.
- Rotas: sempre fixe parâmetros plurais irregulares com `->parameters([...])` (ver `.ai/rules/routes.md`).
- Controllers: `$this->authorize(...)` em toda action, escopo `ownedBy($request->user())`, retorno com `back()->with('status', ...)`.
- Requests dedicados em `app/Http/Requests`; validação nunca inline em controllers grandes.
- Blade: componentes anônimos em `resources/views/components`, layouts em `components/layouts`; reutilize `x-ui.*` antes de criar novos.
- UI: siga o padrão do módulo de Tarefas — `x-ui.page-header`, cards `rounded-3xl border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]`, `x-ui.action`, `x-ui.badge`, `x-ui.empty-state`, inputs `h-10 rounded-xl bg-gray-50` com foco indigo.
- Em componentes Blade, use `x-bind:class` para expressões Alpine; `:attr` significa avaliação PHP.

## Qualidade

- Testes de plugin ficam dentro do próprio plugin: `plugins/<Nome>/Tests/*Test.php` (já cobertos pelo `phpunit.xml` e pelo `tests/Pest.php`). Testes do core continuam em `tests/Feature` e `tests/Unit`.
- Rode `vendor/bin/pint --dirty --format agent` antes de finalizar qualquer mudança em PHP.
- Use `php artisan test --compact <caminho>` para o escopo afetado e peça ao usuário a suíte completa.
- Não crie scripts de verificação ou tinker quando já existe teste cobrindo o comportamento.
- Só crie documentação quando o usuário pedir.

## Ambiente local

- Desenvolvimento: `composer run dev` ou PM2 (`dc-server`, `dc-queue`, `dc-vite`).
- Preparar dados: `php artisan migrate:fresh --seed --force`.
- `storage`, `bootstrap/cache` e `plugins` precisam ser graváveis por `www-data` (php-fpm); o `deploy.sh` já ajusta isso em produção.
