import { spawn } from 'node:child_process';
import { defineConfig } from 'cypress';

export default defineConfig({
    e2e: {
        baseUrl: process.env.CYPRESS_BASE_URL ?? 'http://127.0.0.1:8000',
        specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
        supportFile: 'cypress/support/e2e.js',
        fixturesFolder: 'cypress/fixtures',
        video: false,
        retries: {
            runMode: 1,
            openMode: 0,
        },
        setupNodeEvents(on, config) {
            // `cy.exec()` was removed in Cypress 16, so Artisan runs through a
            // task instead. Tasks execute in Node, so they work regardless of
            // the shell or terminal running Cypress.
            on('task', {
                clearAuthThrottle() {
                    return new Promise((resolve, reject) => {
                        // A shell is required because Herd exposes PHP as
                        // `php.bat` on Windows, and batch files cannot be
                        // spawned directly.
                        const child = spawn('php artisan cache:clear --env=cypress', {
                            cwd: config.projectRoot,
                            shell: true,
                        });

                        let output = '';

                        child.stdout.on('data', (chunk) => {
                            output += chunk.toString();
                        });

                        child.stderr.on('data', (chunk) => {
                            output += chunk.toString();
                        });

                        child.on('error', reject);

                        child.on('close', (code) => {
                            if (code === 0) {
                                resolve(output.trim());
                            } else {
                                reject(new Error(`php artisan cache:clear --env=cypress exited with code ${code}:\n${output.trim()}`));
                            }
                        });
                    });
                },
            });

            return config;
        },
    },
});
