# Nokia

Plugin `nokia` for this application.

## Structure

- `plugin.json` — manifest (alias, version, providers, dependencies).
- `Providers/NokiaServiceProvider.php` — registers routes, views, translations, migrations and config.
- `Routes/web.php` — site CRUD served under `/admin/nokia/sites`.
- `Resources/views` — views available as `nokia::index`.
- `Resources/lang` — translations available as `nokia::general.name`.
- `Config/config.php` — plugin configuration available as `config('nokia.key')`.

## Usage

```bash
php artisan plugin:enable nokia
php artisan plugin:list
php artisan plugin:disable nokia
```
