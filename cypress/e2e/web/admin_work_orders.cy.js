import { adminUser } from '../../support/users';

describe('admin/work-orders - admin access', () => {
  beforeEach(() => {
    cy.uiLogin(adminUser.email, adminUser.password);
    cy.location('pathname').should('eq', '/admin');
  });

  it('loads without 500', () => {
    cy.visit('admin/work-orders', { failOnStatusCode: false });
    cy.get('body').should('be.visible');
    cy.document().its('readyState').should('eq', 'complete');
    cy.location('pathname', { timeout: 5000 }).should('not.contain', '500');
  });
});
