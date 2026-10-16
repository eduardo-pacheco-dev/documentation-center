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
 * Sign out through the account dropdown in the admin header.
 *
 * The dropdown is rendered by Alpine (loaded from a CDN), so the final click is
 * forced: it still submits the real form even if the menu never opened.
 */
Cypress.Commands.add('uiLogout', () => {
    cy.get('header button:has(img)').click();
    cy.get('form[action$="/logout"] button[type="submit"]').click({ force: true });
});
