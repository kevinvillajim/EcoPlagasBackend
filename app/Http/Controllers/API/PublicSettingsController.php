<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicSettingsController extends Controller
{
    /**
     * Get business hours for public consumption
     */
    public function getBusinessHours(): JsonResponse
    {
        try {
            $businessHours = AdminSetting::get('business_hours', []);
            
            return response()->json([
                'success' => true,
                'business_hours' => $businessHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios de atención'
            ], 500);
        }
    }

    /**
     * Get service settings for public consumption (for client booking)
     */
    public function getServiceSettings(): JsonResponse
    {
        try {
            $serviceSettings = AdminSetting::get('service_settings', []);
            
            // Only return settings relevant for client booking
            $publicSettings = [
                'defaultServiceDuration' => $serviceSettings['defaultServiceDuration'] ?? 120,
                'bufferTimeBetweenServices' => $serviceSettings['bufferTimeBetweenServices'] ?? 30,
                'minimumAdvanceDays' => $serviceSettings['minimumAdvanceDays'] ?? 1,
                'enableAdvanceBooking' => $serviceSettings['enableAdvanceBooking'] ?? true,
                'allowWeekendBooking' => $serviceSettings['allowWeekendBooking'] ?? true
            ];
            
            return response()->json([
                'success' => true,
                'service_settings' => $publicSettings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de servicios'
            ], 500);
        }
    }

    /**
     * Get combined booking configuration for client
     */
    public function getBookingConfig(): JsonResponse
    {
        try {
            $businessHours = AdminSetting::get('business_hours', []);
            $serviceSettings = AdminSetting::get('service_settings', []);
            
            // Extract working hours range
            $workingHours = $this->extractWorkingHours($businessHours);
            
            $config = [
                'businessHours' => $businessHours,
                'workingHours' => $workingHours,
                'serviceSettings' => [
                    'defaultServiceDuration' => $serviceSettings['defaultServiceDuration'] ?? 120,
                    'bufferTimeBetweenServices' => $serviceSettings['bufferTimeBetweenServices'] ?? 30,
                    'minimumAdvanceDays' => $serviceSettings['minimumAdvanceDays'] ?? 1,
                    'enableAdvanceBooking' => $serviceSettings['enableAdvanceBooking'] ?? true,
                    'allowWeekendBooking' => $serviceSettings['allowWeekendBooking'] ?? true
                ]
            ];
            
            return response()->json([
                'success' => true,
                'booking_config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de reservas'
            ], 500);
        }
    }

    /**
     * Extract working hours range from business hours
     */
    private function extractWorkingHours(array $businessHours): array
    {
        $earliestOpen = '23:59';
        $latestClose = '00:00';
        $hasOpenDays = false;
        
        foreach ($businessHours as $day => $hours) {
            if ($hours['isOpen'] ?? false) {
                $hasOpenDays = true;
                $open = $hours['open'] ?? '08:00';
                $close = $hours['close'] ?? '18:00';
                
                if ($open < $earliestOpen) {
                    $earliestOpen = $open;
                }
                if ($close > $latestClose) {
                    $latestClose = $close;
                }
            }
        }
        
        return [
            'start' => $hasOpenDays ? $earliestOpen : '08:00',
            'end' => $hasOpenDays ? $latestClose : '18:00',
            'hasOpenDays' => $hasOpenDays
        ];
    }

    /**
     * Get pricing configuration for public consumption
     */
    public function getPricingSettings(): JsonResponse
    {
        try {
            $pricingSettings = AdminSetting::get('pricing_settings', []);
            
            // Only return relevant pricing info for public
            $publicPricing = [
                'currency' => $pricingSettings['currency'] ?? 'USD',
                'baseServicePrice' => $pricingSettings['baseServicePrice'] ?? 75.00,
                'includeTax' => $pricingSettings['includeTax'] ?? true,
                'taxRate' => $pricingSettings['taxRate'] ?? 12,
                'showPrices' => $pricingSettings['showPrices'] ?? true,
                'emergencyServiceSurcharge' => $pricingSettings['emergencyServiceSurcharge'] ?? 50,
                'weekendSurcharge' => $pricingSettings['weekendSurcharge'] ?? 25,
                'servicePrices' => $pricingSettings['servicePrices'] ?? [
                    'residential' => ['min' => 60, 'max' => 150, 'enabled' => true],
                    'commercial' => ['min' => 100, 'max' => 500, 'enabled' => true],
                    'industrial' => ['min' => 200, 'max' => 1000, 'enabled' => true],
                    'emergency' => ['min' => 80, 'max' => 300, 'enabled' => true]
                ]
            ];
            
            return response()->json([
                'success' => true,
                'pricing_settings' => $publicPricing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de precios'
            ], 500);
        }
    }

    /**
     * Calculate service price based on parameters
     */
    public function calculateServicePrice(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string',
            'is_emergency' => 'sometimes|boolean',
            'is_weekend' => 'sometimes|boolean'
        ]);

        try {
            $pricingSettings = AdminSetting::get('pricing_settings', []);
            
            $basePrice = $pricingSettings['baseServicePrice'] ?? 75.00;
            $includeTax = $pricingSettings['includeTax'] ?? true;
            $taxRate = $pricingSettings['taxRate'] ?? 12;
            $emergencySurcharge = $pricingSettings['emergencyServiceSurcharge'] ?? 50;
            $weekendSurcharge = $pricingSettings['weekendSurcharge'] ?? 25;
            
            // Calculate service price
            $price = $basePrice;
            
            // Apply emergency surcharge if applicable
            if ($request->get('is_emergency', false) && $emergencySurcharge > 0) {
                $price += ($basePrice * $emergencySurcharge / 100);
            }
            
            // Apply weekend surcharge if applicable
            if ($request->get('is_weekend', false) && $weekendSurcharge > 0) {
                $price += ($basePrice * $weekendSurcharge / 100);
            }
            
            // Calculate tax only if includeTax is true
            $tax = $includeTax ? ($price * ($taxRate / 100)) : 0;
            $total = $price + $tax;
            
            return response()->json([
                'success' => true,
                'price_breakdown' => [
                    'base_price' => $basePrice,
                    'service_price' => round($price, 2),
                    'include_tax' => $includeTax,
                    'tax_rate' => $includeTax ? $taxRate : 0,
                    'tax' => round($tax, 2),
                    'total' => round($total, 2),
                    'currency' => $pricingSettings['currency'] ?? 'USD',
                    'emergency_surcharge' => $request->get('is_emergency', false) ? $emergencySurcharge : 0,
                    'weekend_surcharge' => $request->get('is_weekend', false) ? $weekendSurcharge : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular precio del servicio'
            ], 500);
        }
    }
}
