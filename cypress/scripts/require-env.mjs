import { existsSync } from 'node:fs';

// Guard against destructive Artisan commands running with `--env=cypress` when
// `.env.cypress` is missing: Laravel silently falls back to `.env`, which would
// point the test suite (and `migrate:fresh`) at the development database.
if (!existsSync('.env.cypress')) {
    console.error('\n[cypress] Missing .env.cypress file.');
    console.error('[cypress] Copy .env.cypress.example to .env.cypress and set the test database credentials.\n');

    process.exit(1);
}

// The `cypress` environment always serves the compiled assets (see
// `AppServiceProvider`), so the build manifest has to be present.
if (!existsSync('public/build/manifest.json')) {
    console.error('\n[cypress] Missing public/build/manifest.json.');
    console.error('[cypress] Run `npm run build` before running the end-to-end suite.\n');

    process.exit(1);
}
