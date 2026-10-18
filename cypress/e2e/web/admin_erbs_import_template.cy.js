import { adminUser } from '../../support/users';

describe('admin/erbs/import/template (file download)', () => {
  beforeEach(() => {
    cy.uiLogin(adminUser.email, adminUser.password);
    cy.location('pathname').should('eq', '/admin');
  });

  it('streams the import template file', () => {
    cy.request('GET', '/admin/erbs/import/template').then((response) => {
      expect(response.status).to.eq(200);
      expect(response.headers['content-disposition']).to.contain('attachment');
    });
  });
});
