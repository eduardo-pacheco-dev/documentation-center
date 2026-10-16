import { adminUser, regularUser } from '../../support/users';

describe('Authentication - Route guards', () => {
    it('redirects guests away from the dashboard', () => {
        cy.visit('/admin');

        cy.location('pathname').should('eq', '/login');
    });

    it('redirects guests from the root URL to the login page', () => {
        cy.visit('/');

        cy.location('pathname').should('eq', '/login');
    });

    it('redirects authenticated users away from the guest-only pages', () => {
        cy.uiLogin(adminUser.email, adminUser.password);
        cy.location('pathname').should('eq', '/admin');

        cy.visit('/login');
        cy.location('pathname').should('eq', '/admin');

        cy.visit('/register');
        cy.location('pathname').should('eq', '/admin');
    });

    it('forbids non-admin members from admin-only routes', () => {
        cy.uiLogin(regularUser.email, regularUser.password);
        cy.location('pathname').should('eq', '/admin');

        cy.request({ url: '/admin/users', failOnStatusCode: false })
            .its('status')
            .should('eq', 403);
    });

    it('allows administrators into admin-only routes', () => {
        cy.uiLogin(adminUser.email, adminUser.password);
        cy.location('pathname').should('eq', '/admin');

        cy.visit('/admin/users');

        cy.location('pathname').should('eq', '/admin/users');
        cy.contains('h1', 'Usuários').should('be.visible');
    });
});
