<?php

namespace Plugins\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Plugins\Analytics\Services\AnalyticsContext;

/**
 * Read-only JSON API that exposes every analytics dataset so external
 * platforms can build their own dashboards.
 */
class AnalyticsApiController extends Controller
{
    /**
     * Describe the available endpoints.
     */
    public function index(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return response()->json([
            'data' => [
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/analytics/summary', 'description' => 'KPIs, status distributions, task split and rankings'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/series', 'description' => 'Creation series for projects, work orders, documents and FAMs'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/fams', 'description' => 'FAMs totals, status/stage/supplier breakdowns, recent rows and changes'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/fams/records', 'description' => 'Latest FAMs record per OC + OS (paginated, searchable)'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/fams/records/{oc}/{os}/history', 'description' => 'Every stored version of an OC + OS pair'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/fams/changes', 'description' => 'Latest FAMs version changes'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/afl', 'description' => 'AFL totals, status/billing/project breakdowns, recent rows and changes'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/afl/records', 'description' => 'Latest AFL record per Cód_AFL (paginated, searchable)'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/afl/records/{code}/history', 'description' => 'Every stored version of an AFL code'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/afl/changes', 'description' => 'Latest AFL version changes'],
                    ['method' => 'GET', 'path' => '/api/v1/analytics/snapshots', 'description' => 'Stored daily snapshots'],
                ],
                'query' => [
                    'period' => '7, 30, 90 or 365 days (default 30)',
                    'from' => 'Custom start date (Y-m-d)',
                    'to' => 'Custom end date (Y-m-d)',
                    'scope' => 'user (default) or global (administrators only)',
                ],
                'authentication' => 'Bearer token (Laravel Sanctum)',
            ],
            'meta' => $context->meta(),
        ]);
    }

    /**
     * The headline indicators and distributions.
     */
    public function summary(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);
        $metrics = $context->metrics;

        return $this->respond([
            'summary' => $metrics->summary(),
            'work_order_statuses' => $metrics->workOrderStatuses(),
            'project_statuses' => $metrics->projectStatuses(),
            'agenda_statuses' => $metrics->agendaStatuses(),
            'form_statuses' => $metrics->formStatuses(),
            'task_split' => $metrics->taskSplit(),
            'tasks_by_list' => $metrics->tasksByList(),
            'top_projects' => $metrics->topProjects(),
        ], $context);
    }

    /**
     * The creation series of the period.
     */
    public function series(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return $this->respond($context->metrics->series(), $context);
    }

    /**
     * The FAMs dataset summary.
     */
    public function fams(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);
        $metrics = $context->metrics;

        return $this->respond([
            'summary' => $metrics->recordsSummary(),
            'by_status' => $metrics->recordsByStatus(),
            'by_stage' => $metrics->recordsByStage(),
            'by_supplier' => $metrics->recordsBySupplier(),
            'recent' => $metrics->recentRecords(10),
            'changes' => $metrics->recentRecordChanges(10),
        ], $context);
    }

    /**
     * The latest FAMs record of each OC + OS pair.
     */
    public function famsRecords(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        $paginator = $context->metrics->recordsList(
            (int) $request->query('per_page', 25),
            $request->query('search'),
        );

        return $this->paginated($context, $paginator);
    }

    /**
     * The stored versions of a FAMs record.
     */
    public function famsHistory(Request $request, string $oc, string $os): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return $this->respond([
            'oc' => $oc,
            'os' => $os,
            'versions' => $context->metrics->recordHistory($oc, $os),
        ], $context);
    }

    /**
     * The latest FAMs changes.
     */
    public function famsChanges(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return $this->respond([
            'changes' => $context->metrics->recentRecordChanges((int) $request->query('limit', 25)),
        ], $context);
    }

    /**
     * The AFL dataset summary.
     */
    public function afl(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);
        $metrics = $context->metrics;

        return $this->respond([
            'summary' => $metrics->aflSummary(),
            'by_status' => $metrics->aflByStatus(),
            'by_billing_status' => $metrics->aflByBillingStatus(),
            'by_project' => $metrics->aflByProject(),
            'recent' => $metrics->recentAflRecords(10),
            'changes' => $metrics->recentAflChanges(10),
        ], $context);
    }

    /**
     * The latest AFL record of each Cód_AFL.
     */
    public function aflRecords(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        $paginator = $context->metrics->aflRecordsList(
            (int) $request->query('per_page', 25),
            $request->query('search'),
        );

        return $this->paginated($context, $paginator);
    }

    /**
     * The stored versions of an AFL record.
     */
    public function aflHistory(Request $request, string $code): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return $this->respond([
            'code' => $code,
            'versions' => $context->metrics->aflHistory($code),
        ], $context);
    }

    /**
     * The latest AFL changes.
     */
    public function aflChanges(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return $this->respond([
            'changes' => $context->metrics->recentAflChanges((int) $request->query('limit', 25)),
        ], $context);
    }

    /**
     * The stored daily snapshots.
     */
    public function snapshots(Request $request): JsonResponse
    {
        $context = AnalyticsContext::fromRequest($request);

        return $this->respond([
            'snapshots' => $context->metrics->snapshots(),
        ], $context);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function respond(array $data, AnalyticsContext $context): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => $context->meta(),
        ]);
    }

    /**
     * @param  LengthAwarePaginator<int, array<string, mixed>>  $paginator
     */
    private function paginated(AnalyticsContext $context, LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                ...$context->meta(),
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            'links' => [
                'self' => $paginator->url($paginator->currentPage()),
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
