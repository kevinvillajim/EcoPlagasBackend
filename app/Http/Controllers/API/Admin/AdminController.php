<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ErrorHandler;
use App\Services\DashboardService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    use ErrorHandler;

    protected DashboardService $dashboardService;
    protected ReportService $reportService;

    public function __construct(
        DashboardService $dashboardService,
        ReportService $reportService
    ) {
        $this->dashboardService = $dashboardService;
        $this->reportService = $reportService;
    }

    /**
     * Get admin dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            // Test basic stats first
            $stats = $this->dashboardService->getAdminStats();
            
            // Test recent activities
            $recentActivities = $this->dashboardService->getRecentActivities(4);
            
            // Test today scheduled
            $todayScheduled = $this->dashboardService->getTodayScheduledServices();

            return $this->successResponse([
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'today_scheduled' => $todayScheduled
            ], 'Dashboard cargado exitosamente');

        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar dashboard: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get business reports
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $reportType = $request->get('type', 'summary');
            $startDate = $request->get('start_date', now()->subMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $reports = match($reportType) {
                'summary' => $this->reportService->getSummaryReport($startDate, $endDate),
                'clients' => $this->reportService->getClientsReport($startDate, $endDate),
                'services' => $this->reportService->getServicesReport($startDate, $endDate),
                'financial' => $this->reportService->getFinancialReport($startDate, $endDate),
                'performance' => $this->reportService->getPerformanceReport($startDate, $endDate),
                default => throw new \InvalidArgumentException('Tipo de reporte no válido')
            };

            return $this->successResponse([
                'report_type' => $reportType,
                'period' => ['start' => $startDate, 'end' => $endDate],
                'data' => $reports
            ], 'Reporte generado exitosamente');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al generar reportes');
        }
    }
}