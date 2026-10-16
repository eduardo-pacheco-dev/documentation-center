import { existsSync } from 'node:fs';

// Guard against destructive Artisan commands running with `--env=cypress` when
// `.env.cypress` is missing: Laravel silently falls back to `.env`, which would
// point the test suite (and `migrate:fresh`) at the development database.
if (!existsSync('.env.cypress')) {
    console.error('\n[cypress] Missing .env.cypress file.');
    console.error('[cypress] Copy .env.cypress.example to .env.cypress and set the test database credentials.\n');

    process.exit(1);
}
