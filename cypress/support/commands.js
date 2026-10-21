// ***********************************************
// Custom commands shared across the end-to-end suite.
// https://docs.cypress.io/api/cypress-api/custom-commands
// ***********************************************

/**
 * Clear the login rate limiter.
 *
 * `POST /login` and `POST /register` are throttled to 5 attempts per minute per
 * e-mail + IP (see `AppServiceProvider`). The limiter is stored in the database
 * cache, so clearing that store before each spec keeps the authenticated
 * scenarios deterministic without touching application code.
 */
Cypress.Commands.add('resetAuthThrottle', () => {
    cy.task('clearAuthThrottle');
});

/**
 * Sign in through the real login form.
 */
Cypress.Commands.add('uiLogin', (email, password, { remember = false } = {}) => {
    cy.visit('/login');
    cy.get('#email').type(email);
    cy.get('#password').type(password);

    if (remember) {
        cy.get('input[name="remember"]').check();
    }

    cy.get('form[action$="/login"] button[type="submit"]').click();
});

/**
 * Sign out through the sidebar account menu.
 *
 * On small viewports the sidebar starts behind the mobile menu trigger, so it
 * has to be opened before the account menu is reachable.
 */
Cypress.Commands.add('uiLogout', () => {
    cy.get('body').then(($body) => {
        const trigger = $body.find('button[aria-label="Abrir menu"]:visible');

        if (trigger.length) {
            cy.wrap(trigger).click();
        }
    });

    cy.get('aside button[aria-label="Menu do usuário"]').click({ force: true });
    cy.get('form[action$="/logout"] button[type="submit"]').click({ force: true });
});
