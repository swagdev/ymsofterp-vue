<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MaintenanceOrderController;
use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\MaintenanceTaskController;
use App\Http\Controllers\MaintenancePurchaseRequisitionController;
use App\Http\Controllers\MaintenanceBAController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\MaintenancePurchaseOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MTPOPaymentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MasterPayrollController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\WarehouseDivisionController;
use App\Http\Controllers\MenuTypeController;
use App\Http\Controllers\OutletMapDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\ModifierOptionController;
use App\Http\Controllers\PrFoodController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return redirect('/home');
})->middleware(['auth', 'verified'])->name('dashboard');


// Data Karyawan routes
Route::middleware('auth')->group(function () {
    Route::get('/employee-data', [EmployeeController::class, 'index'])->name('employee.index');
    Route::get('/employee-create', [EmployeeController::class, 'create'])->name('employee.create');
    Route::get('/employee-edit/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
    Route::post('/employee-store', [EmployeeController::class, 'store'])->name('employee.store');
    Route::post('/employee-update/{id}', [EmployeeController::class, 'update'])->name('employee.update');
    Route::delete('/employee-delete/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
    Route::patch('/employee-data/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employee.toggle-status');
});

// Data Jabatan routes
Route::middleware('auth')->group(function () {
    Route::get('/position-data', [PositionController::class, 'index'])->name('position.index');
    Route::get('/position-create', [PositionController::class, 'create'])->name('position.create');
    Route::get('/position-edit/{id}', [PositionController::class, 'edit'])->name('position.edit');
    Route::post('/position-store', [PositionController::class, 'store'])->name('position.store');
    Route::post('/position-update/{id}', [PositionController::class, 'update'])->name('position.update');
    Route::delete('/position-delete/{id}', [PositionController::class, 'destroy'])->name('position.destroy');
    Route::patch('/position-data/{id}/toggle-status', [PositionController::class, 'toggleStatus'])->name('position.toggle-status');
});

// Data Level routes
Route::middleware('auth')->group(function () {
    Route::get('/level-data', [LevelController::class, 'index'])->name('level.index');
    Route::get('/level-create', [LevelController::class, 'create'])->name('level.create');
    Route::get('/level-edit/{id}', [LevelController::class, 'edit'])->name('level.edit');
    Route::post('/level-store', [LevelController::class, 'store'])->name('level.store');
    Route::post('/level-update/{id}', [LevelController::class, 'update'])->name('level.update');
    Route::delete('/level-delete/{id}', [LevelController::class, 'destroy'])->name('level.destroy');
    Route::patch('/level-data/{id}/toggle-status', [LevelController::class, 'toggleStatus'])->name('level.toggle-status');
});

Route::middleware('auth')->group(function () {
    Route::get('/master-payroll', [MasterPayrollController::class, 'index'])->name('master_payroll.index');
    Route::post('/master-payroll', [MasterPayrollController::class, 'index'])->name('master_payroll.index');
    Route::get('/master-payroll-create', [MasterPayrollController::class, 'create'])->name('master_payroll.create');
    // Route::get('/level-edit/{id}', [MasterPayrollController::class, 'edit'])->name('level.edit');
    // Route::post('/level-store', [MasterPayrollController::class, 'store'])->name('level.store');
    Route::patch('/master-payroll-update/{id}', [MasterPayrollController::class, 'update'])->name('master_payroll.update');
    Route::post('/master-payroll/load-master-other', [MasterPayrollController::class, 'loadmasterother'])->name('master_payroll.load_master_other');
    Route::post('/master-payroll/save-master-other', [MasterPayrollController::class, 'savemasterother'])->name('master_payroll.save_master_other');
    // Route::delete('/level-delete/{id}', [MasterPayrollController::class, 'destroy'])->name('level.destroy');
    // Route::patch('/level-data/{id}/toggle-status', [MasterPayrollController::class, 'toggleStatus'])->name('level.toggle-status');
});

// Data Member routes
Route::middleware('auth')->group(function () {
    Route::get('/member-data', [MemberController::class, 'index'])->name('member.index');
    Route::post('/member-data', [MemberController::class, 'index'])->name('member.index');
    Route::post('/member-data/{id}', [MemberController::class, 'update'])->name('member.update');
    Route::post('/detail-view', [MemberController::class, 'view_detail'])->name('member.view_detail');
    Route::delete('/member-data/{id}', [MemberController::class, 'destroy'])->name('member.destroy');
    Route::patch('/member-data/{id}/toggle-status', [MemberController::class, 'toggleStatus'])->name('member.toggle-status');
});

Route::middleware('auth')->group(function () {
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll-create', [PayrollController::class, 'create'])->name('master_payroll.create');
    Route::patch('/payroll-update/{id}', [PayrollController::class, 'update'])->name('master_payroll.update');
    Route::post('/payroll/load-master-other', [PayrollController::class, 'loadmasterother'])->name('master_payroll.load_master_other');
    Route::post('/payroll/save-master-other', [PayrollController::class, 'savemasterother'])->name('master_payroll.save_master_other');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/home', [\App\Http\Controllers\HomeController::class, 'show'])->name('home');
    
    // Tambahkan route untuk Maintenance Order
    Route::get('/maintenance-order', function () {
        return Inertia::render('MaintenanceOrder/index');
    })->name('maintenance-order');

    Route::post('/signature', [SignatureController::class, 'store'])->name('signature.store');

    Route::get('/dashboard-maintenance', function () {
        return Inertia::render('Dashboard/index');
    })->name('dashboard.maintenance');

    Route::post('/announcement/{id}/publish', [AnnouncementController::class, 'publish'])->name('announcement.publish');
});

// Action Plan Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/action-plans', [ActionPlanController::class, 'store']);
    Route::get('/api/action-plans/task/{taskId}', [ActionPlanController::class, 'getByTask']);
    Route::delete('/api/action-plans/media/{mediaId}', [ActionPlanController::class, 'deleteMedia']);
});

Route::post('/maintenance-tasks/{task}/update-status', [MaintenanceTaskController::class, 'updateStatus'])->name('maintenance-tasks.update-status');

Route::get('/api/maintenance-tasks/{task}/timeline', [MaintenanceTaskController::class, 'timeline']);

Route::get('/api/maintenance-tasks/{task}/purchase-requisitions', [MaintenanceTaskController::class, 'purchaseRequisitions']);
Route::post('/api/maintenance-tasks/{task}/purchase-requisitions', [MaintenanceTaskController::class, 'storePurchaseRequisition']);

Route::get('/api/maintenance-purchase-requisitions/generate-number', [MaintenanceTaskController::class, 'generatePrNumber']);

Route::get('/api/units', [MaintenanceTaskController::class, 'getUnits']);

Route::delete('/api/maintenance-purchase-requisitions/{id}', [MaintenanceTaskController::class, 'deletePurchaseRequisition']);

// PR Approval Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/maintenance-purchase-requisitions/{id}/approve', [MaintenancePurchaseRequisitionController::class, 'approve']);
    Route::get('/api/maintenance-purchase-requisitions/{id}/approval-status', [MaintenancePurchaseRequisitionController::class, 'getApprovalStatus']);
});

Route::get('/maintenance-ba/{id}/preview', [MaintenanceBAController::class, 'preview'])
    ->middleware(['auth'])
    ->name('maintenance.ba.preview');

Route::get('/maintenance-pr/{id}/preview', [MaintenancePurchaseRequisitionController::class, 'preview'])
    ->middleware(['auth'])
    ->name('maintenance.pr.preview');

Route::get('/maintenance-po/{id}/preview', [MaintenancePurchaseOrderController::class, 'preview'])
    ->middleware(['auth'])
    ->name('maintenance.po.preview');

Route::post('/api/maintenance-tasks/{task}/bidding-items-pdf', [\App\Http\Controllers\MaintenanceTaskController::class, 'exportBiddingItemsPdf']);

Route::post('/api/maintenance-tasks/bidding-offers', [\App\Http\Controllers\MaintenanceTaskController::class, 'storeBiddingOffer']);

Route::get('/api/maintenance-tasks/bidding-offers', [\App\Http\Controllers\MaintenanceTaskController::class, 'getBiddingOffers']);

Route::post('/api/maintenance-tasks/create-po-from-bidding', [\App\Http\Controllers\MaintenanceTaskController::class, 'createPOFromBidding']);

Route::get('/api/maintenance-tasks/bidding-history', [\App\Http\Controllers\MaintenanceTaskController::class, 'getBiddingHistory']);

// MT PO Payment Routes
Route::middleware(['auth', 'verified'])->prefix('mt-po-payment')->name('mt-po-payment.')->group(function () {
    Route::get('/', [MTPOPaymentController::class, 'index'])->name('index');
    Route::get('/history', [MTPOPaymentController::class, 'history'])->name('history');
    // Route lain jika perlu
});

Route::get('/maintenance-schedule', [MaintenanceTaskController::class, 'schedule']);

Route::get('/maintenance-order/schedule-calendar', fn() => inertia('MaintenanceOrder/ScheduleCalendar'));

Route::middleware(['auth'])->group(function () {
    Route::get('/announcement', [AnnouncementController::class, 'index'])->name('announcement.index');
    Route::get('/announcement/create', [AnnouncementController::class, 'create'])->name('announcement.create');
    Route::post('/announcement', [AnnouncementController::class, 'store'])->name('announcement.store');
    Route::get('/announcement/{id}', [AnnouncementController::class, 'show'])->name('announcement.show');
    Route::get('/announcement/{id}/edit', [AnnouncementController::class, 'edit'])->name('announcement.edit');
    Route::put('/announcement/{id}', [AnnouncementController::class, 'update'])->name('announcement.update');
    Route::delete('/announcement/{id}', [AnnouncementController::class, 'destroy'])->name('announcement.destroy');
    Route::resource('categories', CategoryController::class);
    Route::put('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'update']);
    Route::patch('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'update']);
    Route::post('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Sub Category resource route
    Route::resource('sub-categories', SubCategoryController::class);
    Route::put('/sub-categories/{id}', [SubCategoryController::class, 'update']);
    Route::patch('/sub-categories/{id}', [SubCategoryController::class, 'update']);
    Route::post('/sub-categories/{id}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
    Route::patch('/sub-categories/{id}/toggle-status', [SubCategoryController::class, 'toggleStatus'])->name('sub-categories.toggle-status');

    Route::resource('units', UnitController::class);
    Route::put('/units/{id}', [UnitController::class, 'update']);
    Route::patch('/units/{id}', [UnitController::class, 'update']);
    Route::post('/units/{id}', [UnitController::class, 'update'])->name('units.update');
    Route::patch('/units/{id}/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');

    // Region routes
    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::put('/regions/{id}', [RegionController::class, 'update'])->name('regions.update');
    Route::post('/regions/{id}', [RegionController::class, 'update'])->name('regions.update');
    Route::delete('/regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');
    Route::patch('/regions/{region}/toggle-status', [RegionController::class, 'toggleStatus'])->name('regions.toggle-status');

    // Warehouse routes
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::put('/warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::post('/warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    Route::patch('/warehouses/{id}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');

    // Outlet routes
    Route::get('/outlets', [OutletController::class, 'index'])->name('outlets.index');
    Route::post('/outlets', [OutletController::class, 'store'])->name('outlets.store');
    Route::put('/outlets/{id}', [OutletController::class, 'update'])->name('outlets.update');
    Route::post('/outlets/{id}', [OutletController::class, 'update'])->name('outlets.update');
    Route::delete('/outlets/{id}', [OutletController::class, 'destroy'])->name('outlets.destroy');
    Route::patch('/outlets/{id}/toggle-status', [OutletController::class, 'toggleStatus'])->name('outlets.toggle-status');
    Route::get('/outlets/{id}/download-qr', [OutletController::class, 'downloadQr'])->name('outlets.download-qr');

    // Customer routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::patch('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

    // Warehouse Division routes
    Route::get('/warehouse-divisions', [WarehouseDivisionController::class, 'index'])->name('warehouse-divisions.index');
    Route::post('/warehouse-divisions', [WarehouseDivisionController::class, 'store'])->name('warehouse-divisions.store');
    Route::put('/warehouse-divisions/{id}', [WarehouseDivisionController::class, 'update'])->name('warehouse-divisions.update');
    Route::post('/warehouse-divisions/{id}', [WarehouseDivisionController::class, 'update'])->name('warehouse-divisions.update');
    Route::delete('/warehouse-divisions/{id}', [WarehouseDivisionController::class, 'destroy'])->name('warehouse-divisions.destroy');
    Route::patch('/warehouse-divisions/{id}/toggle-status', [WarehouseDivisionController::class, 'toggleStatus'])->name('warehouse-divisions.toggle-status');

    // Menu Type routes
    Route::get('/menu-types', [MenuTypeController::class, 'index'])->name('menu-types.index');
    Route::post('/menu-types', [MenuTypeController::class, 'store'])->name('menu-types.store');
    Route::put('/menu-types/{id}', [MenuTypeController::class, 'update'])->name('menu-types.update');
    Route::post('/menu-types/{id}', [MenuTypeController::class, 'update'])->name('menu-types.update');
    Route::delete('/menu-types/{id}', [MenuTypeController::class, 'destroy'])->name('menu-types.destroy');
    Route::patch('/menu-types/{id}/toggle-status', [MenuTypeController::class, 'toggleStatus'])->name('menu-types.toggle-status');

    Route::get('/dashboard-outlet', [OutletMapDashboardController::class, 'index'])->name('dashboard.outlet');
    Route::get('/api/outlets/active', [OutletMapDashboardController::class, 'activeOutlets'])->name('api.outlets.active');
});

Route::get('/items/import-template', [ItemController::class, 'downloadImportTemplate'])->name('items.import.template');
Route::post('/items/import-preview', [ItemController::class, 'importPreview'])->name('items.import.preview');
Route::post('/items/import-excel', [ItemController::class, 'importExcel'])->name('items.import.excel');
Route::get('/items/export/excel', [ItemController::class, 'exportExcel'])->name('items.export.excel');
Route::get('/items/export/pdf', [ItemController::class, 'exportPdf'])->name('items.export.pdf');
Route::post('/items/{id}/toggle-status', [ItemController::class, 'toggleStatus'])->name('items.toggleStatus');
Route::get('/api/items/search-for-pr', [ItemController::class, 'searchForPr']);

Route::resource('items', ItemController::class);
Route::resource('modifiers', ModifierController::class);
Route::resource('modifier-options', ModifierOptionController::class);
Route::resource('pr-foods', PrFoodController::class);
Route::post('pr-foods/{id}/approve-ssd-manager', [PrFoodController::class, 'approveSsdManager'])->name('pr-foods.approve-ssd-manager');
Route::post('pr-foods/{id}/approve-vice-coo', [PrFoodController::class, 'approveViceCoo'])->name('pr-foods.approve-vice-coo');

require __DIR__.'/auth.php';
