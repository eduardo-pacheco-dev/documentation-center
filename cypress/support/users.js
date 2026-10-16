// Deterministic accounts created by `Database\Seeders\CypressSeeder`.
// Prepare them with `npm run cypress:db`. The password is always `password`.
//
// Keep the e-mail addresses in sync with `database/seeders/CypressSeeder.php`.

export const adminUser = {
    name: 'Administrador Cypress',
    email: 'cypress.admin@example.com',
    password: 'password',
};

export const regularUser = {
    name: 'Usuario Cypress',
    email: 'cypress.user@example.com',
    password: 'password',
};

export const inactiveUser = {
    name: 'Usuario Inativo Cypress',
    email: 'cypress.inactive@example.com',
    password: 'password',
};
