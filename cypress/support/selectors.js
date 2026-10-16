// Selectors shared across the authentication specs.
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
