<?php

namespace Database\Seeders;

use App\Enums\DevDocCategory;
use App\Models\DevDoc;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevDocSeeder extends Seeder
{
    /**
     * Seed the complete development wiki.
     */
    public function run(): void
    {
        $author = User::query()->where('is_admin', true)->first();

        if ($author === null) {
            return;
        }

        foreach ($this->documents() as $document) {
            DevDoc::query()->updateOrCreate(
                ['slug' => $document['slug']],
                [
                    ...$document,
                    'user_id' => $author->getKey(),
                ],
            );
        }
    }

    /**
     * The documentation pages shipped with the application.
     *
     * @return list<array{title: string, slug: string, category: DevDocCategory, summary: string, content: string, is_published: bool}>
     */
    private function documents(): array
    {
        return [
            [
                'title' => 'Visão geral do sistema',
                'slug' => 'visao-geral-do-sistema',
                'category' => DevDocCategory::GettingStarted,
                'summary' => 'O que é o Documentation Center, quais recursos fazem parte do sistema base e por onde começar.',
                'content' => <<<'MARKDOWN'
# Visão geral do sistema

O **Documentation Center** é uma aplicação Laravel usada como base administrativa e como central de documentação técnica. O sistema base inclui autenticação, painel administrativo, gestão de usuários, configurações pessoais, uma wiki de desenvolvimento e um sistema de plugins.

## Principais recursos

- **Autenticação web** com login, registro, sessão e controle de conta ativa.
- **Dashboard administrativo** com resumo de usuários e plugins.
- **Gestão de usuários** com busca, filtros, ordenação, paginação e modos de visualização.
- **Configurações do usuário**: tema, módulos, senha e exclusão da conta.
- **Navegação configurável**: os módulos podem ser habilitados, desordenados e movidos entre a sidebar e o menu Módulos.
- **Wiki de desenvolvimento**: documentação em Markdown com categorias, busca, rascunhos e publicação.
- **Sistema de plugins**: descoberta, instalação por ZIP, ativação, migrações e remoção.
- **API v1**: autenticação por token Sanctum, consulta do usuário e upload de imagens.
- **Documentação OpenAPI** gerada com l5-swagger.

## Stack

| Camada | Tecnologia |
| --- | --- |
| Linguagem | PHP 8.3+ |
| Framework | Laravel 13 |
| Templates | Blade |
| Estilos | Tailwind CSS 4 |
| Interatividade | Alpine.js via CDN no layout administrativo |
| Build | Vite 8 |
| Banco | MySQL/MariaDB (SQLite nos testes) |
| Autenticação de API | Laravel Sanctum |
| Documentação de API | l5-swagger / OpenAPI |
| Testes | Pest 5 |
| Formatação | Laravel Pint |

## URLs principais

| URL | Descrição |
| --- | --- |
| `/` | Redireciona para `/admin` |
| `/login` | Login web |
| `/register` | Registro web |
| `/admin` | Dashboard administrativo |
| `/admin/users` | Gestão de usuários (admin) |
| `/admin/dev-docs` | Wiki de desenvolvimento (admin) |
| `/settings` | Configurações pessoais |
| `/profile` | Perfil do usuário |
| `/api/documentation` | Interface do Swagger |
| `/docs` | JSON da especificação OpenAPI |

## O que faz parte do core

O core foi mantido enxuto. Plugins de negócio foram removidos e apenas o plugin de exemplo permanece em `plugins/Example`. A aplicação pode ser estendida novamente por plugins sem alterar os módulos principais.

## Onde continuar

- Para preparar o ambiente, consulte **Configuração do ambiente**.
- Para entender o fluxo de uma requisição, consulte **Arquitetura da aplicação**.
- Para criar páginas nesta wiki, consulte **Wiki de desenvolvimento**.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Configuração do ambiente',
                'slug' => 'configuracao-do-ambiente',
                'category' => DevDocCategory::GettingStarted,
                'summary' => 'Requisitos, instalação, banco de dados, assets e comandos para rodar o projeto localmente.',
                'content' => <<<'MARKDOWN'
# Configuração do ambiente

## Requisitos

- PHP 8.3 ou superior com extensões usuais do Laravel (`pdo_mysql`, `mbstring`, `openssl`, `zip` etc.).
- Composer 2.
- Node.js e npm.
- MySQL ou MariaDB para desenvolvimento. Os testes usam SQLite em memória.
- Opcional: Laravel Herd, que já serve o projeto em `https://documentation-center.test`.

## Instalação rápida

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
```

O `composer.json` também oferece um script de setup:

```bash
composer run setup
```

## Banco de dados

Configure o `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=documentation_center
DB_USERNAME=root
DB_PASSWORD=
```

Depois rode as migrações:

```bash
php artisan migrate
```

Para recriar tudo com dados de exemplo:

```bash
php artisan migrate:fresh --seed
```

> Atenção: `migrate:fresh` apaga todas as tabelas e dados.

## Frontend

```bash
npm install
npm run dev   # servidor de desenvolvimento do Vite
npm run build # build de produção
```

Se aparecer o erro **“Unable to locate file in Vite manifest”**, rode `npm run build` ou mantenha `npm run dev` ativo.

## Executando a aplicação

```bash
composer run dev
```

Esse script sobe o servidor PHP, o worker de fila e o Vite em paralelo.

Ou manualmente:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

## Primeiro administrador

Crie um administrador pela linha de comando:

```bash
php artisan user:create-admin admin@example.com --name="Administrador" --password="senha-segura"
```

Ou use o usuário criado pelo `DatabaseSeeder`:

- E-mail: `admin@example.com`
- Senha: `password`

## Testes

```bash
php artisan test --compact
vendor/bin/pint --dirty
```
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Primeiros passos',
                'slug' => 'primeiros-passos',
                'category' => DevDocCategory::GettingStarted,
                'summary' => 'Roteiro inicial para acessar o sistema, criar uma conta administrativa e conhecer as telas principais.',
                'content' => <<<'MARKDOWN'
# Primeiros passos

## 1. Suba a aplicação

```bash
composer run dev
```

Acesse `https://documentation-center.test` ou a URL configurada em `APP_URL`.

## 2. Entre como administrador

Use a conta criada pelo seeder ou crie a sua:

```bash
php artisan user:create-admin admin@example.com --name="Administrador" --password="senha-segura"
```

Credenciais padrão do seeder:

- `admin@example.com` / `password`
- `test@example.com` / `password`

## 3. Conheça o painel

- `/admin` — dashboard com saudação, totais e usuários recentes.
- `/admin/users` — gestão de contas.
- `/admin/dev-docs` — wiki de desenvolvimento.
- `/settings` — aparência, módulos, plugins, senha e conta.
- `/profile` — nome e e-mail.

## 4. Configure a navegação

Em **Configurações → Módulos** você pode:

- habilitar ou desabilitar módulos;
- reordenar os botões;
- mover um botão entre a sidebar e o menu **Módulos**.

O módulo **Início** permanece sempre habilitado.

## 5. Escreva a primeira página da wiki

Acesse `/admin/dev-docs` e clique em **Nova página**. O conteúdo usa Markdown e pode ficar como rascunho antes de ser publicado.

## 6. Explore o plugin de exemplo

```bash
php artisan plugin:list
php artisan plugin:enable example
```

Com o plugin ativo, a rota `/plugins/example` fica disponível.

## 7. Rode a suíte

```bash
php artisan test --compact
```
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Estrutura de diretórios',
                'slug' => 'estrutura-de-diretorios',
                'category' => DevDocCategory::GettingStarted,
                'summary' => 'Mapa das pastas do projeto e a responsabilidade de cada área.',
                'content' => <<<'MARKDOWN'
# Estrutura de diretórios

```text
app/
  Console/Commands/          Comandos Artisan do core e dos plugins
  Enums/                     Enums de domínio e de navegação
  Exceptions/                Exceções específicas da aplicação
  Http/
    Controllers/             Controllers web, admin e API
    Middleware/              Middlewares de conta ativa e administrador
    Requests/                Form Requests de validação
    Resources/               API Resources
  Models/                    Models Eloquent
  Plugins/                   Infraestrutura do sistema de plugins
  Providers/                 Providers da aplicação e dos plugins
bootstrap/                   Bootstrap do Laravel e providers registrados
config/                      Configurações da aplicação
database/
  factories/                 Factories de testes
  migrations/                Migrações do core
  seeders/                   Seeders do core e do conteúdo inicial
plugins/                     Plugins instalados (atualmente Example)
public/                      Document root e assets compilados
resources/
  css/                       Tailwind e estilos próprios
  js/                        JavaScript do frontend
  views/                     Layouts, páginas e componentes Blade
routes/                      Rotas web, API e console
storage/                     Logs, cache, sessões, uploads e status de plugins
tests/                       Pest: Feature, Unit e suporte
deploy.sh                    Script de deploy da VPS
```

## Onde adicionar código

| Necessidade | Local |
| --- | --- |
| Nova tela administrativa | `app/Http/Controllers/Admin` + `resources/views/admin` |
| Nova entidade | `app/Models` + migration + factory |
| Nova regra de validação | `app/Http/Requests` |
| Novo módulo de navegação | `app/Enums/AdminModule.php` + rota + controller |
| Novo comando | `app/Console/Commands` |
| Novo componente visual | `resources/views/components` |
| Novo teste | `tests/Feature` ou `tests/Unit` |
| Nova funcionalidade opcional | `plugins/<Nome>` |

## Observação

As views de paginação em `resources/views/vendor` são publicadas pelo Laravel e não devem ser alteradas sem necessidade.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Arquitetura da aplicação',
                'slug' => 'arquitetura-da-aplicacao',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Fluxo de uma requisição, providers, rotas, middlewares e organização das camadas.',
                'content' => <<<'MARKDOWN'
# Arquitetura da aplicação

## Fluxo de uma requisição web

1. `public/index.php` inicia a aplicação.
2. `bootstrap/app.php` configura rotas, middlewares e tratamento de exceções.
3. `bootstrap/providers.php` registra `AppServiceProvider` e `PluginServiceProvider`.
4. O roteador escolhe a rota em `routes/web.php` ou `routes/api.php`.
5. Os middlewares globais e de rota são executados.
6. O método do controller recebe um `FormRequest` quando há validação dedicada.
7. O controller consulta models e retorna uma view ou JSON.
8. O Blade renderiza o layout com os componentes reutilizáveis.

## Providers

### `AppServiceProvider`

- Define o rate limiter `login` (5 tentativas por minuto por e-mail + IP).
- Define o rate limiter `unlock` (10 tentativas por minuto por IP).
- Em ambiente `cypress`, aponta o Vite para um hot file inexistente para usar o build compilado.

### `PluginServiceProvider`

- Registra `PluginRepository` e `PluginActivator`.
- Percorre todos os plugins habilitados no `register()`.
- Registra providers, aliases, arquivos, configurações e traduções.
- No `boot()`, carrega traduções e arquivos de plugins habilitados.
- Dispara eventos de ciclo de vida como `plugin.<alias>.boot`.

## Rotas

| Arquivo | Uso |
| --- | --- |
| `routes/web.php` | Autenticação, perfil, configurações, área administrativa |
| `routes/api.php` | API v1 com Sanctum |
| `routes/console.php` | Comandos de console e agendador |

A rota `/up` é registrada automaticamente como health check.

## Middlewares

- `EnsureUserIsActive` é anexado ao grupo `web` e encerra sessões de contas desativadas.
- `EnsureUserIsAdmin` é registrado com o alias `admin` e retorna 403 para não administradores.

## Camadas

O projeto não possui uma camada de services/actions generalizada. Os controllers coordenam entrada, validação, consulta e resposta. Quando a lógica crescer, extraia operações coesas para classes próprias em vez de criar abstrações prematuras.

## Tratamento de exceções

`bootstrap/app.php` configura respostas JSON para requisições `api/*` ou que esperam JSON. Não há handler customizado para formulários públicos neste sistema base.

## Decisões arquiteturais

- **Plugins opcionais** ficam em `plugins/` com namespace PSR-4 `Plugins\`.
- **Navegação** é dirigida por `AdminModule`, não por menus hardcoded.
- **Wiki** usa Markdown seguro renderizado no servidor.
- **API** usa tokens Sanctum e respostas JSON padronizadas por Resources.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Autenticação e autorização',
                'slug' => 'autenticacao-e-autorizacao',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Como funcionam login, registro, sessões, tokens de API, conta ativa e permissões de administrador.',
                'content' => <<<'MARKDOWN'
# Autenticação e autorização

## Autenticação web

O login web usa o guard `web` com sessão e o provider Eloquent `App\Models\User`.

- `GET /login` exibe o formulário.
- `POST /login` valida credenciais e cria a sessão.
- `POST /logout` encerra a sessão e invalida o token CSRF.

Após autenticar, o usuário é redirecionado para o destino pretendido ou para `admin.dashboard`.

### Regras importantes

- A conta precisa estar ativa (`is_active = true`).
- Contas desativadas são deslogadas no login e novamente pelo middleware `EnsureUserIsActive`.
- A sessão é regenerada após autenticação.
- Login e registro usam o rate limiter `login`.

## Registro

`GET /register` e `POST /register` permitem criar uma conta.

Validações principais:

- nome obrigatório, máximo 255;
- e-mail válido, único e normalizado para minúsculas;
- senha confirmada e compatível com `Password::defaults()`.

Após o cadastro, o usuário é autenticado automaticamente e redirecionado ao dashboard.

## Autorização

O campo `users.is_admin` define o acesso administrativo.

Rotas protegidas pelo middleware `admin`:

- `/admin/users` e ações de usuário;
- `/admin/dev-docs` e ações da wiki;
- ações de plugins em `/settings/plugins`.

Usuários comuns continuam acessando dashboard, perfil e configurações pessoais.

## API

A API usa Sanctum com tokens Bearer.

- `POST /api/v1/auth/signup`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (autenticado)
- `GET /api/v1/auth/me` (autenticado)

O token é criado com o nome do dispositivo informado em `device_name` (padrão `mobile`). O logout revoga apenas o token atual.

## Senhas

- O cast `hashed` do model `User` faz o hash automaticamente.
- Troca de senha exige a senha atual.
- A nova senha precisa ser diferente da atual.
- A confirmação é obrigatória.
- A exclusão da conta exige a senha atual.

## Segurança de sessão

Configuração padrão do `.env.example`:

```dotenv
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

As sessões ficam na tabela `sessions`. Tokens de API ficam em `personal_access_tokens`.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Navegação e módulos administrativos',
                'slug' => 'navegacao-e-modulos',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Como o menu administrativo é montado, salvo por usuário e estendido por novos módulos.',
                'content' => <<<'MARKDOWN'
# Navegação e módulos administrativos

## `AdminModule`

Os módulos do core são definidos em `App\Enums\AdminModule`:

| Módulo | Valor | Rota | Admin apenas |
| --- | --- | --- | --- |
| Dashboard | `dashboard` | `admin.dashboard` | Não |
| Usuários | `users` | `admin.users.index` | Sim |
| Documentação Dev | `dev-docs` | `admin.dev-docs.index` | Sim |

Cada caso declara:

- `label()` — texto exibido;
- `icon()` — nome do componente `x-icon`;
- `routeName()` — rota principal;
- `activePattern()` — padrão de destaque no menu;
- `isPrimary()` — se aparece na sidebar por padrão;
- `isAdminOnly()` — se é restrito a administradores.

## Preferências por usuário

A tabela `users` guarda três colunas JSON:

| Coluna | Função |
| --- | --- |
| `enabled_modules` | Quais módulos estão habilitados. `null` significa todos. |
| `module_order` | Ordem dos botões na navegação. |
| `module_sections` | Em qual grupo cada botão aparece: `sidebar` ou `modules`. |

O Dashboard permanece sempre habilitado, mesmo que `enabled_modules` não o inclua.

## Comportamento

- `AdminModule::availableFor($user)` remove módulos administrativos para usuários comuns.
- `enabledFor($user)` aplica as preferências e a ordem salva.
- `toggleableFor($user)` lista apenas o que pode ser ligado/desligado nas configurações.
- `sectionFor($user, $module)` decide se o botão fica na sidebar ou no menu **Módulos**.
- `sortForUser($user, $modules)` respeita `module_order`.

## Configurações de módulos

Em `/settings`, o bloco **Módulos** permite:

- habilitar/desabilitar;
- subir/descer dentro do grupo;
- mover entre **Sidebar** e **Menu Módulos**.

O formulário envia `modules[]`, `order[]` e `sections[...]`; a validação fica em `UpdateModulesRequest`.

## Plugins e navegação

Plugins podem declarar itens de navegação no `plugin.json`. O `PluginRepository` expõe:

- `navigation()` — itens dos plugins habilitados;
- `modules()` — chaves de módulos declaradas por qualquer plugin.

A sidebar ignora módulos do core quando um plugin assume a mesma chave, evitando duplicidade.

## Como adicionar um módulo do core

1. Adicione um caso em `AdminModule`.
2. Implemente `label()`, `icon()`, `routeName()` e, se necessário, `isAdminOnly()`.
3. Crie a rota nomeada.
4. Crie o controller e a view.
5. Adicione testes de acesso, navegação e permissão.

O módulo aparecerá automaticamente nas configurações e poderá ser reordenado/movido pelo usuário.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Dashboard e telas principais',
                'slug' => 'dashboard-e-telas',
                'category' => DevDocCategory::Architecture,
                'summary' => 'O que o dashboard exibe e como as telas administrativas estão organizadas.',
                'content' => <<<'MARKDOWN'
# Dashboard e telas principais

## Dashboard

Rota: `GET /admin` (`admin.dashboard`)

O `DashboardController` monta:

- saudação baseada no horário (`Bom dia`, `Boa tarde` ou `Boa noite`);
- total de usuários para administradores;
- total de plugins instalados;
- total de plugins ativos;
- últimos cinco usuários, apenas para administradores.

A view `admin.dashboard` usa o layout `x-layouts.dashboard` e segue o estilo flat do sistema:

- cards com `rounded-3xl` e `border-gray-200/80`;
- sem sombras ou elevação no hover;
- tipografia e espaçamentos consistentes com as configurações.

## Telas administrativas

| Tela | Rota | Acesso |
| --- | --- | --- |
| Dashboard | `/admin` | Autenticado |
| Usuários | `/admin/users` | Admin |
| Wiki — início | `/admin/dev-docs` | Admin |
| Wiki — página | `/admin/dev-docs/{id}` | Admin |
| Wiki — nova página | `/admin/dev-docs/create` | Admin |
| Wiki — editar | `/admin/dev-docs/{id}/edit` | Admin |

## Telas pessoais

| Tela | Rota |
| --- | --- |
| Perfil | `/profile` |
| Configurações | `/settings` |

## Layouts

- `x-layouts.admin` — usado por usuários e wiki.
- `x-layouts.dashboard` — layout com sidebar administrativa e slot principal.
- `x-layouts.auth` — login e registro.
- `x-layouts.app` e `x-layouts.public` — layouts auxiliares.

## Componentes de navegação

- `dashboard.sidebar` — sidebar principal.
- `dashboard.modules-menu` — dropdown de módulos.
- `dashboard.topbar` — barra superior.
- `dashboard.user-menu` — menu do usuário.
- `dashboard.sidebar-item` — item reutilizável.

## Toasts e confirmações

O layout inclui:

- `x-toasts` — mensagens de sucesso/erro da sessão e validação;
- `x-confirm-modal` — modal global de confirmação acionado por `data-confirm-delete`.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Gestão de usuários',
                'slug' => 'gestao-de-usuarios',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Recursos, filtros, validações e proteções da tela de administração de usuários.',
                'content' => <<<'MARKDOWN'
# Gestão de usuários

Rota: `/admin/users` — apenas administradores.

## Listagem

O `UserController@index` suporta:

- busca por nome, sobrenome e e-mail;
- filtro por perfil (`admin` ou `user`);
- filtro por status (`active` ou `inactive`);
- ordenação por `name`, `email`, `is_admin` ou `created_at`;
- direção `asc`/`desc`;
- paginação com 10, 25 ou 50 itens;
- modos de visualização `table`, `cards` e `compact`.

A listagem também exibe estatísticas: total, administradores, ativos e inativos.

## Criação

`POST /admin/users`

Validações principais:

- nome obrigatório;
- sobrenome opcional;
- e-mail válido, único e em minúsculas;
- senha confirmada e compatível com `Password::defaults()`;
- `is_admin` opcional.

## Atualização

`PUT/PATCH /admin/users/{user}`

Permite atualizar nome, sobrenome, e-mail, perfil administrativo e, opcionalmente, a senha. O e-mail continua único, ignorando o próprio usuário.

## Exclusão

`DELETE /admin/users/{user}`

O administrador não pode excluir a própria conta. A ação é confirmada pelo modal global.

## Ativação/desativação

`PATCH /admin/users/{user}/toggle`

Alterna `is_active`. O administrador não pode desativar a própria conta. Contas inativas não conseguem autenticar e sessões existentes são encerradas.

## Criação via CLI

```bash
php artisan user:create-admin admin@example.com --name="Administrador" --password="senha-segura"
```

Se o e-mail já existir, o comando promove o usuário a administrador e garante que a conta esteja ativa.

## Modelo e validações

- `User` usa `#[Fillable]`, `#[Hidden]` e casts para senha, flags e preferências.
- `StoreUserRequest` estende `RegisterRequest`.
- `UpdateUserRequest` define regras próprias, incluindo senha opcional.

## Testes relacionados

`tests/Feature/AdminPanelTest.php` cobre listagem, busca, filtros, paginação, ordenação, modos de visualização, criação, atualização, exclusão, ativação/desativação e bloqueios de autoexclusão/autodesativação.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Configurações do usuário',
                'slug' => 'configuracoes-do-usuario',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Aparência, módulos, plugins, senha, conta e perfil: o que cada seção faz e onde é persistido.',
                'content' => <<<'MARKDOWN'
# Configurações do usuário

Rota: `/settings` (autenticado).

## Aparência

`PUT /settings/theme` → `settings.theme.update`

Valores aceitos: `system`, `light`, `dark`.

Persistido em `users.theme`. O layout aplica o tema antes de renderizar a página para evitar flash de tema incorreto.

## Módulos

`PUT /settings/modules` → `settings.modules.update`

Permite:

- habilitar/desabilitar módulos;
- definir a ordem (`module_order`);
- mover entre sidebar e menu Módulos (`module_sections`).

O Dashboard permanece sempre habilitado. Módulos administrativos só aparecem para administradores.

## Plugins

Disponível apenas para administradores.

- `POST /settings/plugins` — instala um ZIP.
- `PATCH /settings/plugins/{alias}` — habilita/desabilita.
- `DELETE /settings/plugins/{alias}` — remove o plugin e seus arquivos.

A instalação valida o ZIP e exige `plugin.json`. Ao habilitar, o sistema executa migrações e o hook de instalação.

## Senha

`PUT /settings/password` → `settings.password.update`

Regras:

- senha atual obrigatória e correta;
- nova senha confirmada;
- nova senha diferente da atual;
- política padrão de senha do Laravel.

## Conta

`DELETE /settings/account` → `settings.account.destroy`

Exige a senha atual, encerra a sessão e remove o usuário. Documentos da wiki criados por ele são removidos em cascata (`user_id` com `cascadeOnDelete`).

## Perfil

`GET /profile` e `PUT /profile`

Permite atualizar nome e e-mail. O e-mail continua único e normalizado para minúsculas.

## Componentes da tela

- `resources/views/settings/show.blade.php`
- `resources/views/settings/partials/plugins.blade.php`
- `resources/views/profile/show.blade.php`

## Testes

- `tests/Feature/SettingsTest.php`
- `tests/Feature/SettingsModulesTest.php`
- `tests/Feature/ProfileTest.php`
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Sistema de plugins',
                'slug' => 'sistema-de-plugins',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Manifesto, descoberta, ativação, migrações, instalação por ZIP, remoção e ciclo de vida dos plugins.',
                'content' => <<<'MARKDOWN'
# Sistema de plugins

O sistema segue o modelo de módulos do Akaunting: cada plugin é uma mini aplicação Laravel dentro de `plugins/`, com namespace PSR-4 `Plugins\`.

## Estrutura de um plugin

```text
plugins/Example/
  plugin.json
  composer.json              (opcional)
  Config/config.php
  Providers/ExampleServiceProvider.php
  Routes/web.php
  Resources/lang/en/general.php
  Resources/views/index.blade.php
  Database/Migrations/       (opcional)
  Tests/                     (opcional)
```

## Manifesto

Exemplo de `plugin.json`:

```json
{
    "name": "Example",
    "alias": "example",
    "description": "Plugin de exemplo.",
    "version": "1.0.0",
    "icon": "cube",
    "active": false,
    "priority": 10,
    "providers": ["Plugins\\Example\\Providers\\ExampleServiceProvider"],
    "aliases": [],
    "files": [],
    "requires": []
}
```

Campos relevantes:

| Campo | Uso |
| --- | --- |
| `alias` | Identificador kebab-case, validado por regex |
| `providers` | Service providers registrados |
| `aliases` | Aliases de classes |
| `files` | Arquivos PHP incluídos no autoload |
| `requires` | Dependências de outros plugins |
| `navigation` | Itens de menu contribuídos |
| `lifecycle` | Classe opcional de hooks install/uninstall |
| `priority` | Ordem de descoberta |

## Descoberta

`PluginRepository` varre `config('plugins.paths.plugins')` (padrão `base_path('plugins')`), ignora diretórios sem `plugin.json` e ordena por prioridade e alias.

## Status

O estado habilitado/desabilitado fica em `storage/plugin_statuses.json` (ou em `PLUGIN_STATUSES`). Ele não altera o `plugin.json` versionado.

Se não houver status salvo, o campo `active` do manifesto é usado.

## Ciclo de vida

Eventos disparados:

- `plugin.<alias>.enabling`, `enabled`
- `plugin.<alias>.disabling`, `disabled`
- `plugin.<alias>.installed`
- `plugin.<alias>.removed`
- `plugin.<alias>.boot`

Hooks opcionais em uma classe que implementa `App\Plugins\Contracts\PluginLifecycle`:

- `install()` — executado ao habilitar;
- `uninstall()` — executado antes de remover.

Se o install falhar, o plugin é desabilitado novamente. Se o uninstall falhar, os arquivos não são removidos.

## Comandos

```bash
php artisan plugin:list [--only=all|enabled|disabled]
php artisan plugin:make meu-plugin
php artisan plugin:install caminho/plugin.zip
php artisan plugin:enable meu-plugin
php artisan plugin:disable meu-plugin
php artisan plugin:remove meu-plugin [--force]
```

`plugin:make` cria manifesto, provider, rotas, view, traduções, config e README. Rode `composer dump-autoload` após criar um plugin.

## Instalação por ZIP

`PluginInstaller`:

1. abre o ZIP;
2. rejeita caminhos absolutos, `..` e bytes nulos;
3. localiza o `plugin.json` na raiz ou em um único diretório;
4. valida o alias com `^[a-z0-9]+(?:-[a-z0-9]+)*$`;
5. move para `plugins/<StudlyName>`;
6. dispara evento de instalação.

Não é permitido sobrescrever um plugin já instalado.

## Migrações

`PluginMigrator` registra os providers do plugin antes de rodar `Database/Migrations`. As migrações usam a tabela padrão `migrations` do Laravel.

## Navegação de plugins

Um plugin pode declarar itens de menu. A sidebar só exibe itens cujo `route` exista e cujo `module` esteja habilitado para o usuário.

## Helpers

```php
plugin();                    // PluginRepository
plugin('example');           // Plugin|null
plugin_version('example');   // versão
plugin_path('example', 'Routes/web.php');
plugin_attribute('example', 'name');
```

## Testes

`tests/Feature/PluginSystemTest.php` cobre descoberta, status, comandos, instalação, remoção, migrações, hooks e validações de ZIP.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Wiki de desenvolvimento',
                'slug' => 'wiki-de-desenvolvimento',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Como o módulo de documentação funciona, seu modelo de dados, categorias, Markdown e publicação.',
                'content' => <<<'MARKDOWN'
# Wiki de desenvolvimento

Rota: `/admin/dev-docs` — apenas administradores.

## Objetivo

Centralizar a documentação técnica do projeto em páginas Markdown editáveis pela interface.

## Modelo

`App\Models\DevDoc` — tabela `dev_docs`.

| Coluna | Tipo | Uso |
| --- | --- | --- |
| `id` | bigint | Chave primária |
| `user_id` | foreignId | Autor |
| `title` | string | Título |
| `slug` | string unique | Identificador estável |
| `category` | string index | Categoria |
| `summary` | text nullable | Resumo |
| `content` | longText | Markdown |
| `is_published` | boolean | Publicado ou rascunho |
| `created_at`, `updated_at` | timestamps | Auditoria |

O autor é acessado por `$document->author`. A categoria é convertida para o enum `DevDocCategory`.

## Categorias

`App\Enums\DevDocCategory`:

- Primeiros passos
- Arquitetura
- Banco de dados
- API
- Frontend
- Testes
- Deploy
- Convenções

Para criar uma categoria, adicione um caso ao enum e seu label.

## Funcionalidades

- listagem em layout de wiki com sidebar;
- busca por título, resumo e conteúdo;
- filtro por categoria;
- criação, edição e exclusão;
- rascunhos e publicação;
- slug único gerado a partir do título;
- contadores compactos de páginas, publicadas, rascunhos e categorias;
- navegação lateral agrupada por categoria;
- breadcrumb na página do artigo.

## Renderização Markdown

O controller usa:

```php
Str::markdown($devDoc->content, [
    'html_input' => 'escape',
    'allow_unsafe_links' => false,
]);
```

Isso permite Markdown rico, mas escapa HTML inserido pelo autor e bloqueia links inseguros.

## Rotas

| Método | Rota | Ação |
| --- | --- | --- |
| GET | `/admin/dev-docs` | Listagem |
| GET | `/admin/dev-docs/create` | Formulário |
| POST | `/admin/dev-docs` | Criar |
| GET | `/admin/dev-docs/{devDoc}` | Visualizar |
| GET | `/admin/dev-docs/{devDoc}/edit` | Editar |
| PUT/PATCH | `/admin/dev-docs/{devDoc}` | Atualizar |
| DELETE | `/admin/dev-docs/{devDoc}` | Excluir |

## Seeders

`DevDocSeeder` publica o conteúdo inicial da wiki. Para recriar o conteúdo:

```bash
php artisan db:seed --class=DevDocSeeder --force
```

O seeder usa `updateOrCreate` pelo `slug`, portanto não duplica páginas existentes.

## Interface

- `resources/views/admin/dev-docs/index.blade.php`
- `resources/views/admin/dev-docs/show.blade.php`
- `resources/views/admin/dev-docs/create.blade.php`
- `resources/views/admin/dev-docs/edit.blade.php`
- `resources/views/components/admin/dev-docs/form.blade.php`
- `resources/views/components/admin/dev-docs/wiki-sidebar.blade.php`

## Testes

`tests/Feature/DevDocTest.php` cobre acesso, CRUD, validação, busca, filtro, slug estável e escape de HTML no Markdown.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Comandos Artisan',
                'slug' => 'comandos-artisan',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Comandos próprios do projeto, comandos de plugins e rotinas úteis do dia a dia.',
                'content' => <<<'MARKDOWN'
# Comandos Artisan

## Comandos do projeto

### `user:create-admin`

Cria um administrador ou promove um usuário existente.

```bash
php artisan user:create-admin admin@example.com --name="Administrador" --password="senha-segura"
```

- valida e-mail;
- valida a política de senha;
- ativa a conta;
- se o e-mail já existir, promove a administrador.

### Plugin

```bash
php artisan plugin:list
php artisan plugin:list --only=enabled
php artisan plugin:make meu-plugin
php artisan plugin:install caminho/plugin.zip
php artisan plugin:enable meu-plugin
php artisan plugin:disable meu-plugin
php artisan plugin:remove meu-plugin --force
```

### `inspire`

Comando de exemplo do Laravel:

```bash
php artisan inspire
```

## Comandos frequentes

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed --class=DevDocSeeder --force
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan queue:restart
php artisan l5-swagger:generate
```

## Agendador

O arquivo `routes/console.php` registra apenas o comando `inspire`. Não há tarefas agendadas ativas no core.

Para adicionar uma tarefa:

```php
Schedule::command('minha:rotina')->daily();
```

E garanta que o cron do servidor execute `php artisan schedule:run` a cada minuto.

## Testes e formatação

```bash
php artisan test --compact
php artisan test --compact tests/Feature/DevDocTest.php
vendor/bin/pint --dirty
```
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Banco de dados e migrações',
                'slug' => 'banco-de-dados',
                'category' => DevDocCategory::Database,
                'summary' => 'Tabelas do core, colunas importantes, convenções e como criar novas migrações.',
                'content' => <<<'MARKDOWN'
# Banco de dados e migrações

## Conexões

A conexão padrão é definida por `DB_CONNECTION` (MySQL no `.env.example`). Os testes usam SQLite em memória (`:memory:`).

Cache, sessões e filas usam o banco por padrão:

```dotenv
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Tabelas do core

| Tabela | Função |
| --- | --- |
| `users` | Contas e preferências de navegação |
| `password_reset_tokens` | Tokens de redefinição de senha |
| `sessions` | Sessões web |
| `cache`, `cache_locks` | Cache e locks |
| `jobs`, `job_batches`, `failed_jobs` | Fila |
| `personal_access_tokens` | Tokens Sanctum |
| `dev_docs` | Páginas da wiki |

## Tabela `users`

Colunas relevantes:

| Coluna | Uso |
| --- | --- |
| `name`, `surname` | Nome completo |
| `email` | Login, único |
| `password` | Hash |
| `is_admin` | Acesso administrativo |
| `is_active` | Conta ativa |
| `theme` | `system`, `light` ou `dark` |
| `enabled_modules` | Módulos habilitados (JSON) |
| `module_order` | Ordem dos módulos (JSON) |
| `module_sections` | Grupo de cada módulo (JSON) |
| `email_verified_at` | Data de verificação (não usado no fluxo atual) |

## Tabela `dev_docs`

- `slug` é único;
- `category` tem índice;
- existe índice composto `category + is_published`;
- `user_id` remove os documentos em cascata quando o autor é excluído.

## Criando migrações

```bash
php artisan make:migration create_exemplo_table
php artisan make:migration add_campo_to_users_table
```

Boas práticas:

- use `foreignId()->constrained()` com ação de exclusão explícita;
- não edite migrações já publicadas em produção;
- teste `migrate` e `migrate:fresh --seed`;
- adicione índices baseados em consultas reais;
- mantenha `down()` coerente quando a reversão for segura.

## Testes

Os testes usam `RefreshDatabase` e executam todas as migrações em SQLite em memória. Se uma migração falhar, toda a suíte de feature falha.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Modelo de usuário',
                'slug' => 'modelo-de-usuario',
                'category' => DevDocCategory::Database,
                'summary' => 'Campos, casts, accessors, preferências de navegação e factory do model User.',
                'content' => <<<'MARKDOWN'
# Modelo de usuário

Arquivo: `app/Models/User.php`.

## Configuração

```php
#[Fillable(['name', 'surname', 'email', 'password', 'theme', 'enabled_modules', 'module_order', 'module_sections'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
```

O model usa:

- `HasApiTokens` (Sanctum);
- `HasFactory`;
- `Notifiable`.

## Atributos padrão

```php
protected $attributes = [
    'is_active' => true,
];
```

## Casts

| Campo | Cast |
| --- | --- |
| `email_verified_at` | `datetime` |
| `password` | `hashed` |
| `is_admin` | `boolean` |
| `is_active` | `boolean` |
| `enabled_modules` | `array` |
| `module_order` | `array` |
| `module_sections` | `array` |

## Accessor

`full_name` retorna `name` + `surname` sem espaços extras:

```blade
{{ $user->full_name }}
```

## Navegação

- `enabledModules(): array` — valores dos módulos habilitados;
- `hasModule(string $module): bool` — verifica se o módulo está habilitado.

Esses métodos delegam para `AdminModule`.

## Factory

`UserFactory` define:

- nome e e-mail únicos;
- senha padrão `password`;
- e-mail verificado;
- estado `admin()` para `is_admin = true`;
- estado `unverified()` para remover a verificação.

```php
$admin = User::factory()->admin()->create();
```

## Observações

- O model não declara relacionamentos de negócio.
- `DevDoc` referencia o autor via `user_id` e `author()`.
- A exclusão de um usuário remove seus documentos da wiki em cascata.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Seeders e factories',
                'slug' => 'seeders-e-factories',
                'category' => DevDocCategory::Database,
                'summary' => 'Dados iniciais, contas de teste, factories e como recriar o banco.',
                'content' => <<<'MARKDOWN'
# Seeders e factories

## `DatabaseSeeder`

Cria:

- `admin@example.com` com `is_admin = true`;
- `test@example.com` como usuário comum;
- chama `DevDocSeeder` para publicar a wiki.

```bash
php artisan migrate:fresh --seed
```

## `DevDocSeeder`

Publica todas as páginas da wiki de desenvolvimento.

```bash
php artisan db:seed --class=DevDocSeeder --force
```

Usa `updateOrCreate` pelo `slug`, então pode ser executado novamente sem duplicar.

## `CypressSeeder`

Cria contas determinísticas para os testes E2E:

| Conta | E-mail | Perfil |
| --- | --- | --- |
| Administrador Cypress | `cypress.admin@example.com` | Admin |
| Usuario Cypress | `cypress.user@example.com` | Comum |
| Usuario Inativo Cypress | `cypress.inactive@example.com` | Inativo |

Senha de todas: `password`.

O script `npm run cypress:db` recria o banco de testes e executa esse seeder.

## `UserSeeder`

Cria 25 usuários aleatórios. Não é chamado pelo `DatabaseSeeder`, mas pode ser executado manualmente:

```bash
php artisan db:seed --class=UserSeeder --force
```

## Factories

### `UserFactory`

- `admin()` — administrador;
- `unverified()` — e-mail não verificado;
- senha padrão `password`.

### `DevDocFactory`

- cria documento publicado por padrão;
- `draft()` — cria rascunho.

## Testes

Os testes usam factories em vez de dados fixos. Prefira estados existentes antes de montar atributos manualmente.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'API de autenticação',
                'slug' => 'api-autenticacao',
                'category' => DevDocCategory::Api,
                'summary' => 'Endpoints de signup, login, logout e me, tokens Sanctum, erros e exemplos de uso.',
                'content' => <<<'MARKDOWN'
# API de autenticação

Prefixo: `/api/v1`  
Controller: `App\Http\Controllers\Api\AuthController`

## Endpoints

| Método | Rota | Autenticação |
| --- | --- | --- |
| POST | `/api/v1/auth/signup` | Pública |
| POST | `/api/v1/auth/login` | Pública |
| POST | `/api/v1/auth/logout` | Bearer token |
| GET | `/api/v1/auth/me` | Bearer token |

## Signup

```http
POST /api/v1/auth/signup
Content-Type: application/json

{
  "name": "Maria Silva",
  "email": "maria@example.com",
  "password": "senha-segura",
  "password_confirmation": "senha-segura",
  "device_name": "Galaxy S24"
}
```

Resposta `201`:

```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Maria Silva",
      "email": "maria@example.com",
      "is_admin": false,
      "created_at": "..."
    },
    "token": "1|..."
  }
}
```

## Login

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "maria@example.com",
  "password": "senha-segura",
  "device_name": "Galaxy S24"
}
```

Credenciais inválidas retornam `401`. O token é criado via Sanctum e deve ser enviado como `Authorization: Bearer <token>`.

## Me

```http
GET /api/v1/auth/me
Authorization: Bearer <token>
```

Retorna o usuário autenticado no formato de `UserResource`.

## Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer <token>
```

Revoga apenas o token atual.

## Rate limiting

Signup e login usam o limiter `login`:

- 5 tentativas por minuto;
- chave: e-mail + IP.

## Erros

Requisições `api/*` recebem JSON. Validações retornam `422` com `message` e `errors`.

## Testes

`tests/Feature/ApiAuthTest.php` cobre signup, validações, e-mail duplicado, login, credenciais inválidas, proteção por token, logout e rate limit.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'API de uploads',
                'slug' => 'api-uploads',
                'category' => DevDocCategory::Api,
                'summary' => 'Endpoint de upload de imagens, validação, disco público e resposta.',
                'content' => <<<'MARKDOWN'
# API de uploads

## Endpoint

```http
POST /api/v1/uploads
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

Campo: `file`.

## Validação

- obrigatório;
- precisa ser arquivo;
- precisa ser imagem;
- tamanho máximo: 20 MB (20480 KB).

## Armazenamento

O arquivo é salvo no disco `public`, pasta `uploads`:

```php
$path = $request->file('file')->store('uploads', 'public');
```

O disco `public` aponta para `storage/app/public` e é exposto em `/storage` após:

```bash
php artisan storage:link
```

## Resposta

`201 Created`

```json
{
  "url": "https://documentation-center.test/storage/uploads/foto.jpg",
  "path": "https://documentation-center.test/storage/uploads/foto.jpg"
}
```

## Observações

- A rota exige token Sanctum.
- O endpoint é destinado a imagens; outros tipos são rejeitados.
- Não há endpoint de remoção de upload no core.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Documentação OpenAPI',
                'slug' => 'documentacao-openapi',
                'category' => DevDocCategory::Api,
                'summary' => 'Como o Swagger é gerado, onde acessar e como documentar novos endpoints.',
                'content' => <<<'MARKDOWN'
# Documentação OpenAPI

A aplicação usa `darkaonline/l5-swagger` para gerar a especificação OpenAPI a partir de atributos PHP.

## Onde acessar

| URL | Conteúdo |
| --- | --- |
| `/api/documentation` | Interface Swagger UI |
| `/docs` | JSON da especificação |

## Geração

```bash
php artisan l5-swagger:generate
```

A configuração está em `config/l5-swagger.php`.

- As anotações são lidas de `app/`.
- O arquivo padrão é `storage/api-docs/api-docs.json`.
- `L5_SWAGGER_GENERATE_ALWAYS=true` regenera a documentação automaticamente.

## Como documentar um endpoint

1. Adicione atributos `OpenApi\Attributes` no controller.
2. Declare `OA\Info`, `OA\Server` e `OA\SecurityScheme` quando necessário.
3. Use `OA\Schema` para request/response.
4. Rode `php artisan l5-swagger:generate`.
5. Verifique `/api/documentation`.

Os controllers `Api\AuthController` e `Api\UploadController` são os exemplos atuais.

## Teste

`tests/Feature/ApiDocumentationTest.php` garante que a interface e o JSON estão disponíveis.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Frontend, Vite e Tailwind',
                'slug' => 'frontend-e-build',
                'category' => DevDocCategory::Frontend,
                'summary' => 'Build com Vite, Tailwind 4, Alpine via CDN, fontes e módulos JavaScript.',
                'content' => <<<'MARKDOWN'
# Frontend, Vite e Tailwind

## Entradas do Vite

`vite.config.js` declara:

```js
input: ['resources/css/app.css', 'resources/js/app.js']
```

- `resources/css/app.css` importa Tailwind 4, define o tema e estilos da wiki.
- `resources/js/app.js` contém os comportamentos globais.

## Tailwind

- Tailwind 4 via `@tailwindcss/vite`.
- Tema customizado em `@theme` (fontes, cores, tamanhos e raio).
- Variante dark via `@custom-variant dark`.
- Fontes Instrument Sans carregadas pelo plugin de fontes do Vite.

## Alpine.js

O layout administrativo carrega Alpine via CDN:

```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
```

A sidebar, dropdowns, modais e a ordenação de módulos usam Alpine.

## JavaScript global

`resources/js/app.js` contém:

- **Toasts** — notificações de sessão/validação;
- **ConfirmModal** — confirmação global;
- **PasswordVisibility** — alternância de visibilidade de senha;
- **LoginForm** — comportamento do formulário de login.

Formulários com `data-confirm-delete` passam pelo modal de confirmação.

## Comandos

```bash
npm run dev
npm run build
```

Em produção, os assets ficam em `public/build`.

## Cypress

No ambiente `cypress`, `AppServiceProvider` aponta o Vite para um hot file inexistente para usar o build compilado.

## Problemas comuns

Se o Blade falhar com **Unable to locate file in Vite manifest**:

```bash
npm run build
```

ou mantenha `npm run dev` em execução.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Componentes e layouts Blade',
                'slug' => 'componentes-blade',
                'category' => DevDocCategory::Frontend,
                'summary' => 'Organização dos layouts, componentes de UI e padrões para criar novas telas.',
                'content' => <<<'MARKDOWN'
# Componentes e layouts Blade

## Layouts

| Componente | Uso |
| --- | --- |
| `x-layouts.admin` | Telas administrativas |
| `x-layouts.dashboard` | Layout principal com sidebar |
| `x-layouts.auth` | Login e registro |
| `x-layouts.app` | Layout auxiliar |
| `x-layouts.public` | Páginas públicas/plugins |

## Componentes globais

- `x-toasts` — container de notificações.
- `x-confirm-modal` — modal de confirmação.
- `x-relative-time` — data relativa.
- `x-pagination` — paginação padronizada.
- `x-sort-header` — cabeçalhos ordenáveis.
- `x-searchable-select` — select com busca.
- `x-icon` — ícones SVG por nome.

## Componentes `x-ui.*`

- `action`, `alert`, `avatar`, `badge`, `button`, `checkbox`;
- `dropdown`, `empty-state`, `field`, `field-error`, `field-help`, `field-label`;
- `icon-button`, `kbd`, `modal`, `page-header`, `progress-bar`, `search-input`, `tooltip`.

Reutilize esses componentes antes de criar novos.

## Dashboard

- `x-dashboard.brand`
- `x-dashboard.sidebar`
- `x-dashboard.sidebar-item`
- `x-dashboard.sidebar-toggle`
- `x-dashboard.modules-menu`
- `x-dashboard.topbar`
- `x-dashboard.user-menu`

## Wiki

- `x-admin.dev-docs.form` — formulário compartilhado de criação/edição.
- `x-admin.dev-docs.wiki-sidebar` — navegação lateral da wiki.

## Padrões

- Use `{{ }}` para texto não confiável.
- Use `{!! !!}` apenas para HTML sanitizado, como o Markdown da wiki.
- Prefira `@class([...])` para classes condicionais.
- Em componentes, declare `@props`.
- Evite lógica de banco nas views; passe dados pelo controller.
- Use `@vite` apenas nos layouts.

## Ícones

Os ícones são declarados em `resources/views/components/icon.blade.php`. Para um novo ícone, adicione um `@case` com o SVG correspondente.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Testes e qualidade',
                'slug' => 'testes-e-qualidade',
                'category' => DevDocCategory::Testing,
                'summary' => 'Como a suíte Pest está organizada, como rodar testes e como manter o código formatado.',
                'content' => <<<'MARKDOWN'
# Testes e qualidade

## Stack

- Pest 5 sobre PHPUnit;
- Laravel Testing;
- SQLite em memória;
- RefreshDatabase nos testes de feature;
- factories para dados.

## Estrutura

```text
tests/
  Feature/       Fluxos HTTP, autenticação, admin, wiki, API, plugins
  Unit/          Testes unitários simples
  Support/       Apoio a testes de plugins
  Fixtures/      Status de plugins para testes
  Pest.php       Configuração global do Pest
  TestCase.php   Base dos testes
```

Plugins podem ter testes próprios em `plugins/<Nome>/Tests`; o `phpunit.xml` inclui essa pasta.

## Bancos e ambiente

`phpunit.xml` define:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="CACHE_STORE" value="array"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="PLUGIN_STATUSES" value="tests/Fixtures/plugin_statuses.json"/>
```

## Comandos

```bash
php artisan test --compact
php artisan test --compact tests/Feature/DevDocTest.php
php artisan test --compact --filter=nome_do_teste
vendor/bin/pest
```

## Formatação

```bash
vendor/bin/pint --dirty --format agent
```

Rode o Pint antes de finalizar qualquer alteração em PHP.

## Cobertura atual

A suíte cobre, entre outros:

- login, registro, logout e sessão;
- conta ativa e bloqueio de administrador;
- CRUD e filtros de usuários;
- perfil e configurações;
- wiki de desenvolvimento;
- API de autenticação e documentação;
- sistema de plugins (instalação, remoção, migrações e hooks).

## Boas práticas

- Use factories e estados existentes.
- Teste o fluxo HTTP real quando possível.
- Prefira `assertViewHas` para verificar dados do controller quando a página contém muitos elementos.
- Rode o escopo afetado primeiro e a suíte completa ao final.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Deploy',
                'slug' => 'deploy',
                'category' => DevDocCategory::Deployment,
                'summary' => 'Como o deploy funciona na VPS, o que o script executa e quais cuidados tomar.',
                'content' => <<<'MARKDOWN'
# Deploy

## Automação

O workflow `.github/workflows/deploy.yml` executa em push para `main`.

Ele conecta na VPS via SSH e executa:

```bash
git fetch --prune origin main
git checkout origin/main -- deploy.sh
bash deploy.sh
```

## O que o `deploy.sh` faz

1. Atualiza o repositório para `origin/main` (fast-forward ou reset).
2. Verifica o `.env` e a conexão de banco.
3. Executa `composer install --no-dev --optimize-autoloader`.
4. Limpa caches com `php artisan optimize:clear --except=cache`.
5. Garante diretórios graváveis em `storage`, `bootstrap/cache` e `plugins`.
6. Gera `APP_KEY` se estiver vazio.
7. Entra em manutenção.
8. Executa `npm ci && npm run build`.
9. Gera caches de config, rotas e views.
10. Cria o link de storage.
11. Executa `php artisan migrate --force`.
12. Limpa o cache da aplicação.
13. Configura/reinicia o worker de fila via systemd.

## Requisitos do servidor

- PHP, Composer, Node.js e npm;
- MySQL/MariaDB;
- Nginx ou outro servidor web apontando para `public/`;
- permissões de escrita em `storage`, `bootstrap/cache` e `plugins`;
- segredos do GitHub: `SSH_HOST`, `SSH_USERNAME`, `SSH_PRIVATE_KEY`, `SSH_PORT`, `SERVER_PATH`, `WEB_USER`.

## Worker de fila

O script instala um serviço systemd quando executado como root:

```bash
php artisan queue:work --sleep=3 --tries=1 --timeout=1900 --max-time=3600 --memory=1024
```

Sem systemd, garanta um worker manual e rode `php artisan queue:restart` após deploys.

## Checklist antes do deploy

```bash
php artisan test --compact
vendor/bin/pint --dirty
npm run build
```

- Migrações revisadas.
- `.env` de produção correto.
- `APP_DEBUG=false`.
- Backup do banco, quando necessário.

## Rollback

O script prioriza `git merge --ff-only` e faz `git reset --hard origin/main` se necessário. Para reverter uma versão, faça um novo commit de correção e faça deploy novamente.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Variáveis de ambiente',
                'slug' => 'variaveis-de-ambiente',
                'category' => DevDocCategory::Deployment,
                'summary' => 'Principais chaves do .env, valores padrão e impacto de cada configuração.',
                'content' => <<<'MARKDOWN'
# Variáveis de ambiente

O arquivo de referência é `.env.example`. Nunca commite `.env`.

## Aplicação

| Variável | Padrão | Uso |
| --- | --- | --- |
| `APP_NAME` | `Laravel` | Nome exibido |
| `APP_ENV` | `local` | Ambiente |
| `APP_KEY` | vazio | Criptografia/sessão |
| `APP_DEBUG` | `true` | Debug detalhado |
| `APP_URL` | `http://localhost:8000` | URL base |
| `APP_LOCALE` | `en` | Idioma |
| `APP_FAKER_LOCALE` | `en_US` | Faker |

## Banco

| Variável | Padrão |
| --- | --- |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `documentation_center` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | vazio |

## Sessão, cache e fila

| Variável | Padrão |
| --- | --- |
| `SESSION_DRIVER` | `database` |
| `SESSION_LIFETIME` | `120` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |

## Arquivos e e-mail

| Variável | Padrão |
| --- | --- |
| `FILESYSTEM_DISK` | `local` |
| `MAIL_MAILER` | `log` |

Uploads da API usam o disco `public`.

## Documentação e plugins

| Variável | Uso |
| --- | --- |
| `L5_SWAGGER_GENERATE_ALWAYS` | Regenera o Swagger |
| `PLUGIN_STATUSES` | Caminho do JSON de status |
| `PLUGIN_CACHE_ENABLED` | Cache de status/manifestos |

## Vite

| Variável | Uso |
| --- | --- |
| `VITE_APP_NAME` | Nome no frontend |
| `VITE_DEV_SERVER_CERT` | Certificado HTTPS do dev server |
| `VITE_DEV_SERVER_KEY` | Chave HTTPS |
| `VITE_DEV_SERVER_ORIGIN` | Origem pública atrás de proxy |

## Config cache

Em produção, após alterar o `.env`, rode:

```bash
php artisan config:clear
php artisan config:cache
```

Ou `php artisan optimize:clear`.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Convenções de código',
                'slug' => 'convencoes-de-codigo',
                'category' => DevDocCategory::Conventions,
                'summary' => 'Padrões de PHP, Laravel, Blade, nomes, validação e organização adotados no projeto.',
                'content' => <<<'MARKDOWN'
# Convenções de código

## PHP

- Sempre use chaves em estruturas de controle, mesmo de uma linha.
- Use constructor property promotion.
- Declare tipos de parâmetro e retorno.
- Prefira PHPDoc a comentários inline.
- Use `TitleCase` para casos de enum.
- Use nomes descritivos: `isRegisteredForDiscounts`, não `discount()`.
- Use `mb_*` ou helpers do `Str` para strings UTF-8.
- Prefira `validated()` ou `safe()->only()` a `$request->all()`.

## Laravel

- Use `php artisan make:*` para criar arquivos.
- Controllers devem coordenar HTTP, validação, operação e resposta.
- Use Form Requests quando a validação for relevante ou reutilizável.
- Use route model binding.
- Use nomes de rota em `route()`.
- Use `back()->with('status', ...)` para feedback.
- Use migrations com chaves estrangeiras explícitas.
- Use factories e seeders para dados de teste.

## Nomes

| Elemento | Convenção | Exemplo |
| --- | --- | --- |
| Controller | Singular | `DevDocController` |
| Model | Singular StudlyCase | `DevDoc` |
| Tabela | Plural snake_case | `dev_docs` |
| Coluna | snake_case | `is_published` |
| Rota | plural | `/admin/dev-docs` |
| Enum | Singular | `DevDocCategory` |
| View | kebab-case | `wiki-sidebar.blade.php` |
| Variável | camelCase | `$navigationDocument` |

## Blade

- Componentes em `resources/views/components`.
- Reutilize `x-ui.*`.
- Use `x-bind:class` para expressões Alpine.
- Use `@props` em componentes.
- Não consulte banco diretamente na view.
- Escape saída com `{{ }}`; use `{!! !!}` somente para HTML seguro.

## Formatação

```bash
vendor/bin/pint --dirty --format agent
```

## Testes

- Pest 5.
- Testes no escopo afetado primeiro.
- Factories em vez de dados manuais.
- Rode a suíte completa antes de finalizar.

## Git

- Só faça commit/push com autorização explícita.
- Trabalhe em branches `feature/...`.
- Use mensagens imperativas em inglês.
- Não commite `.env`, `storage` gerado, `vendor`, `node_modules`, `public/build` ou `public/hot`.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Segurança',
                'slug' => 'seguranca',
                'category' => DevDocCategory::Conventions,
                'summary' => 'Autenticação, autorização, CSRF, rate limiting, validação, uploads, Markdown e plugins.',
                'content' => <<<'MARKDOWN'
# Segurança

## Autenticação e sessão

- Senhas são hasheadas pelo cast `hashed`.
- Sessões são regeneradas após login.
- Logout invalida a sessão e o token CSRF.
- Contas inativas são deslogadas no login e pelo middleware `EnsureUserIsActive`.
- Rotas da API usam tokens Sanctum.

## Autorização

- O middleware `admin` protege rotas administrativas.
- Ações de usuários, wiki e plugins exigem `is_admin`.
- O administrador não pode excluir ou desativar a própria conta.
- Form Requests também verificam autorização quando aplicável (`DevDocRequest`, `UploadPluginRequest`).

## CSRF

Todos os formulários web de escrita usam `@csrf`. Requisições de API usam token Bearer e não dependem do CSRF de sessão.

## Rate limiting

- Login/registro: 5 tentativas por minuto por e-mail + IP.
- `unlock`: 10 tentativas por minuto por IP.

## Validação

- Validação centralizada em Form Requests.
- E-mails são normalizados para minúsculas e únicos.
- Senhas seguem `Password::defaults()`.
- Uploads de plugin exigem ZIP e `plugin.json`.

## Uploads

- API aceita apenas imagens de até 20 MB.
- Arquivos ficam no disco `public`, pasta `uploads`.
- Plugins ZIP são extraídos em diretório temporário.
- O instalador rejeita caminhos absolutos, `..` e bytes nulos.
- O removedor valida que o plugin está dentro de `plugins/`.

## Markdown da wiki

A wiki usa:

```php
Str::markdown($content, [
    'html_input' => 'escape',
    'allow_unsafe_links' => false,
]);
```

Isso evita HTML arbitrário e links perigosos.

## Segredos

- Nunca commite `.env`.
- Use variáveis de ambiente e `config()` em vez de credenciais no código.
- Faça rotação de chaves e tokens quando necessário.

## Dependências

Rode periodicamente:

```bash
composer audit
npm audit
```
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Fluxo de contribuição',
                'slug' => 'fluxo-de-contribuicao',
                'category' => DevDocCategory::Conventions,
                'summary' => 'Como preparar uma alteração, testar, formatar, commitar e publicar com segurança.',
                'content' => <<<'MARKDOWN'
# Fluxo de contribuição

## Antes de começar

1. Leia `.ai/rules/index.md` e as regras que cobrem os arquivos alterados.
2. Verifique o estado do git.
3. Trabalhe em uma branch `feature/...`.

```bash
git switch -c feature/minha-alteracao
```

## Implementação

- Siga as convenções de código.
- Prefira reutilizar componentes e padrões existentes.
- Adicione ou atualize testes quando houver mudança de comportamento.
- Não altere dependências sem autorização.

## Verificação

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact tests/Feature/MeuTeste.php
php artisan test --compact
npm run build
```

## Commit

Commit só com autorização explícita do usuário.

- Mensagem imperativa em inglês.
- Sem prefixos.
- Corpo curto explicando o motivo.

Exemplo:

```text
Add developer documentation module

Provide an admin-only Markdown knowledge base with categories, search,
drafts, publishing and starter documentation for the project.
```

Nunca commite:

- `.env`;
- `storage` gerado;
- `vendor`;
- `node_modules`;
- `public/build`;
- `public/hot`.

## Push e PR

Push só com autorização explícita.

```bash
git push -u origin feature/minha-alteracao
```

Não abra PR, tag ou force push sem autorização.

## Deploy

O deploy é disparado por push na `main`, via GitHub Actions e `deploy.sh`. Finalize o trabalho na branch, valide e só faça merge/push quando autorizado.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Troubleshooting',
                'slug' => 'troubleshooting',
                'category' => DevDocCategory::Conventions,
                'summary' => 'Soluções para os erros mais comuns de ambiente, assets, permissões, plugins e banco.',
                'content' => <<<'MARKDOWN'
# Troubleshooting

## “Unable to locate file in Vite manifest”

```bash
npm run build
```

Ou mantenha `npm run dev` ativo.

## Classe de plugin não encontrada

Exemplo: `Plugins\WorkOrders\Models\WorkOrder` não existe.

- Confirme que a pasta do plugin existe.
- Rode `composer dump-autoload`.
- Rode `php artisan optimize:clear`.
- Verifique se o plugin não foi removido.

## 403 em `/admin/...`

- O usuário precisa ter `is_admin = true`.
- Confirme em `/admin/users` ou crie com `user:create-admin`.

## Login redireciona de volta com “conta desativada”

- Verifique `users.is_active`.
- Ative a conta em `/admin/users` ou com o comando de administrador.

## 419 Page Expired

- Token CSRF expirado.
- Recarregue a página e envie novamente.
- Verifique `SESSION_DRIVER` e permissões de storage.

## Erro de conexão com banco

- Confira `DB_*` no `.env`.
- Rode `php artisan config:clear`.
- Teste `php artisan migrate:status`.

## Plugin não habilita

- Rode `php artisan plugin:list` para confirmar a descoberta.
- Verifique migrações do plugin.
- Veja o log em `storage/logs/laravel.log`.
- Se o install hook falhar, o plugin volta para desabilitado.

## Rotas não atualizam

```bash
php artisan route:clear
php artisan optimize:clear
```

## Permissões no servidor

```bash
chmod -R ug+rwX storage bootstrap/cache plugins
```

Garanta que o usuário do PHP-FPM seja o dono desses diretórios.

## Filas paradas

```bash
php artisan queue:restart
```

Verifique se o worker systemd está ativo.

## Wiki sem o módulo na navegação

- Verifique se o usuário é administrador.
- Em **Configurações → Módulos**, confirme que **Documentação Dev** está habilitado.
- Verifique se o botão não foi movido para um grupo inesperado.
- Não confunda rascunho com página inexistente: rascunhos aparecem na wiki para administradores.

## Como investigar

- `storage/logs/laravel.log`
- Logs do servidor web/PHP-FPM
- `php artisan about`
- `php artisan route:list`
- `php artisan test --compact`
MARKDOWN,
                'is_published' => true,
            ],
        ];
    }
}
