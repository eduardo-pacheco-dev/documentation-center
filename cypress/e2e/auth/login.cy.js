import { adminUser, inactiveUser, regularUser } from '../../support/users';
import { fieldError } from '../../support/selectors';

describe('Authentication - Login', () => {
    it('shows the login form to guests', () => {
        cy.visit('/login');

        cy.contains('h1', 'Entrar no painel').should('be.visible');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.contains('button', 'Entrar').should('be.visible');
        cy.contains('a', 'Cadastre-se').should('have.attr', 'href').and('include', '/register');
    });

    it('signs in an active administrator', () => {
        cy.uiLogin(adminUser.email, adminUser.password);

        cy.location('pathname').should('eq', '/admin');
        cy.get('header').contains(adminUser.name).should('be.visible');
    });

    it('signs in an active member and does not remember the session by default', () => {
        cy.uiLogin(regularUser.email, regularUser.password);

        cy.location('pathname').should('eq', '/admin');
        cy.get('header').contains(regularUser.name).should('be.visible');

        cy.getCookies().should((cookies) => {
            const rememberCookie = cookies.find((cookie) => cookie.name.startsWith('remember_web_'));
            expect(rememberCookie, 'remember cookie').to.be.undefined;
        });
    });

    it('redirects to the originally requested page after signing in', () => {
        cy.visit('/admin/users');
        cy.location('pathname').should('eq', '/login');

        cy.get('#email').type(adminUser.email);
        cy.get('#password').type(adminUser.password);
        cy.get('form[action$="/login"] button[type="submit"]').click();

        cy.location('pathname').should('eq', '/admin/users');
        cy.contains('h1', 'Usuários').should('be.visible');
    });

    it('rejects an unknown e-mail', () => {
        cy.uiLogin('cypress.unknown@example.com', 'password');

        cy.location('pathname').should('eq', '/login');
        fieldError('email').should('be.visible').and('contain', 'As credenciais informadas');
    });

    it('rejects a wrong password', () => {
        cy.uiLogin(regularUser.email, 'wrong-password');

        cy.location('pathname').should('eq', '/login');
        fieldError('email')
            .should('be.visible')
            .and('contain', 'As credenciais informadas não correspondem a um usuário.');
    });

    it('rejects an inactive account', () => {
        cy.uiLogin(inactiveUser.email, inactiveUser.password);

        cy.location('pathname').should('eq', '/login');
        fieldError('email').should('be.visible').and('contain', 'Sua conta está desativada.');
    });

    it('remembers the session when requested', () => {
        cy.uiLogin(adminUser.email, adminUser.password, { remember: true });

        cy.location('pathname').should('eq', '/admin');

        cy.getCookies().should((cookies) => {
            const rememberCookie = cookies.find((cookie) => cookie.name.startsWith('remember_web_'));
            expect(rememberCookie, 'remember cookie').to.not.be.undefined;
            expect(rememberCookie.value).to.not.be.empty;
        });
    });

    it('blocks further attempts after five failures', () => {
        // A unique address keeps this test independent from attempts left over
        // by an earlier run of the suite.
        const email = `cypress.throttle.${Date.now()}@example.com`;

        cy.visit('/login');

        cy.get('input[name="_token"]').invoke('val').then((token) => {
            const statuses = [];

            const attempt = () => cy
                .request({
                    method: 'POST',
                    url: '/login',
                    form: true,
                    followRedirect: false,
                    failOnStatusCode: false,
                    body: { _token: token, email, password: 'wrong-password' },
                })
                .then((response) => {
                    statuses.push(response.status);
                });

            // Each request runs exactly once: the assertions only run once every
            // request is done, so Cypress never retries a request and never
            // consumes extra hits from the rate limiter.
            attempt()
                .then(attempt)
                .then(attempt)
                .then(attempt)
                .then(attempt)
                .then(attempt)
                .then(() => {
                    expect(statuses.slice(0, 5), 'first five attempts').to.deep.equal([302, 302, 302, 302, 302]);
                    expect(statuses[5], 'sixth attempt').to.equal(429);
                });
        });
    });
});
