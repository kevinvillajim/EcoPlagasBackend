<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// CORS handled by Laravel native middleware now

// Controllers
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Client\ClientController;
use App\Http\Controllers\API\Client\ServiceController as ClientServiceController;
use App\Http\Controllers\API\Client\CertificateController as ClientCertificateController;
use App\Http\Controllers\API\Admin\AdminController;
use App\Http\Controllers\API\Admin\UserManagementController;
use App\Http\Controllers\API\Admin\ServiceManagementController;
use App\Http\Controllers\API\Admin\CertificateManagementController;
use App\Http\Controllers\API\Admin\NotificationManagementController;
use App\Http\Controllers\API\Admin\ReviewManagementController;
use App\Http\Controllers\API\Client\ReviewController;
use App\Http\Controllers\API\PublicReviewController;
use App\Http\Controllers\API\PublicGalleryController;
use App\Http\Controllers\API\Admin\GalleryManagementController;
use App\Http\Controllers\API\Admin\AdminSettingsController;
use App\Http\Controllers\API\PublicSettingsController;
use App\Http\Controllers\API\Client\ProfileController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (no middleware)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password');
    Route::post('activate-account', [AuthController::class, 'activateAccount'])->name('api.auth.activate-account');
    Route::post('validate-reset-token', [AuthController::class, 'validateResetToken'])->name('api.auth.validate-reset-token');
    Route::post('validate-activation-token', [AuthController::class, 'validateActivationToken'])->name('api.auth.validate-activation-token');
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('refresh-token', [AuthController::class, 'refreshToken'])->name('api.auth.refresh-token');
        Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::put('profile', [AuthController::class, 'updateProfile'])->name('api.auth.update-profile');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('api.auth.change-password');
    });
});

// Public review routes (no authentication required)
Route::prefix('reviews')->name('api.reviews.')->group(function () {
    Route::get('/', [PublicReviewController::class, 'index'])->name('index');
    Route::get('featured', [PublicReviewController::class, 'getFeatured'])->name('featured');
    Route::get('stats', [PublicReviewController::class, 'getStats'])->name('stats');
    Route::get('service-types', [PublicReviewController::class, 'getServiceTypes'])->name('service-types');
    Route::get('{review}', [PublicReviewController::class, 'show'])->name('show');
    Route::put('{review}/helpful', [PublicReviewController::class, 'markHelpful'])->name('mark-helpful');
});

// Public gallery routes (no authentication required)
Route::prefix('gallery')->name('api.gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'index'])->name('index');
    Route::get('featured', [PublicGalleryController::class, 'getFeatured'])->name('featured');
    Route::get('featured/random', [PublicGalleryController::class, 'getFeaturedRandom'])->name('featured.random');
    Route::get('categories', [PublicGalleryController::class, 'getCategories'])->name('categories');
    Route::get('{gallery}', [PublicGalleryController::class, 'show'])->name('show');
});

// Public PDF preview route (no authentication required)
Route::get('certificates/{certificate}/preview', [CertificateManagementController::class, 'previewPdf'])->name('api.certificates.preview-pdf');

// Public settings routes (no authentication required) - for client booking system
Route::prefix('public-settings')->name('api.public-settings.')->group(function () {
    Route::get('business-hours', [PublicSettingsController::class, 'getBusinessHours'])->name('business-hours');
    Route::get('service-settings', [PublicSettingsController::class, 'getServiceSettings'])->name('service-settings');
    Route::get('booking-config', [PublicSettingsController::class, 'getBookingConfig'])->name('booking-config');
    Route::get('pricing', [PublicSettingsController::class, 'getPricingSettings'])->name('pricing');
    Route::post('calculate-price', [PublicSettingsController::class, 'calculateServicePrice'])->name('calculate-price');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Common notification routes (available to all authenticated users)
    Route::prefix('notifications')->name('api.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::put('{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::put('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
    });
    
    // Common chat routes (available to all authenticated users)
    Route::prefix('chats')->name('api.chats.')->group(function () {
        Route::get('{chat}/messages', [ChatController::class, 'getChatMessages'])->name('messages');
        Route::post('{chat}/messages', [ChatController::class, 'sendAdminMessage'])->name('send-message');
        Route::post('{chat}/read', [ChatController::class, 'markChatAsRead'])->name('mark-read');
        Route::post('{chat}/typing', [ChatController::class, 'sendTypingIndicator'])->name('typing');
    });
    
    // Client routes
    Route::prefix('client')->middleware('role:client')->name('api.client.')->group(function () {
        Route::get('dashboard', [ClientController::class, 'dashboard'])->name('dashboard');
        Route::get('profile', [ProfileController::class, 'getProfile'])->name('profile');
        Route::put('profile', [ProfileController::class, 'updateProfile'])->name('update-profile');
        Route::post('change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        
        // Client services
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [ClientServiceController::class, 'index'])->name('index');
            Route::post('/', [ClientServiceController::class, 'store'])->name('store');
            Route::post('emergency', [ClientServiceController::class, 'storeEmergency'])->name('store-emergency');
            Route::get('types', [ClientServiceController::class, 'getServiceTypes'])->name('types');
            Route::get('settings', [ClientServiceController::class, 'getServiceSettings'])->name('settings');
            Route::get('occupied-slots', [ClientServiceController::class, 'getOccupiedTimeSlots'])->name('occupied-slots');
            Route::get('history', [ClientServiceController::class, 'history'])->name('history');
            Route::get('{service}', [ClientServiceController::class, 'show'])->name('show');
            Route::put('{service}', [ClientServiceController::class, 'update'])->name('update');
            Route::delete('{service}', [ClientServiceController::class, 'destroy'])->name('destroy');
        });
        
        // Client certificates
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [ClientCertificateController::class, 'index'])->name('index');
            Route::get('{certificate}', [ClientCertificateController::class, 'show'])->name('show');
            Route::get('{certificate}/download', [ClientCertificateController::class, 'download'])->name('download');
        });
        
        // Client reviews
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::post('/', [ReviewController::class, 'store'])->name('store');
            Route::get('reviewable-services', [ReviewController::class, 'getReviewableServices'])->name('reviewable-services');
            Route::put('{review}', [ReviewController::class, 'update'])->name('update');
            Route::delete('{review}', [ReviewController::class, 'destroy'])->name('destroy');
            Route::put('{review}/helpful', [ReviewController::class, 'markHelpful'])->name('mark-helpful');
        });
        
        // Client chat
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/', [ChatController::class, 'getClientChat'])->name('index');
            Route::get('messages', [ChatController::class, 'getClientMessages'])->name('messages');
            Route::post('messages', [ChatController::class, 'sendClientMessage'])->name('send-message');
            Route::post('read', [ChatController::class, 'markClientChatAsRead'])->name('mark-read');
            Route::post('typing', [ChatController::class, 'sendClientTypingIndicator'])->name('typing');
        });
    });
    
    // Admin routes
    Route::prefix('admin')->middleware('admin')->name('api.admin.')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('email-status', [AuthController::class, 'getEmailStatus'])->name('email-status');
        
        // User management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::post('register', [AuthController::class, 'register'])->name('register'); // Admin user creation
            Route::get('{user}', [UserManagementController::class, 'show'])->name('show');
            Route::put('{user}', [UserManagementController::class, 'update'])->name('update');
            Route::delete('{user}', [UserManagementController::class, 'destroy'])->name('destroy');
            Route::put('{user}/status', [UserManagementController::class, 'updateStatus'])->name('update-status');
        });
        
        // Service management
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceManagementController::class, 'index'])->name('index');
            Route::post('/', [ServiceManagementController::class, 'store'])->name('store');
            Route::get('scheduled', [ServiceManagementController::class, 'scheduled'])->name('scheduled');
            Route::get('{service}', [ServiceManagementController::class, 'show'])->name('show');
            Route::put('{service}', [ServiceManagementController::class, 'update'])->name('update');
            Route::delete('{service}', [ServiceManagementController::class, 'destroy'])->name('destroy');
            Route::put('{service}/assign-technician', [ServiceManagementController::class, 'assignTechnician'])->name('assign-technician');
        });

        // Calendar endpoints
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('events', [ServiceManagementController::class, 'getCalendarEvents'])->name('events');
            Route::get('availability', [ServiceManagementController::class, 'getAvailabilitySlots'])->name('availability');
            Route::get('stats', [ServiceManagementController::class, 'getCalendarStats'])->name('stats');
        });

        // Certificate management
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [CertificateManagementController::class, 'index'])->name('index');
            Route::post('/', [CertificateManagementController::class, 'store'])->name('store');
            Route::get('eligible-services', [CertificateManagementController::class, 'getEligibleServices'])->name('eligible-services');
            Route::get('stats', [CertificateManagementController::class, 'getStats'])->name('stats');
            Route::get('{certificate}/pdf', [CertificateManagementController::class, 'generatePdf'])->name('generate-pdf');
            Route::get('{certificate}/preview', [CertificateManagementController::class, 'previewPdf'])->name('preview-pdf');
            Route::get('{certificate}', [CertificateManagementController::class, 'show'])->name('show');
            Route::put('{certificate}', [CertificateManagementController::class, 'update'])->name('update');
            Route::delete('{certificate}', [CertificateManagementController::class, 'destroy'])->name('destroy');
        });

        // Notification management
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationManagementController::class, 'index'])->name('index');
            Route::post('/', [NotificationManagementController::class, 'store'])->name('store');
            Route::get('stats', [NotificationManagementController::class, 'getStats'])->name('stats');
            Route::post('send-automated', [NotificationManagementController::class, 'sendAutomatedNotifications'])->name('send-automated');
            Route::get('{notification}', [NotificationManagementController::class, 'show'])->name('show');
            Route::delete('{notification}', [NotificationManagementController::class, 'destroy'])->name('destroy');
        });

        // Review management
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewManagementController::class, 'index'])->name('index');
            Route::get('stats', [ReviewManagementController::class, 'getStats'])->name('stats');
            Route::put('{review}/approve', [ReviewManagementController::class, 'approve'])->name('approve');
            Route::put('{review}/reject', [ReviewManagementController::class, 'reject'])->name('reject');
            Route::put('{review}/toggle-featured', [ReviewManagementController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::put('{review}/respond', [ReviewManagementController::class, 'respond'])->name('respond');
            Route::put('settings', [ReviewManagementController::class, 'updateSettings'])->name('update-settings');
            Route::delete('{review}', [ReviewManagementController::class, 'destroy'])->name('destroy');
        });

        // Gallery management
        Route::prefix('gallery')->name('gallery.')->group(function () {
            Route::get('/', [GalleryManagementController::class, 'index'])->name('index');
            Route::post('/', [GalleryManagementController::class, 'store'])->name('store');
            Route::get('{gallery}', [GalleryManagementController::class, 'show'])->name('show');
            Route::post('{gallery}', [GalleryManagementController::class, 'update'])->name('update'); // POST for file upload
            Route::put('{gallery}', [GalleryManagementController::class, 'update'])->name('update-put'); // PUT for data only
            Route::delete('{gallery}', [GalleryManagementController::class, 'destroy'])->name('destroy');
            Route::put('{gallery}/toggle-status', [GalleryManagementController::class, 'toggleStatus'])->name('toggle-status');
            
            // Media management routes
            Route::delete('{gallery}/media/{media}', [GalleryManagementController::class, 'deleteMediaItem'])->name('delete-media');
            Route::put('{gallery}/media/order', [GalleryManagementController::class, 'updateMediaOrder'])->name('update-media-order');
        });

        // Admin Settings management
        Route::prefix('admin-settings')->name('admin-settings.')->group(function () {
            Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
            Route::put('/', [AdminSettingsController::class, 'update'])->name('update');
            Route::post('reset', [AdminSettingsController::class, 'reset'])->name('reset');
            Route::get('{key}', [AdminSettingsController::class, 'getSetting'])->name('get-setting');
        });

        // Admin chat management
        Route::prefix('chats')->name('chats.')->group(function () {
            Route::get('/', [ChatController::class, 'getConversations'])->name('index');
            Route::get('search', [ChatController::class, 'searchConversations'])->name('search');
            Route::get('{chat}/messages', [ChatController::class, 'getChatMessages'])->name('messages');
            Route::post('{chat}/messages', [ChatController::class, 'sendAdminMessage'])->name('send-message');
            Route::post('{chat}/read', [ChatController::class, 'markChatAsRead'])->name('mark-read');
            Route::patch('{chat}/status', [ChatController::class, 'updateChatStatus'])->name('update-status');
            Route::post('{chat}/typing', [ChatController::class, 'sendTypingIndicator'])->name('typing');
        });
    });
});

// SSE Stream routes (simplified as JSON responses)
Route::get('admin/chats/stream', [ChatController::class, 'adminChatStream'])->name('api.admin.chats.stream-public');
Route::get('client/chat/stream', [ChatController::class, 'clientChatStream'])->name('api.client.chat.stream-public');

// Health check route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
})->name('api.health');