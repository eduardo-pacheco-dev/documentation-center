// ***********************************************************
// This file is loaded automatically before every spec file.
// https://docs.cypress.io/guides/core-concepts/writing-and-organizing-tests#Support-file
// ***********************************************************

import './commands';

// `POST /login` and `POST /register` are rate limited to 5 attempts per minute
// per e-mail + IP, and the limiter is shared between specs. Reset it before each
// spec so the authentication scenarios start from a known state.
before(() => {
    cy.resetAuthThrottle();
});
