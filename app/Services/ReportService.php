<?php

namespace App\Services;

use App\Models\User;
use App\Models\Service;
use App\Models\Certificate;
use App\Models\Review;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate summary report for a date range
     */
    public function getSummaryReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'days' => $start->diffInDays($end) + 1,
            ],
            'metrics' => [
                'new_clients' => $this->getNewClientsCount($start, $end),
                'services_completed' => $this->getCompletedServicesCount($start, $end),
                'certificates_issued' => $this->getCertificatesIssuedCount($start, $end),
                'reviews_received' => $this->getReviewsReceivedCount($start, $end),
                'average_rating' => $this->getAverageRating($start, $end),
                'total_revenue' => $this->getTotalRevenue($start, $end),
            ],
        ];
    }

    /**
     * Generate detailed clients report
     */
    public function getClientsReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $clients = User::where('role', 'client')
            ->withCount(['services', 'certificates', 'reviews'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'is_active' => $client->is_active,
                    'services_count' => $client->services_count,
                    'certificates_count' => $client->certificates_count,
                    'reviews_count' => $client->reviews_count,
                    'registered_at' => $client->created_at,
                    'lifetime_value' => $this->getClientLifetimeValue($client->id),
                ];
            });

        return [
            'summary' => [
                'total_clients' => $clients->count(),
                'active_clients' => $clients->where('is_active', true)->count(),
                'average_services_per_client' => $clients->avg('services_count'),
                'total_lifetime_value' => $clients->sum('lifetime_value'),
            ],
            'clients' => $clients->values(),
        ];
    }

    /**
     * Generate services report
     */
    public function getServicesReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $services = Service::with(['user:id,name,email', 'technician:id,name'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'type' => $service->type,
                    'client' => [
                        'name' => $service->user->name,
                        'email' => $service->user->email,
                    ],
                    'technician' => $service->technician ? [
                        'name' => $service->technician->name,
                    ] : null,
                    'status' => $service->status,
                    'cost' => $service->cost,
                    'scheduled_date' => $service->scheduled_date,
                    'completed_date' => $service->completed_date,
                    'created_at' => $service->created_at,
                    'address' => $service->address,
                ];
            });

        $statusCounts = $services->groupBy('status')->map->count();
        $typeCounts = $services->groupBy('type')->map->count();

        return [
            'summary' => [
                'total_services' => $services->count(),
                'total_revenue' => $services->where('status', 'completed')->sum('cost'),
                'average_service_value' => $services->where('status', 'completed')->avg('cost'),
                'status_breakdown' => $statusCounts,
                'type_breakdown' => $typeCounts,
            ],
            'services' => $services->values(),
        ];
    }

    /**
     * Generate financial report
     */
    public function getFinancialReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $completedServices = Service::where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->get();

        $revenueByType = $completedServices->groupBy('type')
            ->map(function($services, $type) {
                return [
                    'type' => $type,
                    'count' => $services->count(),
                    'revenue' => $services->sum('cost'),
                    'average_value' => $services->avg('cost'),
                ];
            })
            ->values();

        $revenueByMonth = $completedServices->groupBy(function($service) {
                return Carbon::parse($service->completed_date)->format('Y-m');
            })
            ->map(function($services, $month) {
                return [
                    'month' => $month,
                    'count' => $services->count(),
                    'revenue' => $services->sum('cost'),
                ];
            })
            ->values();

        return [
            'summary' => [
                'total_revenue' => $completedServices->sum('cost'),
                'average_service_value' => $completedServices->avg('cost') ?? 0,
                'services_completed' => $completedServices->count(),
                'highest_value_service' => $completedServices->max('cost') ?? 0,
                'lowest_value_service' => $completedServices->min('cost') ?? 0,
            ],
            'revenue_by_service_type' => $revenueByType,
            'revenue_by_month' => $revenueByMonth,
            'top_clients' => $this->getTopClientsByRevenue($start, $end),
        ];
    }

    /**
     * Generate performance metrics report
     */
    public function getPerformanceReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'customer_satisfaction' => [
                'average_rating' => $this->getAverageRating($start, $end),
                'total_reviews' => $this->getReviewsReceivedCount($start, $end),
                'rating_distribution' => $this->getRatingDistribution($start, $end),
            ],
            'operational_efficiency' => [
                'average_completion_time' => $this->getAverageCompletionTime($start, $end),
                'service_completion_rate' => $this->getServiceCompletionRate($start, $end),
                'certificate_issuance_rate' => $this->getCertificateIssuanceRate($start, $end),
            ],
            'business_growth' => [
                'client_retention_rate' => $this->getClientRetentionRate($start, $end),
                'repeat_customer_percentage' => $this->getRepeatCustomerPercentage($start, $end),
                'revenue_growth' => $this->getRevenueGrowth($start, $end),
            ],
        ];
    }

    /**
     * Private helper methods
     */
    private function getNewClientsCount(Carbon $start, Carbon $end): int
    {
        return User::where('role', 'client')
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function getCompletedServicesCount(Carbon $start, Carbon $end): int
    {
        return Service::where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->count();
    }

    private function getCertificatesIssuedCount(Carbon $start, Carbon $end): int
    {
        return Certificate::whereBetween('issue_date', [$start, $end])->count();
    }

    private function getReviewsReceivedCount(Carbon $start, Carbon $end): int
    {
        return Review::whereBetween('created_at', [$start, $end])->count();
    }

    private function getAverageRating(Carbon $start, Carbon $end): float
    {
        return Review::whereBetween('created_at', [$start, $end])
            ->avg('rating') ?? 0.0;
    }

    private function getTotalRevenue(Carbon $start, Carbon $end): float
    {
        return Service::where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->sum('cost') ?? 0.0;
    }

    private function getClientLifetimeValue(int $clientId): float
    {
        return Service::where('user_id', $clientId)
            ->where('status', 'completed')
            ->sum('cost') ?? 0.0;
    }

    private function getTopClientsByRevenue(Carbon $start, Carbon $end, int $limit = 10): array
    {
        return Service::with('user:id,name,email')
            ->where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->selectRaw('user_id, SUM(cost) as total_revenue, COUNT(*) as services_count')
            ->groupBy('user_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($service) {
                return [
                    'client' => [
                        'name' => $service->user->name,
                        'email' => $service->user->email,
                    ],
                    'total_revenue' => $service->total_revenue,
                    'services_count' => $service->services_count,
                    'average_service_value' => $service->total_revenue / $service->services_count,
                ];
            })
            ->toArray();
    }

    private function getRatingDistribution(Carbon $start, Carbon $end): array
    {
        $distribution = Review::whereBetween('created_at', [$start, $end])
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        $total = $distribution->sum('count');

        return $distribution->map(function($item) use ($total) {
            return [
                'rating' => $item->rating,
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function getAverageCompletionTime(Carbon $start, Carbon $end): ?float
    {
        $services = Service::where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->whereNotNull('scheduled_date')
            ->whereNotNull('completed_date')
            ->get();

        if ($services->isEmpty()) {
            return null;
        }

        $totalDays = $services->sum(function($service) {
            return Carbon::parse($service->scheduled_date)
                ->diffInDays(Carbon::parse($service->completed_date));
        });

        return $totalDays / $services->count();
    }

    private function getServiceCompletionRate(Carbon $start, Carbon $end): float
    {
        $totalServices = Service::whereBetween('created_at', [$start, $end])->count();
        $completedServices = Service::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return $totalServices > 0 ? ($completedServices / $totalServices) * 100 : 0.0;
    }

    private function getCertificateIssuanceRate(Carbon $start, Carbon $end): float
    {
        $completedServices = Service::where('status', 'completed')
            ->whereBetween('completed_date', [$start, $end])
            ->count();

        $issuedCertificates = Certificate::whereBetween('issue_date', [$start, $end])->count();

        return $completedServices > 0 ? ($issuedCertificates / $completedServices) * 100 : 0.0;
    }

    private function getClientRetentionRate(Carbon $start, Carbon $end): float
    {
        // Simplified calculation - clients who had services both before and during the period
        $clientsWithPreviousServices = User::whereHas('services', function($query) use ($start) {
                $query->where('created_at', '<', $start);
            })
            ->where('role', 'client')
            ->count();

        $returningClients = User::whereHas('services', function($query) use ($start) {
                $query->where('created_at', '<', $start);
            })
            ->whereHas('services', function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->where('role', 'client')
            ->count();

        return $clientsWithPreviousServices > 0 
            ? ($returningClients / $clientsWithPreviousServices) * 100 
            : 0.0;
    }

    private function getRepeatCustomerPercentage(Carbon $start, Carbon $end): float
    {
        $totalClients = User::whereHas('services', function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->where('role', 'client')
            ->count();

        $repeatCustomers = User::whereHas('services', function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            }, '>', 1)
            ->where('role', 'client')
            ->count();

        return $totalClients > 0 ? ($repeatCustomers / $totalClients) * 100 : 0.0;
    }

    private function getRevenueGrowth(Carbon $start, Carbon $end): array
    {
        $currentPeriodRevenue = $this->getTotalRevenue($start, $end);
        
        $periodLength = $start->diffInDays($end) + 1;
        $previousStart = $start->copy()->subDays($periodLength);
        $previousEnd = $start->copy()->subDay();
        
        $previousPeriodRevenue = $this->getTotalRevenue($previousStart, $previousEnd);

        $growth = $previousPeriodRevenue > 0 
            ? (($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100
            : 0.0;

        return [
            'current_period' => $currentPeriodRevenue,
            'previous_period' => $previousPeriodRevenue,
            'growth_percentage' => $growth,
            'growth_amount' => $currentPeriodRevenue - $previousPeriodRevenue,
        ];
    }
}