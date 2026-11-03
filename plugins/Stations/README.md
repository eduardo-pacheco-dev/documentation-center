# Stations

Plugin `stations` para o cadastro de estações e elementos de rede.

## Estrutura

- `plugin.json` — manifesto (alias, versão, providers, navegação e dependências).
- `Providers/StationsServiceProvider.php` — registra rotas, views, componentes Blade, traduções, migrations e config.
- `Routes/web.php` — rotas do CRUD em `/admin/stations`, com os nomes `admin.stations.*`.
- `Models/Station.php` — modelo com os 23 campos do inventário e busca por `scopeMatching`.
- `Http/Controllers/StationController.php` — listagem com filtros, cadastro, edição, visualização e exclusão.
- `Http/Requests` — validação compartilhada entre criação e edição.
- `Database/Migrations` — tabela `stations`.
- `Database/Factories` — dados de teste.
- `Database/Seeders/StationSeeder.php` — três registros de referência.
- `Resources/views` — views disponíveis como `stations::index` e componentes como `<x-stations::partials.form>`.
- `Tests/StationTest.php` — testes do CRUD.
- `Config/config.php` — configuração disponível como `config('stations.key')`.

## Campos

`SITE_ID`, `ENDERECO_ID`, `TIPO_DE_ELEMENTO`, `TECNOLOGIA`, `TIPO_DE_CONEXAO`,
`CLASSIFICACAO`, `DETENTOR_AREA`, `TIPO_DE_INFRA`, `TIPO_DE_EV`,
`TIPO_DE_LOGRADOURO`, `LOGRADOURO`, `NUMERO`, `COMPLEMENTO`, `BAIRRO`,
`MUNICIPIO`, `ESTADO`, `CEP`, `REGIONAL`, `LATITUDE`, `LONGITUDE`, `STATUS`,
`TIPO_DA_TORRE` e `STATION_ID`.

Apenas `SITE_ID` é obrigatório. `LATITUDE` e `LONGITUDE` são armazenados como
texto para aceitar coordenadas no formato recebido do inventário.

## Uso

```bash
php artisan plugin:enable stations
php artisan plugin:list
php artisan plugin:disable stations
```

Depois de habilitado, o cadastro fica disponível em `/admin/stations` e no menu
de navegação como **Estações**.

## Dados de exemplo

```bash
php artisan db:seed --class="Plugins\Stations\Database\Seeders\StationSeeder"
```

O seeder não insere nada quando a tabela já possui registros.

## Testes

```bash
php artisan test --compact plugins/Stations/Tests/StationTest.php
```
