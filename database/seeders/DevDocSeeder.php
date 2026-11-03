<?php

namespace Database\Seeders;

use App\Enums\DevDocCategory;
use App\Models\DevDoc;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevDocSeeder extends Seeder
{
    /**
     * Seed the starter development documentation.
     */
    public function run(): void
    {
        $author = User::query()->where('is_admin', true)->first();

        if ($author === null) {
            return;
        }

        $documents = [
            [
                'title' => 'Visão geral do sistema',
                'slug' => 'visao-geral-do-sistema',
                'category' => DevDocCategory::Architecture,
                'summary' => 'Como o sistema base está organizado e quais são os seus principais pontos de extensão.',
                'content' => <<<'MARKDOWN'
# Visão geral

O projeto é uma aplicação **Laravel** com interface em **Blade + Tailwind CSS + Alpine.js** e autenticação própria.

## Camadas principais

- `app/Http/Controllers` — controllers HTTP, incluindo a área administrativa em `Admin`.
- `app/Models` — models Eloquent e relacionamentos.
- `app/Enums` — enums de domínio e módulos administrativos.
- `resources/views` — layouts, componentes Blade e telas.
- `plugins/` — plugins opcionais carregados pelo sistema de plugins.
- `tests/` — testes Pest de feature e unitários.

## Navegação administrativa

Os botões da área administrativa são definidos em `App\Enums\AdminModule` e podem ser:

- habilitados/desabilitados por usuário;
- ordenados;
- movidos entre a sidebar e o menu **Módulos**.

Essas preferências ficam salvas nas colunas `enabled_modules`, `module_order` e `module_sections` da tabela `users`.
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Configuração do ambiente',
                'slug' => 'configuracao-do-ambiente',
                'category' => DevDocCategory::GettingStarted,
                'summary' => 'Passos para preparar o projeto localmente e rodar a aplicação.',
                'content' => <<<'MARKDOWN'
# Configuração do ambiente

## Requisitos

- PHP 8.3+
- Composer
- Node.js e npm
- MySQL/MariaDB (ou SQLite para testes)

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
```

## Desenvolvimento

```bash
composer run dev
```

Ou suba os processos separadamente:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

## Testes

```bash
php artisan test --compact
vendor/bin/pint --dirty
```
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Padrões de código e testes',
                'slug' => 'padroes-de-codigo-e-testes',
                'category' => DevDocCategory::Conventions,
                'summary' => 'Convenções de PHP, Blade e testes usadas no projeto.',
                'content' => <<<'MARKDOWN'
# Padrões de código e testes

## PHP

- Use type hints e tipos de retorno explícitos.
- Prefira `constructor property promotion` e `PHPDoc` para formatos de array.
- Use `FormRequest` quando a validação for relevante ou reutilizável.
- Nunca interpole valores não confiáveis em SQL; use bindings do Eloquent.

## Blade

- Reutilize os componentes `x-ui.*` antes de criar novos.
- Use `{{ }}` para conteúdo não confiável.
- Markdown renderizado é permitido apenas com `html_input: escape`.

## Testes

- Escreva testes de feature para fluxos HTTP.
- Use factories em vez de montar models manualmente.
- Rode o escopo afetado primeiro:

```bash
php artisan test --compact tests/Feature/DevDocTest.php
```
MARKDOWN,
                'is_published' => true,
            ],
            [
                'title' => 'Fluxo de deploy',
                'slug' => 'fluxo-de-deploy',
                'category' => DevDocCategory::Deployment,
                'summary' => 'Resumo do processo de publicação e cuidados antes de subir alterações.',
                'content' => <<<'MARKDOWN'
# Fluxo de deploy

## Antes do deploy

- Rode os testes e o Pint.
- Confira as migrações pendentes.
- Compile os assets com `npm run build`.
- Revise as variáveis de ambiente.

## Publicação

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Depois do deploy

- Valide a página de login e o dashboard.
- Confira os logs em `storage/logs`.
- Reinicie os workers de fila quando houver mudanças de código.
MARKDOWN,
                'is_published' => true,
            ],
        ];

        foreach ($documents as $document) {
            DevDoc::query()->updateOrCreate(
                ['slug' => $document['slug']],
                [
                    ...$document,
                    'user_id' => $author->getKey(),
                ],
            );
        }
    }
}
