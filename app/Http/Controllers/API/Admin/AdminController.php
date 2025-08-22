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
            $stats = $this->dashboardService->getAdminStats();
            $monthlyData = $this->dashboardService->getMonthlyGrowthData();
            $serviceDistribution = $this->dashboardService->getServiceDistribution();

            return $this->successResponse([
                'stats' => $stats,
                'monthly_data' => $monthlyData,
                'service_distribution' => $serviceDistribution
            ], 'Dashboard cargado exitosamente');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cargar dashboard administrativo');
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