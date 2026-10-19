import { adminUser, regularUser } from '../../support/users';

const agendaUrl = '/admin/agenda';

/**
 * Open the agenda in the given view.
 */
const openAgenda = (view = 'month') => {
    cy.visit(view === 'month' ? agendaUrl : `${agendaUrl}?view=${view}`);
    cy.contains('h1', 'Agenda').should('be.visible');
};

/**
 * Switch the agenda view through the toolbar.
 */
const switchView = (view) => {
    cy.get(`a[href*="view=${view}"]`).first().click();
    cy.location('search').should('contain', `view=${view}`);
};

/**
 * The date the server renders for the reference day, which can differ from
 * the browser clock around midnight.
 */
const serverDate = () => cy.get('[data-agenda-create]').invoke('attr', 'data-date');

/**
 * Scroll the calendar sidebar until every page has been appended.
 */
const loadAllCalendars = () => {
    cy.get('[data-agenda-calendar-list]').scrollTo('bottom');
    cy.get('[data-agenda-calendar-end]').should('be.visible');
};

/**
 * Save the event modal currently open.
 */
const saveEvent = () => {
    cy.get('#agenda-event-modal').contains('button', 'Salvar').click();
};

describe('Admin - Agenda', () => {
    beforeEach(() => {
        cy.resetAuthThrottle();
        cy.uiLogin(adminUser.email, adminUser.password);
        cy.location('pathname').should('eq', '/admin');
    });

    it('renders the agenda views', () => {
        openAgenda();

        cy.contains('p', 'Meus calendários').should('be.visible');
        cy.contains('[data-agenda-calendar-item]', 'Minha agenda').should('be.visible');

        switchView('week');
        cy.get('[data-agenda-grid]').should('be.visible');

        switchView('day');
        cy.get('[data-agenda-grid]').should('be.visible');

        switchView('list');
        cy.contains('button', 'Reunião Cypress').should('be.visible');
    });

    it('loads more calendars while scrolling the sidebar list', () => {
        openAgenda();

        cy.get('[data-agenda-calendar-item]').should('have.length', 10);
        cy.contains('[data-agenda-calendar-item]', 'Calendário 12').should('not.exist');

        cy.get('[data-agenda-calendar-list]').scrollTo('bottom');

        cy.get('[data-agenda-calendar-item]').should('have.length.at.least', 11);
        cy.contains('[data-agenda-calendar-item]', 'Calendário 12').should('be.visible');
        cy.contains('[data-agenda-calendar-end]', 'Todos os calendários carregados.').should('be.visible');
    });

    it('creates, edits and deletes an event', () => {
        openAgenda('list');

        serverDate().then((date) => {
            const original = `Evento Cypress ${Date.now()}`;
            const edited = `${original} editado`;

            cy.get('[data-agenda-create]').click();
            cy.get('#agenda-event-modal').should('be.visible');
            cy.get('[data-agenda-field="title"]').type(original);
            cy.get('#agenda-event-start').clear().type(`${date}T13:00`);
            cy.get('#agenda-event-end').clear().type(`${date}T14:00`);
            saveEvent();
            cy.location('pathname').should('eq', '/admin/agenda');

            openAgenda('list');
            cy.contains('button', original).should('be.visible').click();
            cy.get('[data-agenda-field="title"]').clear().type(edited);
            saveEvent();
            cy.location('pathname').should('eq', '/admin/agenda');

            openAgenda('list');
            cy.contains('button', edited).should('be.visible').click();
            cy.get('#agenda-event-modal').contains('button', 'Excluir').click();
            cy.location('pathname').should('eq', '/admin/agenda');

            openAgenda('list');
            cy.contains('button', edited).should('not.exist');
        });
    });

    it('creates, edits, shares and deletes a calendar', () => {
        const name = `Calendário Cypress ${Date.now()}`;
        const edited = `${name} editado`;

        openAgenda();
        cy.get('[data-agenda-calendar-create]').click();
        cy.get('#agenda-calendar-modal').should('be.visible');
        cy.get('#agenda-calendar-name').type(name);
        cy.get('[data-agenda-calendar-form]').contains('button', 'Salvar').click();
        cy.contains('[data-toast-container] [data-toast]', 'Calendário criado.').should('be.visible');

        loadAllCalendars();
        cy.contains('[data-agenda-calendar-item]', name)
            .should('be.visible')
            .find('[data-agenda-calendar-edit]')
            .click();

        cy.get('#agenda-calendar-name').clear().type(edited);
        cy.get('[data-agenda-calendar-form]').contains('button', 'Salvar').click();
        cy.contains('[data-toast-container] [data-toast]', 'Calendário atualizado.').should('be.visible');

        loadAllCalendars();
        cy.contains('[data-agenda-calendar-item]', edited)
            .should('be.visible')
            .find('[data-agenda-calendar-edit]')
            .click();

        cy.get('[data-agenda-calendar-share-form] input[name="email"]').type(regularUser.email);
        cy.get('[data-agenda-calendar-share-form] select[name="role"]').select('writer');
        cy.get('[data-agenda-calendar-share-form]').contains('button', 'Compartilhar').click();
        cy.contains('[data-toast-container] [data-toast]', `Calendário compartilhado com ${regularUser.email}.`).should('be.visible');

        loadAllCalendars();
        cy.contains('[data-agenda-calendar-item]', edited)
            .find('[data-agenda-calendar-edit]')
            .click();
        cy.get('#agenda-calendar-modal').contains('button', 'Excluir').click();
        cy.get('[data-confirm-accept]').click();
        cy.contains('[data-toast-container] [data-toast]', 'Calendário excluído.').should('be.visible');
    });

    it('exports the agenda as an ics file', () => {
        cy.request('/admin/agenda/export').then((response) => {
            expect(response.status).to.eq(200);
            expect(response.headers['content-type']).to.contain('text/calendar');
            expect(response.headers['content-disposition']).to.contain('.ics');
            expect(response.body).to.contain('BEGIN:VCALENDAR');
            expect(response.body).to.contain('SUMMARY:Reunião Cypress');
        });
    });

    it('imports events from an ics file', () => {
        openAgenda();

        cy.get('form[action$="/admin/agenda/import"] input[name="file"]')
            .selectFile('cypress/fixtures/agenda.ics');
        cy.get('form[action$="/admin/agenda/import"]').contains('button', 'Importar arquivo').click();

        cy.contains('[data-toast-container] [data-toast]', '1 evento(s) importado(s).').should('be.visible');
    });
});

describe('Admin - Agenda of a shared calendar', () => {
    beforeEach(() => {
        cy.resetAuthThrottle();
        cy.uiLogin(regularUser.email, regularUser.password);
        cy.location('pathname').should('eq', '/admin');
    });

    it('shows the shared calendar and its events to the reader', () => {
        cy.visit(`${agendaUrl}?view=list`);

        cy.contains('[data-agenda-calendar-item]', 'Minha agenda').should('contain', 'Compartilhado');
        cy.contains('button', 'Reunião Cypress').should('be.visible');
        cy.contains('button', 'Compromisso do membro').should('be.visible');
    });
});
