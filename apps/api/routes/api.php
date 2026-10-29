<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BandController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\BreedingController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\FarmSettingsController;
use App\Http\Controllers\Api\FarmSummaryController;
use App\Http\Controllers\Api\FarrowingController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\FormulaController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\HerdAnalyticsController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\MortalityController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PigController;
use App\Http\Controllers\Api\ReproductionController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\Shop\BuyerAuthController;
use App\Http\Controllers\Api\Shop\BuyerOrderController;
use App\Http\Controllers\Api\Shop\CatalogController;
use App\Http\Controllers\Api\Shop\ListingController;
use App\Http\Controllers\Api\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Api\Shop\SellerOrderController;
use App\Http\Controllers\Api\SowController;
use App\Http\Controllers\Api\StockItemController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\WeaningController;
use App\Http\Controllers\Api\WeightController;
use App\Http\Resources\NutrientResource;
use App\Models\Nutrient;
use Illuminate\Support\Facades\Route;

// Rate-limited auth endpoints (brute-force protection).
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [GoogleAuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('shop/register', [BuyerAuthController::class, 'register']);
    Route::post('shop/login', [BuyerAuthController::class, 'login']);
});

// Public storefront catalog
Route::get('shop/products', [CatalogController::class, 'index']);
Route::get('shop/products/{product}', [CatalogController::class, 'show']);

Route::middleware('auth:buyer')->group(function () {
    Route::get('shop/me', [BuyerAuthController::class, 'me']);
    Route::post('shop/logout', [BuyerAuthController::class, 'logout']);
    Route::get('shop/orders', [BuyerOrderController::class, 'index']);
    Route::post('shop/orders', [BuyerOrderController::class, 'store']);
    Route::get('shop/orders/{order}', [BuyerOrderController::class, 'show']);
    Route::post('shop/orders/{order}/pay', [BuyerOrderController::class, 'pay']);
});

Route::middleware(['auth:sanctum', 'farm'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/farms', [FarmController::class, 'index']);

    Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::post('/account/reset-farm', [AccountController::class, 'resetFarm'])->middleware('role:owner');
    Route::delete('/account', [AccountController::class, 'destroy'])->middleware('role:owner');

    Route::apiResource('batches', BatchController::class);
    Route::apiResource('pigs', PigController::class);

    // Field-app aliases + lot-level operations (lots == batches)
    Route::get('user', [AuthController::class, 'me']); // alias of /me
    Route::apiResource('lots', BatchController::class)->parameters(['lots' => 'batch']);
    Route::get('lots/{batch}/weighings', [LotController::class, 'weighings']);
    Route::post('lots/{batch}/weighings', [LotController::class, 'storeWeighing']);
    Route::get('lots/{batch}/feed', [LotController::class, 'feedIntakes']);
    Route::post('lots/{batch}/feed', [LotController::class, 'storeFeed']);
    Route::get('lots/{batch}/events', [LotController::class, 'events']);

    // Sows projection (field app) + farm summary
    Route::get('sows', [SowController::class, 'index']);
    Route::get('sows/{pig}', [SowController::class, 'show']);
    Route::get('farm/summary', [FarmSummaryController::class, 'show']);

    // Weigh-ins + growth analytics
    Route::get('pigs/{pig}/weights', [WeightController::class, 'index']);
    Route::post('pigs/{pig}/weights', [WeightController::class, 'store']);
    Route::get('pigs/{pig}/growth-curve', [HerdAnalyticsController::class, 'pigGrowthCurve']);
    Route::get('batches/{batch}/weight-stats', [HerdAnalyticsController::class, 'batchWeightStats']);
    Route::get('batches/{batch}/growth-curve', [HerdAnalyticsController::class, 'batchGrowthCurve']);

    // Mortalité
    Route::get('mortalities', [MortalityController::class, 'index']);
    Route::post('mortalities', [MortalityController::class, 'store']);
    Route::get('mortalities/stats', [MortalityController::class, 'stats']);
    Route::delete('mortalities/{mortality}', [MortalityController::class, 'destroy']);

    // Bâtiments + densité
    Route::apiResource('buildings', BuildingController::class);
    Route::get('buildings/{building}/density', [BuildingController::class, 'density']);

    // Formulation
    Route::get('nutrients', fn () => NutrientResource::collection(Nutrient::all()));
    Route::apiResource('ingredients', IngredientController::class);
    Route::post('formulas/analyze', [FormulaController::class, 'analyze']);
    Route::post('formulas/optimize', [FormulaController::class, 'optimize']);
    Route::apiResource('formulas', FormulaController::class);

    // Stocks (alimentation + vétérinaire) + movements
    Route::apiResource('stock-items', StockItemController::class)->parameters(['stock-items' => 'stockItem']);
    Route::get('stock-items/{stockItem}/movements', [StockItemController::class, 'movements']);
    Route::post('stock-items/{stockItem}/movements', [StockItemController::class, 'storeMovement']);

    // Traitements (lot / individuel / prophylaxie)
    Route::get('treatments/upcoming', [TreatmentController::class, 'upcoming']);
    Route::apiResource('treatments', TreatmentController::class);

    // "À faire aujourd'hui" completion state
    Route::get('tasks/done', [TaskController::class, 'done']);
    Route::post('tasks/done', [TaskController::class, 'markDone']);

    // Reproduction
    Route::get('reproduction/empty-sows', [ReproductionController::class, 'emptySows']);
    Route::get('reproduction/gtt', [ReproductionController::class, 'gtt']);
    Route::get('pigs/{pig}/breeding-history', [ReproductionController::class, 'breedingHistory']);
    Route::apiResource('breedings', BreedingController::class);
    Route::apiResource('farrowings', FarrowingController::class);
    Route::apiResource('weanings', WeaningController::class);
    Route::apiResource('bands', BandController::class);

    // Finances
    Route::get('finances/overview', [FinanceController::class, 'overview']);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('salaries', SalaryController::class);

    // Farm settings
    Route::get('farm', [FarmSettingsController::class, 'show']);
    Route::put('farm', [FarmSettingsController::class, 'update'])->middleware('role:owner');
    Route::post('farm/logo', [FarmSettingsController::class, 'uploadLogo'])->middleware('role:owner');
    Route::get('farm/notifications', [FarmSettingsController::class, 'notifications']);
    Route::put('farm/notifications', [FarmSettingsController::class, 'updateNotifications'])->middleware('role:owner');

    // Team
    Route::get('farm/members', [TeamController::class, 'index']);
    Route::post('farm/members', [TeamController::class, 'store'])->middleware('role:owner');
    Route::delete('farm/members/{user}', [TeamController::class, 'destroy'])->middleware('role:owner');

    // Tutoriels
    Route::get('tutorials', fn () => response()->json(['data' => config('tutorials')]));

    // Storefront — seller side
    Route::get('listings', [ListingController::class, 'index']);
    Route::post('listings', [ListingController::class, 'store']);
    Route::match(['put', 'patch'], 'listings/{product}', [ListingController::class, 'update']);
    Route::delete('listings/{product}', [ListingController::class, 'destroy']);
    Route::apiResource('products', ShopProductController::class);
    Route::get('shop/seller/orders', [SellerOrderController::class, 'index']);
    Route::patch('shop/seller/orders/{order}/status', [SellerOrderController::class, 'updateStatus']);

    // Offline field sync + audit
    Route::post('sync', [SyncController::class, 'sync']);
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('role:owner');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'read']);

    // PDF documents (branded)
    Route::get('sales/{sale}/receipt', [PdfController::class, 'saleReceipt']);
    Route::get('batches/{batch}/summary', [PdfController::class, 'batchSummary']);
    Route::get('formulas/{formula}/sheet', [PdfController::class, 'formulaSheet']);
    Route::get('finances/report', [PdfController::class, 'financeReport']);
    Route::get('reproduction/gtt-report', [PdfController::class, 'gttReport']);
});
