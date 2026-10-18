import { adminUser } from '../../support/users';

describe('admin/backups/status (JSON endpoint)', () => {
  beforeEach(() => {
    cy.uiLogin(adminUser.email, adminUser.password);
    cy.location('pathname').should('eq', '/admin');
  });

  it('returns the backup status payload', () => {
    cy.request('GET', '/admin/backups/status').then((response) => {
      expect(response.status).to.eq(200);
      expect(response.headers['content-type']).to.contain('application/json');
    });
  });
});
