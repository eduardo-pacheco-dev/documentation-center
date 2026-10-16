import { regularUser } from '../../support/users';
import { fieldError } from '../../support/selectors';

describe('Authentication - Registration', () => {
    it('shows the registration form to guests', () => {
        cy.visit('/register');

        cy.contains('h1', 'Criar conta').should('be.visible');
        cy.get('#name').should('be.visible');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.get('#password_confirmation').should('be.visible');
        cy.contains('a', 'Entrar').should('have.attr', 'href').and('include', '/login');
    });

    it('registers a new account and signs the user in', () => {
        const email = `cypress.signup.${Date.now()}@example.com`;

        cy.visit('/register');
        cy.get('#name').type('Nova Pessoa');
        cy.get('#email').type(email);
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('form[action$="/register"] button[type="submit"]').click();

        cy.location('pathname').should('eq', '/admin');
        cy.get('header').contains('Nova Pessoa').should('be.visible');
    });

    it('rejects an e-mail that is already registered and keeps the input', () => {
        cy.visit('/register');
        cy.get('#name').type('Nova Pessoa');
        cy.get('#email').type(regularUser.email);
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('form[action$="/register"] button[type="submit"]').click();

        cy.location('pathname').should('eq', '/register');
        fieldError('email').should('be.visible');
        cy.get('#email').should('have.value', regularUser.email);
    });

    it('rejects a mismatched password confirmation', () => {
        const email = `cypress.signup.${Date.now()}@example.com`;

        cy.visit('/register');
        cy.get('#name').type('Nova Pessoa');
        cy.get('#email').type(email);
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('different-password');
        cy.get('form[action$="/register"] button[type="submit"]').click();

        cy.location('pathname').should('eq', '/register');
        fieldError('password').should('be.visible');
    });
});
