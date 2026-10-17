// Selectors shared across the end-to-end specs.
//
// The auth views render validation errors as a paragraph right below the field
// container, so scoping by the field label keeps the specs decoupled from CSS
// classes and validation messages.

/** Validation error paragraph rendered below a field (e.g. `email`). */
export const fieldError = (field) => cy.get(`label[for="${field}"]`).parent().find('p');

/** The login form. */
export const loginForm = () => cy.get('form[action$="/login"]');

/** The registration form. */
export const registerForm = () => cy.get('form[action$="/register"]');

/** The logout form inside the admin header dropdown. */
export const logoutForm = () => cy.get('form[action$="/logout"]');

// Dashboard.
//
// The layout navigation reuses the labels of the dashboard counters
// ("Clientes", "Arquivos", ...), so the cards and the tiles are located through
// the `data-stat` and `data-module` attributes rendered by the view instead of
// through their text.

/** A summary card of the dashboard header, by slug (e.g. `clientes`). */
export const statCard = (stat) => cy.get(`[data-stat="${stat}"]`);

/** The value rendered by a summary card. */
export const statValue = (stat) => statCard(stat).find('p').first();

/** A work order status row of the dashboard breakdown (e.g. `open`). */
export const statusRow = (status) => cy.get(`a[href*="status=${status}"]`);

/** A quick access tile of the dashboard module grid (e.g. `erbs`). */
export const moduleTile = (module) => cy.get(`[data-module="${module}"]`);

/** The counter rendered by a module tile. */
export const moduleCount = (module) => moduleTile(module).find('span').last();

/** A dashboard panel located by its title (e.g. `Arquivos recentes`). */
export const panel = (title) => cy.contains('h2', title).closest('section');
