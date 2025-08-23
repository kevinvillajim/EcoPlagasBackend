<?php

namespace App\Services;

use App\Models\User;
use App\Models\Service;
use App\Models\Certificate;
use App\Models\Review;
use App\Models\Gallery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get admin dashboard statistics
     */
    public function getAdminStats(): array
    {
        return [
            'users' => $this->getUserStats(),
            'services' => $this->getServiceStats(),
            'certificates' => $this->getCertificateStats(),
            'reviews' => $this->getReviewStats(),
            'gallery' => $this->getGalleryStats(),
            'revenue' => $this->getRevenueStats(),
        ];
    }

    /**
     * Get recent activities for dashboard
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $activities = [];

        // Recent completed services
        $recentServices = Service::with(['user', 'technician'])
            ->where('status', 'completed')
            ->where('completed_date', '>=', now()->subDays(7))
            ->orderBy('completed_date', 'desc')
            ->limit($limit / 2)
            ->get();

        foreach ($recentServices as $service) {
            $activities[] = [
                'id' => 'service_' . $service->id,
                'type' => 'service_completed',
                'client' => $service->user->name ?? 'Cliente desconocido',
                'service' => $service->type ?? 'Servicio general',
                'time' => $this->getTimeAgo($service->completed_date),
                'status' => 'completed',
                'created_at' => $service->completed_date,
            ];
        }

        // Recent new clients
        $newClients = User::where('role', 'client')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit($limit / 4)
            ->get();

        foreach ($newClients as $client) {
            $activities[] = [
                'id' => 'client_' . $client->id,
                'type' => 'new_client',
                'client' => $client->name,
                'service' => 'Registro nuevo cliente',
                'time' => $this->getTimeAgo($client->created_at),
                'status' => 'active',
                'created_at' => $client->created_at,
            ];
        }

        // Recent certificates issued
        $recentCertificates = Certificate::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit($limit / 4)
            ->get();

        foreach ($recentCertificates as $certificate) {
            $activities[] = [
                'id' => 'certificate_' . $certificate->id,
                'type' => 'certificate_issued',
                'client' => $certificate->user->name ?? 'Cliente desconocido',
                'service' => 'Certificado PDF generado',
                'time' => $this->getTimeAgo($certificate->created_at),
                'status' => 'completed',
                'created_at' => $certificate->created_at,
            ];
        }

        // Recent scheduled services
        $scheduledServices = Service::with(['user', 'technician'])
            ->where('status', 'scheduled')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit($limit / 4)
            ->get();

        foreach ($scheduledServices as $service) {
            $activities[] = [
                'id' => 'scheduled_' . $service->id,
                'type' => 'service_scheduled',
                'client' => $service->user->name ?? 'Cliente desconocido',
                'service' => $service->type ?? 'Servicio general',
                'time' => $this->getTimeAgo($service->created_at),
                'status' => 'scheduled',
                'created_at' => $service->created_at,
            ];
        }

        // Sort activities by creation date and limit
        usort($activities, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get today's scheduled services
     */
    public function getTodayScheduledServices(): array
    {
        return Service::with(['user', 'technician'])
            ->whereDate('scheduled_date', today())
            ->orderBy('scheduled_time')
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'client_name' => $service->user->name ?? 'Cliente desconocido',
                    'service_type' => $service->type ?? 'Servicio general',
                    'scheduled_time' => $service->scheduled_time ? 
                        Carbon::parse($service->scheduled_time)->format('H:i A') : 'Sin hora específica',
                    'technician' => $service->technician->name ?? 'Sin asignar',
                    'status' => $service->status,
                    'address' => $service->address,
                ];
            })
            ->toArray();
    }

    /**
     * Get client dashboard statistics
     */
    public function getClientStats(int $userId): array
    {
        return [
            'services' => $this->getClientServiceStats($userId),
            'certificates' => $this->getClientCertificateStats($userId),
            'notifications' => $this->getClientNotificationStats($userId),
        ];
    }

    /**
     * Get monthly growth data
     */
    public function getMonthlyGrowthData(int $months = 12): array
    {
        return Service::where('created_at', '>=', now()->subMonths($months))
            ->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as services_count,
                SUM(CASE WHEN status = "completed" THEN cost ELSE 0 END) as revenue
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'period' => sprintf('%04d-%02d', $item->year, $item->month),
                    'services' => $item->services_count,
                    'revenue' => $item->revenue ?? 0
                ];
            });
    }

    /**
     * Get service type distribution
     */
    public function getServiceDistribution(): array
    {
        $distribution = Service::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $total = $distribution->sum('count');

        return $distribution->map(function($item) use ($total) {
            return [
                'type' => $item->type,
                'count' => $item->count,
                'percentage' => $total > 0 
                    ? round(($item->count / $total) * 100, 1) 
                    : 0
            ];
        });
    }

    /**
     * Private helper methods
     */
    private function getUserStats(): array
    {
        return [
            'total' => User::where('role', 'client')->count(),
            'active' => User::where('role', 'client')->where('is_active', true)->count(),
            'new_this_month' => User::where('role', 'client')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }

    private function getServiceStats(): array
    {
        return [
            'total' => Service::count(),
            'completed' => Service::where('status', 'completed')->count(),
            'pending' => Service::where('status', 'scheduled')->count(),
            'in_progress' => Service::where('status', 'in_progress')->count(),
            'this_month' => Service::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    private function getCertificateStats(): array
    {
        return [
            'total' => Certificate::count(),
            'valid' => Certificate::where('status', 'valid')
                ->where('valid_until', '>=', now())
                ->count(),
            'expiring_soon' => Certificate::where('status', 'valid')
                ->whereBetween('valid_until', [now(), now()->addDays(30)])
                ->count(),
        ];
    }

    private function getReviewStats(): array
    {
        return [
            'total' => Review::count(),
            'approved' => Review::approved()->count(),
            'pending' => Review::pending()->count(),
            'average_rating' => Review::approved()->avg('rating') ?? 0,
        ];
    }

    private function getGalleryStats(): array
    {
        return [
            'total_items' => Gallery::count(),
            'active_items' => Gallery::where('is_active', true)->count(),
            'featured_items' => Gallery::where('featured', true)->count(),
        ];
    }

    private function getRevenueStats(): array
    {
        $revenueData = Service::where('status', 'completed')
            ->selectRaw('
                SUM(cost) as total_revenue,
                AVG(cost) as avg_service_cost,
                COUNT(*) as completed_count
            ')
            ->first();

        $thisMonthRevenue = Service::where('status', 'completed')
            ->where('completed_date', '>=', now()->startOfMonth())
            ->sum('cost');

        return [
            'total' => $revenueData->total_revenue ?? 0,
            'average_service' => $revenueData->avg_service_cost ?? 0,
            'completed_services' => $revenueData->completed_count ?? 0,
            'this_month' => $thisMonthRevenue ?? 0,
        ];
    }

    private function getClientServiceStats(int $userId): array
    {
        return [
            'total' => Service::where('user_id', $userId)->count(),
            'completed' => Service::where('user_id', $userId)
                ->where('status', 'completed')->count(),
            'pending' => Service::where('user_id', $userId)
                ->where('status', 'scheduled')->count(),
            'next_service' => Service::where('user_id', $userId)
                ->where('status', 'scheduled')
                ->orderBy('scheduled_date')
                ->first(),
        ];
    }

    private function getClientCertificateStats(int $userId): array
    {
        return [
            'total' => Certificate::where('user_id', $userId)->count(),
            'valid' => Certificate::where('user_id', $userId)
                ->where('status', 'valid')
                ->where('valid_until', '>=', now())
                ->count(),
            'expiring_soon' => Certificate::where('user_id', $userId)
                ->where('status', 'valid')
                ->whereBetween('valid_until', [now(), now()->addDays(30)])
                ->count(),
        ];
    }

    private function getClientNotificationStats(int $userId): array
    {
        return [
            'unread' => \App\Models\Notification::where('user_id', $userId)
                ->whereNull('read_at')->count(),
            'total' => \App\Models\Notification::where('user_id', $userId)->count(),
        ];
    }

    /**
     * Helper method to format time ago
     */
    private function getTimeAgo($datetime): string
    {
        $carbon = Carbon::parse($datetime);
        
        $diffInMinutes = $carbon->diffInMinutes(now());
        $diffInHours = $carbon->diffInHours(now());
        $diffInDays = $carbon->diffInDays(now());

        if ($diffInMinutes < 60) {
            return $diffInMinutes == 1 ? 'hace 1 minuto' : "hace {$diffInMinutes} minutos";
        } elseif ($diffInHours < 24) {
            return $diffInHours == 1 ? 'hace 1 hora' : "hace {$diffInHours} horas";
        } elseif ($diffInDays < 7) {
            return $diffInDays == 1 ? 'hace 1 día' : "hace {$diffInDays} días";
        } else {
            return $carbon->format('d/m/Y');
        }
    }
}