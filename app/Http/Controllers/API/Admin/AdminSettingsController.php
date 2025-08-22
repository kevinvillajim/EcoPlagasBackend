<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminSettingsController extends Controller
{
    /**
     * Get all admin settings
     */
    public function index(): JsonResponse
    {
        try {
            $settings = AdminSetting::getAllSettings();
            
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuraciones'
            ], 500);
        }
    }

    /**
     * Update admin settings
     */
    public function update(Request $request): JsonResponse
    {
        // Log incoming request data for debugging
        \Log::info('AdminSettings update request received:', [
            'request_data' => $request->all(),
            'serviceSettings' => $request->get('serviceSettings'),
            'serviceSettings_type' => gettype($request->get('serviceSettings'))
        ]);
        $validator = Validator::make($request->all(), [
            'businessHours' => 'sometimes|array',
            'businessHours.*.open' => 'sometimes|date_format:H:i',
            'businessHours.*.close' => 'sometimes|date_format:H:i',
            'businessHours.*.isOpen' => 'sometimes|boolean',
            
            'serviceSettings' => 'sometimes|array',
            'serviceSettings.defaultServiceDuration' => 'sometimes|integer|min:30|max:480',
            'serviceSettings.bufferTimeBetweenServices' => 'sometimes|integer|min:15|max:120',
            'serviceSettings.minimumAdvanceDays' => 'sometimes|integer|min:1|max:30',
            'serviceSettings.enableAdvanceBooking' => 'sometimes|boolean',
            'serviceSettings.allowWeekendBooking' => 'sometimes|boolean',
            
            'pricingSettings' => 'sometimes|array',
            'pricingSettings.currency' => 'sometimes|string|in:USD,EUR,COL',
            'pricingSettings.baseServicePrice' => 'sometimes|numeric|min:0',
            'pricingSettings.includeTax' => 'sometimes|boolean',
            'pricingSettings.taxRate' => 'sometimes|numeric|min:0|max:50',
            'pricingSettings.showPrices' => 'sometimes|boolean',
            'pricingSettings.emergencyServiceSurcharge' => 'sometimes|integer|min:0|max:200',
            'pricingSettings.weekendSurcharge' => 'sometimes|integer|min:0|max:100',
            'pricingSettings.servicePrices' => 'sometimes|array',
            'pricingSettings.servicePrices.*.min' => 'sometimes|numeric|min:0',
            'pricingSettings.servicePrices.*.max' => 'sometimes|numeric|min:0',
            'pricingSettings.servicePrices.*.enabled' => 'sometimes|boolean',
            
            'notificationSettings' => 'sometimes|array',
            'notificationSettings.emailNotifications' => 'sometimes|boolean',
            'notificationSettings.clientReminders' => 'sometimes|boolean',
            'notificationSettings.adminAlerts' => 'sometimes|boolean',
            'notificationSettings.reminderHours' => 'sometimes|integer|min:1|max:168',
            'notificationSettings.followUpDays' => 'sometimes|integer|min:1|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update settings
            AdminSetting::updateSettings($request->only([
                'businessHours',
                'serviceSettings', 
                'pricingSettings',
                'notificationSettings'
            ]));

            // Get updated settings
            $settings = AdminSetting::getAllSettings();

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones actualizadas exitosamente',
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuraciones'
            ], 500);
        }
    }

    /**
     * Reset settings to default values
     */
    public function reset(): JsonResponse
    {
        try {
            $defaultSettings = [
                'businessHours' => [
                    'monday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'tuesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'wednesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'thursday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'friday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'saturday' => ['open' => '08:00', 'close' => '14:00', 'isOpen' => true],
                    'sunday' => ['open' => '08:00', 'close' => '12:00', 'isOpen' => false]
                ],
                'serviceSettings' => [
                    'defaultServiceDuration' => 120,
                    'bufferTimeBetweenServices' => 30,
                    'minimumAdvanceDays' => 1,
                    'enableAdvanceBooking' => true,
                    'allowWeekendBooking' => true,
                ],
                'pricingSettings' => [
                    'currency' => 'USD',
                    'baseServicePrice' => 75.00,
                    'includeTax' => true,
                    'taxRate' => 12,
                    'showPrices' => true,
                    'emergencyServiceSurcharge' => 50,
                    'weekendSurcharge' => 25,
                    'servicePrices' => [
                        'residential' => ['min' => 60, 'max' => 150, 'enabled' => true],
                        'commercial' => ['min' => 100, 'max' => 500, 'enabled' => true],
                        'industrial' => ['min' => 200, 'max' => 1000, 'enabled' => true],
                        'emergency' => ['min' => 80, 'max' => 300, 'enabled' => true]
                    ]
                ],
                'notificationSettings' => [
                    'emailNotifications' => true,
                    'clientReminders' => true,
                    'adminAlerts' => true,
                    'reminderHours' => 24,
                    'followUpDays' => 7
                ]
            ];

            AdminSetting::updateSettings($defaultSettings);

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones restablecidas a valores por defecto',
                'settings' => $defaultSettings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer configuraciones'
            ], 500);
        }
    }

    /**
     * Get specific setting by key
     */
    public function getSetting(string $key): JsonResponse
    {
        try {
            $value = AdminSetting::get($key);
            
            if ($value === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'key' => $key,
                'value' => $value
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración'
            ], 500);
        }
    }
}
