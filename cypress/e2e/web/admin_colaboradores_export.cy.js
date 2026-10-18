import { adminUser } from '../../support/users';

describe('admin/colaboradores/export (file download)', () => {
  beforeEach(() => {
    cy.uiLogin(adminUser.email, adminUser.password);
    cy.location('pathname').should('eq', '/admin');
  });

  it('streams the colaboradores export file', () => {
    cy.request('GET', '/admin/colaboradores/export').then((response) => {
      expect(response.status).to.eq(200);
      expect(response.headers['content-disposition']).to.contain('attachment');
    });
  });
});
