import { adminUser } from '../../support/users';

describe('profile - admin access', () => {
  it('loads without 500', () => {
    cy.uiLogin(adminUser.email, adminUser.password);
    cy.location('pathname').should('eq', '/admin');

    cy.visit('/profile');
    cy.location('pathname').should('eq', '/profile');
    cy.get('body').should('be.visible');
    cy.document().its('readyState').should('eq', 'complete');
  });
});

describe('profile - guest redirect', () => {
  it('redirects guest to login if protected', () => {
    cy.clearCookies();
    cy.clearLocalStorage();

    cy.visit('/profile');
    cy.location('pathname').should('eq', '/login');
  });
});
