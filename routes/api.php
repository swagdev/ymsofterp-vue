<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceOrderController;
use App\Http\Controllers\MaintenanceLabelController;
use App\Http\Controllers\MaintenancePriorityController;
use App\Http\Controllers\MaintenanceCommentController;
use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\RetailController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\Api\MaintenancePurchaseOrderController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MaintenancePurchaseOrderInvoiceController;
use App\Http\Controllers\MaintenancePurchaseOrderReceiveController;
use App\Http\Controllers\Api\MaintenanceEvidenceController;
use App\Http\Controllers\MaintenanceTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\QuoteController;

//api flutter justusmember
use App\Http\Controllers\Api\ApiFlutterController;
use App\Http\Controllers\Api\ApiBackupDBController;
use App\Http\Controllers\Api\ApiAppManToolsController;
use App\Http\Controllers\Api\ApiLoadDataMenuRestoController;
use App\Http\Controllers\Api\ApiPushNotificationController;
use App\Http\Controllers\Api\SurveyPelangganController;
use App\Http\Controllers\Api\ApiMembershipController;
use App\Http\Controllers\Api\ApiLocationsController;
use App\Http\Controllers\Api\ApiBrandsMenuController;






// api flutter justusmember
// Route::get('/flutter/login', [ApiFlutterController::class, 'login']);
Route::post('/flutter/login', [ApiFlutterController::class, 'login']);
// Route::get('/flutter/login/admin_cashier', [ApiFlutterController::class, 'loginAdminCashier']);
Route::post('/flutter/login/admin_cashier', [ApiFlutterController::class, 'loginAdminCashier']);
Route::get('/flutter/login/update/token', [ApiFlutterController::class, 'loginUpdateToken']);
Route::post('/flutter/login/update/token', [ApiFlutterController::class, 'loginUpdateToken']);
Route::get('/flutter/login/check/popup', [ApiFlutterController::class, 'loginCheckPopup']);
Route::post('/flutter/login/check/popup', [ApiFlutterController::class, 'loginCheckPopup']);
Route::get('/flutter/register', [ApiFlutterController::class, 'register']);
Route::post('/flutter/register', [ApiFlutterController::class, 'register']);
Route::get('/flutter/forgot_password', [ApiFlutterController::class, 'forgotPassword']);
Route::post('/flutter/forgot_password', [ApiFlutterController::class, 'forgotPassword']);
Route::get('/flutter/scan_promo_barcode', [ApiFlutterController::class, 'scanPromoBarcode']);
Route::post('/flutter/scan_promo_barcode', [ApiFlutterController::class, 'scanPromoBarcode']);
Route::get('/flutter/scan_promo_barcode_update', [ApiFlutterController::class, 'scanPromoBarcodeUpdate']);
Route::post('/flutter/scan_promo_barcode_update', [ApiFlutterController::class, 'scanPromoBarcodeUpdate']);
Route::post('/flutter/get_point', [ApiFlutterController::class, 'getPoint']);
Route::get('/flutter/get_point', [ApiFlutterController::class, 'getPoint']);
Route::post('/flutter/get_point_table', [ApiFlutterController::class, 'getPointTable']);
Route::get('/flutter/get_point_table', [ApiFlutterController::class, 'getPointTable']);
Route::post('/flutter/update_profile', [ApiFlutterController::class, 'updateProfile']);
Route::get('/flutter/update_profile', [ApiFlutterController::class, 'updateProfile']);
Route::post('/flutter/update_profile_password', [ApiFlutterController::class, 'updateProfilePassword']);
Route::get('/flutter/update_profile_password', [ApiFlutterController::class, 'updateProfilePassword']);
Route::post('/flutter/get_img_slide_beranda', [ApiFlutterController::class, 'getImgSlideBeranda']);
Route::get('/flutter/get_img_slide_beranda', [ApiFlutterController::class, 'getImgSlideBeranda']);
Route::post('/flutter/news', [ApiFlutterController::class, 'getNews']);
Route::get('/flutter/news', [ApiFlutterController::class, 'getNews']);
Route::post('/flutter/send_wa', [ApiFlutterController::class, 'sendWA']);
Route::get('/flutter/send_wa', [ApiFlutterController::class, 'sendWA']);
Route::post('/flutter/send_wa_fonnte', [ApiFlutterController::class, 'sendWAFonnte']);
Route::get('/flutter/send_wa_fonnte', [ApiFlutterController::class, 'sendWAFonnte']);
Route::post('/flutter/send_email', [ApiFlutterController::class, 'sendEmail']);
Route::get('/flutter/send_email', [ApiFlutterController::class, 'sendEmail']);
Route::post('/flutter/food_n_beverages', [ApiFlutterController::class, 'foodNBeverages']);
Route::get('/flutter/food_n_beverages', [ApiFlutterController::class, 'foodNBeverages']);
Route::post('/flutter/store_location', [ApiFlutterController::class, 'storeLocation']);
Route::get('/flutter/store_location', [ApiFlutterController::class, 'storeLocation']);
Route::post('/flutter/gift_inbox', [ApiFlutterController::class, 'giftInbox']);
Route::get('/flutter/gift_inbox', [ApiFlutterController::class, 'giftInbox']);
Route::post('/flutter/about_us', [ApiFlutterController::class, 'aboutUs']);
Route::get('/flutter/about_us', [ApiFlutterController::class, 'aboutUs']);
//https://justusmember.co.id/api/flutter/daily_push_notif
Route::get('/flutter/daily_push_notif', [ApiFlutterController::class, 'dailyPushNotif']);
Route::get('/flutter/single_push_notif', [ApiFlutterController::class, 'singlePushNotif']);
Route::post('/flutter/single_push_notif', [ApiFlutterController::class, 'singlePushNotif']);
Route::get('/flutter/request_push_notif', [ApiFlutterController::class, 'dailyAutoPushNotifInbox']);
Route::get('/flutter/happybirthday', [ApiFlutterController::class, 'dailyPushBirthDay']);

Route::get('/flutter/test', [ApiFlutterController::class, 'test']);

Route::get('/backup/db_member', [ApiBackupDBController::class, 'index']);


Route::get('/flutter/get_latest_version_from_server', [ApiFlutterController::class, 'getLatestVersionFromServer']);
Route::get('/flutter', [ApiFlutterController::class, 'index']);


//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('/flutter_app_man_tools', [ApiAppManToolsController::class, 'index']);
Route::get('/flutter_app_man_tools/load_chart', [ApiAppManToolsController::class, 'loadChart']);
Route::post('/flutter_app_man_tools/load_chart', [ApiAppManToolsController::class, 'loadChart']);
Route::get('/flutter_app_man_tools/load_outlet_transaction', [ApiAppManToolsController::class, 'loadOutletTransaction']);
Route::post('/flutter_app_man_tools/load_outlet_transaction', [ApiAppManToolsController::class, 'loadOutletTransaction']);
Route::get('/flutter_app_man_tools/load_outlet_transaction_detail', [ApiAppManToolsController::class, 'loadOutletTransactionDetail']);
Route::post('/flutter_app_man_tools/load_outlet_transaction_detail', [ApiAppManToolsController::class, 'loadOutletTransactionDetail']);
Route::get('/flutter_app_man_tools/load_outlet_master', [ApiAppManToolsController::class, 'loadOutletMaster']);
Route::post('/flutter_app_man_tools/load_outlet_master', [ApiAppManToolsController::class, 'loadOutletMaster']);

Route::get('/flutter_app_man_tools/load_menu_app', [ApiLoadDataMenuRestoController::class, 'loadMenuApp']);
Route::post('/flutter_app_man_tools/load_menu_app', [ApiLoadDataMenuRestoController::class, 'loadMenuApp']);

Route::get('/flutter_app_man_tools/load_menu_app_testing', [ApiLoadDataMenuRestoController::class, 'loadMenuAppTesting']);
Route::post('/flutter_app_man_tools/load_menu_app_testing', [ApiLoadDataMenuRestoController::class, 'loadMenuAppTesting']);

Route::get('/flutter_app_man_tools/load_data_menu_justus', [ApiLoadDataMenuRestoController::class, 'loadMenuJustus']);
Route::post('/flutter_app_man_tools/load_data_menu_justus', [ApiLoadDataMenuRestoController::class, 'loadMenuJustus']);

Route::get('/flutter_app_man_tools/load_data_menu_tempayan', [ApiLoadDataMenuRestoController::class, 'loadMenuTempayan']);
Route::post('/flutter_app_man_tools/load_data_menu_tempayan', [ApiLoadDataMenuRestoController::class, 'loadMenuTempayan']);

Route::get('/cronjob/pushnotification', [ApiLoadDataMenuRestoController::class, 'index']);
Route::get('/cronjob/pushnotification/target', [ApiLoadDataMenuRestoController::class, 'loadDataTargetNotif']);
Route::get('/cronjob/pushnotification/load_promo_8_agustus_8_september', [ApiLoadDataMenuRestoController::class, 'loadPromo8a8s']);

// Route::get('/flutter_app_man_tools/connsqlserver', 'ApiAppManToolsController@sqlServerQueryExample');
// Route::post('/flutter_app_man_tools/connsqlserver', 'ApiAppManToolsController@sqlServerQueryExample');
// Route::get('/flutter_app_man_tools/connodbc', 'ApiAppManToolsController@queryODBC');
// Route::post('/flutter_app_man_tools/connodbc', 'ApiAppManToolsController@queryODBC');

Route::match(['get', 'post'], '/survey_pelanggan', [SurveyPelangganController::class, 'apiLoadData']);
Route::match(['get', 'post'], '/check_membership', [ApiMembershipController::class, 'checkMembership']);
Route::match(['get', 'post'], '/report_member', [ApiMembershipController::class, 'reportMember']);
Route::match(['get', 'post'], '/get_member_outlet', [ApiMembershipController::class, 'getMemberOutlet']);
Route::match(['get'], '/get_trans_poin_member/{member_id}', [ApiMembershipController::class, 'getTransPoinMember']);

Route::match(['get', 'post'], '/get_history_send_gift', [ApiMembershipController::class, 'getHistorySendGift']);


Route::match(['get', 'post'], '/web_profile_locations/load', [ApiLocationsController::class, 'load']);

Route::get('/brands/menu/{id}', [ApiBrandsMenuController::class, 'menu']);

Route::post('/flutter/data-popup-alert', [ApiFlutterController::class, 'dataPopupAlert']);







// Endpoint API untuk Kanban Maintenance Order
Route::get('/outlet', [MaintenanceOrderController::class, 'getOutlets']);
Route::get('/ruko', [MaintenanceOrderController::class, 'getRukos']);
Route::get('/maintenance-order', [MaintenanceOrderController::class, 'index']);
Route::patch('/maintenance-order/{id}', [MaintenanceOrderController::class, 'updateStatus']);
Route::get('/maintenance-labels', [MaintenanceLabelController::class, 'index']);
Route::get('/maintenance-priorities', [MaintenancePriorityController::class, 'index']);
Route::middleware(['auth:web'])->group(function () {
    Route::post('/maintenance-order', [MaintenanceOrderController::class, 'store']);
});
Route::delete('/maintenance-tasks/{id}', [MaintenanceTaskController::class, 'destroy']);
// Maintenance Comment Routes
Route::get('/maintenance-comments/{taskId}', [MaintenanceCommentController::class, 'index']);
Route::get('/maintenance-comments/{taskId}/count', [MaintenanceCommentController::class, 'count']);
Route::post('/maintenance-comments', [MaintenanceCommentController::class, 'store']);
Route::delete('/maintenance-comments/{id}', [MaintenanceCommentController::class, 'destroy']);

// New endpoints
Route::get('/assignable-users', [MaintenanceOrderController::class, 'assignableUsers']);
Route::get('/maintenance-members/{taskId}', [MaintenanceOrderController::class, 'getTaskMembers']);
Route::post('/assign-members', [MaintenanceOrderController::class, 'assignMembers']);

// Action Plan Routes
Route::post('/action-plans', [ActionPlanController::class, 'store']);
Route::get('/action-plans/task/{taskId}', [ActionPlanController::class, 'getByTask']);
Route::delete('/action-plans/media/{mediaId}', [ActionPlanController::class, 'deleteMedia']); 

// Retail Routes
Route::post('/retail', [RetailController::class, 'store']);
Route::get('/retail/task/{taskId}', [RetailController::class, 'getByTask']);
Route::delete('/retail/image/{imageId}/{type}', [RetailController::class, 'deleteImage']);

Route::match(['get', 'post'], '/send-fcm', [FcmController::class, 'sendFcmNotification']);

// Purchase Order Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('maintenance-tasks/{taskId}/purchase-orders')->group(function () {
        Route::get('/', [MaintenancePurchaseOrderController::class, 'index']);
        Route::post('/', [MaintenancePurchaseOrderController::class, 'store']);
        Route::get('/{poId}', [MaintenancePurchaseOrderController::class, 'show']);
        Route::put('/{poId}', [MaintenancePurchaseOrderController::class, 'update']);
        Route::delete('/{poId}', [MaintenancePurchaseOrderController::class, 'destroy']);
    });
    
    // Maintenance PO routes
    Route::post('/maintenance-tasks/{taskId}/purchase-orders/{poId}/approve', [MaintenancePurchaseOrderController::class, 'approve']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Evidence routes
    Route::post('/maintenance-evidence', [MaintenanceEvidenceController::class, 'store']);
    Route::get('/maintenance-evidence/{taskId}', [MaintenanceEvidenceController::class, 'show']);
}); 

// Supplier Routes
Route::get('/suppliers', function () {
    return response()->json(DB::table('suppliers')
        ->where('status', 'active')
        ->select('id', 'name')
        ->orderBy('name')
        ->get()
    );
}); 

// Purchase Order Invoice Routes
Route::get('/purchase-orders/{poId}/invoices', [MaintenancePurchaseOrderInvoiceController::class, 'index']);
Route::post('/purchase-orders/{poId}/invoices', [MaintenancePurchaseOrderInvoiceController::class, 'store']);
Route::post('/purchase-orders/{poId}/receive', [MaintenancePurchaseOrderReceiveController::class, 'store']);
Route::get('/purchase-orders/{poId}/receives', [\App\Http\Controllers\MaintenancePurchaseOrderReceiveController::class, 'index']); 

// Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/maintenance', [DashboardController::class, 'index']);
    Route::get('/dashboard/maintenance/filter', [DashboardController::class, 'filter']);
    Route::get('/dashboard/maintenance/report', [\App\Http\Controllers\DashboardController::class, 'exportExcel']);
    Route::get('/maintenance-tasks/{id}/detail', [\App\Http\Controllers\DashboardController::class, 'taskDetail']);
    Route::get('/maintenance-tasks/all', [\App\Http\Controllers\DashboardController::class, 'allTasks']);
    Route::get('/maintenance-tasks/done', [\App\Http\Controllers\DashboardController::class, 'allDoneTasks']);
    Route::get('/maintenance-tasks/leaderboard-done', [\App\Http\Controllers\DashboardController::class, 'doneTasksLeaderboard']);
    Route::get('/maintenance-po-latest', [\App\Http\Controllers\DashboardController::class, 'polatestWithDetail']);
    Route::get('/maintenance-pr-latest', [\App\Http\Controllers\DashboardController::class, 'allPRWithDetail']);
    Route::get('/retail-latest', [\App\Http\Controllers\DashboardController::class, 'allRetailWithDetail']);
    Route::get('/activity-latest', [\App\Http\Controllers\DashboardController::class, 'allActivityWithDetail']);
    Route::get('/overdue-tasks/all', [\App\Http\Controllers\DashboardController::class, 'allOverdueTasks']);
    Route::get('/dashboard/task-completion-stats', [\App\Http\Controllers\DashboardController::class, 'taskCompletionStats']);
    Route::get('/dashboard/task-by-due-date-stats', [\App\Http\Controllers\DashboardController::class, 'taskByDueDateStats']);
}); 

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);
});

Route::get('/quotes/{dayOfYear}', [QuoteController::class, 'getQuoteByDay']);