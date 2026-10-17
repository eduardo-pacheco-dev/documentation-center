import {
    moduleCount,
    moduleTile,
    panel,
    statCard,
    statValue,
    statusRow,
} from '../../support/selectors';
import { adminUser, regularUser } from '../../support/users';

// Mirrors the records created by `Database\Seeders\CypressSeeder`. The dashboard
// counters are scoped to the signed-in user, so the expectations below describe
// the data set each account owns.
const administrator = {
    clients: 2,
    openWorkOrders: 3,
    activeProjects: 1,
    documents: 6,
    erbs: 4,
    radioLinks: 2,
    catalogItems: 5,
    forms: 1,
    colaboradores: 2,
    links: 3,
    workOrders: 7,
    completionRate: 43,
    statuses: [
        { key: 'open', label: 'Aberta', count: 2, percentage: 29 },
        { key: 'in_progress', label: 'Em andamento', count: 1, percentage: 14 },
        { key: 'completed', label: 'Concluída', count: 3, percentage: 43 },
        { key: 'cancelled', label: 'Cancelada', count: 1, percentage: 14 },
    ],
    recentWorkOrders: ['OS-1007', 'OS-1006', 'OS-1005', 'OS-1004', 'OS-1003', 'OS-1002'],
    recentDocuments: ['arquivo-6.pdf', 'arquivo-5.pdf', 'arquivo-4.pdf', 'arquivo-3.pdf', 'arquivo-2.pdf'],
};

const member = {
    clients: 1,
    openWorkOrders: 1,
    documents: 1,
    erbs: 1,
    links: 1,
};

/** Collapse the whitespace Blade leaves between the rendered elements. */
const collapse = (text) => text.replace(/\s+/g, ' ').trim();

/**
 * The greeting the dashboard builds from the server hour and the first name.
 *
 * The hour comes from the application timezone (`UTC`), so the period cannot be
 * derived from the browser clock: only its shape is asserted here.
 */
const greetingFor = (user) => new RegExp(`^(Bom dia|Boa tarde|Boa noite), ${user.name.split(' ')[0]}!$`);

const longDate = /^((segunda|terça|quarta|quinta|sexta)-feira|sábado|domingo), \d{2} de \w+ de \d{4}$/;

describe('Admin - Dashboard', () => {
    describe('as an administrator', () => {
        beforeEach(() => {
            // Each test signs in again, so the login limiter has to be cleared
            // before every one of them.
            cy.resetAuthThrottle();

            cy.uiLogin(adminUser.email, adminUser.password);
            cy.location('pathname').should('eq', '/admin');
        });

        it('describes the day of the administrator', () => {
            cy.contains('h1', greetingFor(adminUser)).should('be.visible');

            cy.contains('h1', greetingFor(adminUser))
                .closest('section')
                .find('span')
                .first()
                .invoke('text')
                .then(collapse)
                .should('match', longDate);

            cy.contains('a', 'Nova OS').should('have.attr', 'href').and('include', '/admin/work-orders/create');
            cy.contains('a', 'Novo link').should('have.attr', 'href').and('include', '/admin/links');
        });

        it('summarises the records of the administrator', () => {
            [
                ['clientes', administrator.clients, '/admin/clients'],
                ['os-abertas', administrator.openWorkOrders, '/admin/work-orders'],
                ['projetos-ativos', administrator.activeProjects, '/admin/projects'],
                ['arquivos', administrator.documents, '/admin/files'],
            ].forEach(([stat, value, url]) => {
                statCard(stat).should('have.attr', 'href').and('include', url);
                statValue(stat).should('have.text', String(value));
            });

            [
                ['erbs', administrator.erbs, '/admin/erbs'],
                ['radio-links', administrator.radioLinks, '/admin/radio-links'],
                ['catalogo', administrator.catalogItems, '/admin/catalog'],
                ['formularios', administrator.forms, '/admin/forms'],
                ['colaboradores', administrator.colaboradores, '/admin/colaboradores'],
                ['links', administrator.links, '/admin/links'],
            ].forEach(([module, count, url]) => {
                moduleTile(module).should('have.attr', 'href').and('include', url);
                moduleCount(module).should('have.text', String(count));
            });

            // Templates and users are counted globally, so only their shape is
            // asserted here: the value depends on the whole seed set.
            moduleTile('templates').should('have.attr', 'href').and('include', '/admin/form-templates');
            moduleCount('templates').invoke('text').should('match', /^\d+$/);

            moduleTile('usuarios').should('have.attr', 'href').and('include', '/admin/users');
            moduleCount('usuarios').invoke('text').should('match', /^\d+$/);
        });

        it('breaks the work orders down by status', () => {
            cy.contains('p', `${administrator.workOrders} no total · distribuição por status`).should('be.visible');

            administrator.statuses.forEach((status) => {
                statusRow(status.key)
                    .should('have.attr', 'href')
                    .and('include', `/admin/work-orders?status=${status.key}`);

                statusRow(status.key)
                    .invoke('text')
                    .then(collapse)
                    .should('eq', `${status.label} ${status.count} ${status.percentage}%`);
            });

            cy.contains('p', `${administrator.completionRate}% das OS`).should('be.visible');

            statusRow('completed').click();

            cy.location('pathname').should('eq', '/admin/work-orders');
            cy.location('search').should('eq', '?status=completed');
        });

        it('lists the most recent work orders and files', () => {
            panel('Ordens de serviço recentes')
                .find('a[href*="/admin/work-orders/"] p:first-child')
                .should(($numbers) => {
                    expect($numbers.map((index, element) => element.textContent.trim()).get())
                        .to.deep.equal(administrator.recentWorkOrders);
                });

            // The seventh work order is older than the panel window, and the one
            // whose client was removed falls back to a placeholder.
            cy.contains('a', 'OS-1001').should('not.exist');
            cy.contains('a', 'OS-1006').should('contain', 'Sem cliente').and('contain', 'Concluída');
            cy.contains('a', 'OS-1007').should('contain', 'R$ 100,00');

            panel('Arquivos recentes')
                .find('p[title]')
                .should(($names) => {
                    expect($names.map((index, element) => element.textContent.trim()).get())
                        .to.deep.equal(administrator.recentDocuments);
                });

            panel('Arquivos recentes').find('time').should('have.length', administrator.recentDocuments.length);
        });
    });

    describe('as a member', () => {
        beforeEach(() => {
            cy.resetAuthThrottle();

            cy.uiLogin(regularUser.email, regularUser.password);
            cy.location('pathname').should('eq', '/admin');
        });

        it('only counts the records the member owns', () => {
            statValue('clientes').should('have.text', String(member.clients));
            statValue('os-abertas').should('have.text', String(member.openWorkOrders));
            statValue('projetos-ativos').should('have.text', '0');
            statValue('arquivos').should('have.text', String(member.documents));

            moduleCount('erbs').should('have.text', String(member.erbs));
            moduleCount('links').should('have.text', String(member.links));
            moduleCount('radio-links').should('have.text', '0');
            moduleCount('catalogo').should('have.text', '0');
            moduleCount('formularios').should('have.text', '0');
            moduleCount('colaboradores').should('have.text', '0');

            // Records owned by other accounts never reach the member counters.
            cy.contains('p', '1 no total · distribuição por status').should('be.visible');
            cy.contains('a', 'OS-2001').should('be.visible');
            cy.contains('a', 'OS-1007').should('not.exist');
        });

        it('hides the administrator only shortcuts', () => {
            moduleTile('usuarios').should('not.exist');

            cy.contains('p', 'arquivo-do-membro.pdf').should('be.visible');

            statusRow('open').invoke('text').then(collapse).should('eq', 'Aberta 1 100%');
            statusRow('completed').invoke('text').then(collapse).should('eq', 'Concluída 0 0%');
        });
    });
});
