<?php

/*
|--------------------------------------------------------------------------
| Intelephense helper (never executed)
|--------------------------------------------------------------------------
|
| Intelephense binds Pest's higher order test functions to PHPUnit's
| TestCase, which does not expose Laravel's testing helpers such as
| actingAs(), get() and post(). These stubs tell the language server that
| the closures receive the application TestCase instead.
|
| This file is only read by the IDE; it is not autoloaded by the app.
|
*/

use Pest\PendingCalls\AfterEachCall;
use Pest\PendingCalls\BeforeEachCall;
use Pest\PendingCalls\TestCall;
use Tests\TestCase;

/**
 * @param-closure-this TestCase $closure
 *
 * @disregard P1075
 */
function beforeAll(Closure $closure): void {}

/**
 * @param-closure-this TestCase $closure
 *
 * @disregard P1075
 */
function beforeEach(?Closure $closure = null): BeforeEachCall {}

/**
 * @param-closure-this TestCase $closure
 *
 * @disregard P1075
 */
function afterEach(?Closure $closure = null): AfterEachCall {}

/**
 * @param-closure-this TestCase $closure
 *
 * @disregard P1075
 */
function afterAll(Closure $closure): void {}

/**
 * @param-closure-this TestCase $closure
 *
 * @disregard P1075
 */
function it(string $description, ?Closure $closure = null): TestCall {}

/**
 * @param-closure-this TestCase $closure
 *
 * @disregard P1075
 */
function test(?string $description = null, ?Closure $closure = null): TestCall {}
