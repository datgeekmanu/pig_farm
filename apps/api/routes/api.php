<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BandController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\BreedingController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\FarmSettingsController;
use App\Http\Controllers\Api\FarrowingController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\FormulaController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\HerdAnalyticsController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\MortalityController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PigController;
use App\Http\Controllers\Api\ReproductionController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockItemController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\WeaningController;
use App\Http\Controllers\Api\WeightController;
use App\Http\Resources\NutrientResource;
use App\Models\Nutrient;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [GoogleAuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::middleware(['auth:sanctum', 'farm'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/farms', [FarmController::class, 'index']);

    Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::post('/account/reset-farm', [AccountController::class, 'resetFarm'])->middleware('role:owner');
    Route::delete('/account', [AccountController::class, 'destroy'])->middleware('role:owner');

    Route::apiResource('batches', BatchController::class);
    Route::apiResource('pigs', PigController::class);

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
    Route::apiResource('treatments', TreatmentController::class);

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

    // PDF documents (branded)
    Route::get('sales/{sale}/receipt', [PdfController::class, 'saleReceipt']);
    Route::get('batches/{batch}/summary', [PdfController::class, 'batchSummary']);
    Route::get('formulas/{formula}/sheet', [PdfController::class, 'formulaSheet']);
    Route::get('finances/report', [PdfController::class, 'financeReport']);
    Route::get('reproduction/gtt-report', [PdfController::class, 'gttReport']);
});
