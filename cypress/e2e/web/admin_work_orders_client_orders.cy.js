import { adminUser } from '../../support/users';

/**
 * Open the details page of the seeded work order.
 */
const openSeededWorkOrder = () => {
    cy.visit('/admin/work-orders');
    cy.contains('a', 'OS-1007').click();
    cy.location('pathname').should('match', /\/admin\/work-orders\/\d+$/);
};

describe('Admin - Work order client OS', () => {
    beforeEach(() => {
        cy.resetAuthThrottle();
        cy.uiLogin(adminUser.email, adminUser.password);
        cy.location('pathname').should('eq', '/admin');
    });

    it('shows the client OS data on the work order details page', () => {
        openSeededWorkOrder();

        cy.contains('h2', 'OS do cliente')
            .closest('section')
            .within(() => {
                cy.contains('OS Cliente').should('be.visible');
                cy.contains('CLI-1007').should('be.visible');
                cy.contains('OC (ordem complexa)').should('be.visible');
                cy.contains('OC-1007').should('be.visible');
                cy.contains('OS-CLI-1007').should('be.visible');
                cy.contains('OS do cliente vinculada ao item da OS.').should('be.visible');
                cy.contains('1 item vinculado').should('be.visible');
            });

        cy.contains('OS cliente: OS-CLI-1007').should('be.visible');
    });

    it('edits the client OS fields and the client OS list', () => {
        openSeededWorkOrder();

        cy.contains('button', 'Editar').first().click();
        cy.get('#work-order-edit-modal').should('be.visible');

        cy.get('#client_os').should('have.value', 'CLI-1007');
        cy.get('#oc').should('have.value', 'OC-1007');
        cy.get('#client-order-number-client-order-0').should('have.value', 'OS-CLI-1007');
        cy.get('#client-order-description-client-order-0').should('have.value', 'OS do cliente vinculada ao item da OS.');

        cy.get('#work-order-edit-modal select[name="items[0][client_order_key]"]')
            .find('option:selected')
            .should('have.text', 'OS-CLI-1007');
    });
});
