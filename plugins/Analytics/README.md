# Analytics & Field Operations

Plugin `analytics` para análise e acompanhamento de ordens de serviço (OC/OS), fornecedores, etapas de processo e status de implantação em telecomunicações.

## Funcionalidades

- Painel em `/admin/analytics` com filtros por período, fornecedor, status, node name e busca.
- KPIs de total de OS, OS aceitas/concluídas e OS pendentes/impeditivas.
- Tabela paginada com os registros importados.
- Importação em lote de planilhas CSV, XLSX e XLS (`maatwebsite/excel`), atualizando registros pela OS.
- Widgets `analytics.overview` (status das OS) e `analytics.supplier` (fornecedores e etapas) no dashboard.
- Factory e seeder para dados de teste.

## Estrutura

- `plugin.json` — manifesto, navegação e provider.
- `Providers/AnalyticsServiceProvider.php` — rotas, views, traduções, migrations, config e widgets.
- `Routes/web.php` — rotas protegidas por `auth` + `admin` em `/admin/analytics`.
- `Models/AnalyticsRecord.php` — modelo, casts e escopos de filtro/status.
- `Database/Migrations` — tabela `analytics_records`.
- `Database/Factories` e `Database/Seeders` — dados de teste.
- `Imports/AnalyticsImport.php` — leitura da planilha.
- `Support/AnalyticsImporter.php` — validação, normalização e upsert em transação.
- `Http/Controllers` e `Http/Requests` — painel, importação, filtros e validações.
- `Widgets` — widgets do dashboard.
- `Resources/views` — painel, importação e widgets.
- `Tests/Feature/AnalyticsTest.php` — testes de feature.

## Instalação

```bash
php artisan plugin:enable analytics
php artisan db:seed --class="Plugins\Analytics\Database\Seeders\AnalyticsSeeder"
```

O comando `plugin:enable` já executa as migrations do plugin.

## Colunas da planilha

`OC`, `OS`, `Data Criação`, `Fornecedor`, `Fornecedor Fiscalização`, `Tipo Projeto`,
`Etapa Processo`, `Tipo Elemento`, `Endereço Atendimento`, `Cidade`, `Estado`,
`Node Name`, `Localização`, `Operador` e `Status`.

Somente `OC`, `OS`, `Data Criação`, `Fornecedor`, `Node Name` e `Status` são obrigatórias.

## Testes

```bash
php artisan test --compact plugins/Analytics/Tests/Feature/AnalyticsTest.php
```
