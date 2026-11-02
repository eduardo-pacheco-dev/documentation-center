<?php

namespace Plugins\Analytics\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI documentation for the analytics JSON API.
 *
 * Kept in a single class so the swagger generator can discover every endpoint
 * without annotating the controller methods one by one. The global Info,
 * Server and bearerAuth scheme live in App\Http\Controllers\Api\AuthController.
 */
#[OA\Tag(name: 'Analytics', description: 'Indicators, series, FAMs, AFL and snapshots')]
#[OA\Get(
    path: '/api/v1/analytics',
    tags: ['Analytics'],
    summary: 'Describe the available analytics endpoints',
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/summary',
    tags: ['Analytics'],
    summary: 'Headline indicators and distributions',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/series',
    tags: ['Analytics'],
    summary: 'Creation series of the period',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/fams',
    tags: ['Analytics'],
    summary: 'FAMs totals, breakdowns, recent rows and changes',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/fams/records',
    tags: ['Analytics'],
    summary: 'Latest FAMs record per OC + OS',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
        new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/fams/records/{oc}/{os}/history',
    tags: ['Analytics'],
    summary: 'Every stored version of an OC + OS pair',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'oc', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'os', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/fams/changes',
    tags: ['Analytics'],
    summary: 'Latest FAMs version changes',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/afl',
    tags: ['Analytics'],
    summary: 'AFL totals, breakdowns, recent rows and changes',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/afl/records',
    tags: ['Analytics'],
    summary: 'Latest AFL record per Cód_AFL',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
        new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/afl/records/{code}/history',
    tags: ['Analytics'],
    summary: 'Every stored version of an AFL code',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'code', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/afl/changes',
    tags: ['Analytics'],
    summary: 'Latest AFL version changes',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
#[OA\Get(
    path: '/api/v1/analytics/snapshots',
    tags: ['Analytics'],
    summary: 'Stored daily snapshots',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', description: 'Period in days (default 30)', schema: new OA\Schema(type: 'integer', enum: [7, 30, 90, 365])),
        new OA\Parameter(name: 'from', in: 'query', description: 'Custom start date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', description: 'Custom end date', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'scope', in: 'query', description: 'user or global (global requires an administrator)', schema: new OA\Schema(type: 'string', enum: ['user', 'global'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Successful response', content: new OA\JsonContent(type: 'object')),
        new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'The global scope requires an administrator'),
        new OA\Response(response: 422, description: 'Invalid query parameters'),
    ],
)]
final class AnalyticsApiDocumentation {}
