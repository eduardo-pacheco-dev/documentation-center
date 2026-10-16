import { adminUser } from '../../support/users';

describe('Authentication - Logout', () => {
    it('signs the user out through the account dropdown', () => {
        cy.uiLogin(adminUser.email, adminUser.password);
        cy.location('pathname').should('eq', '/admin');

        cy.uiLogout();

        cy.location('pathname').should('eq', '/login');
        cy.contains('h1', 'Entrar no painel').should('be.visible');
    });

    it('denies access to the dashboard once signed out', () => {
        cy.uiLogin(adminUser.email, adminUser.password);
        cy.uiLogout();
        cy.location('pathname').should('eq', '/login');

        cy.visit('/admin');

        cy.location('pathname').should('eq', '/login');
    });
});
