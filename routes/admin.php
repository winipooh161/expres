<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Маршруты Администратора
|--------------------------------------------------------------------------
|
| Здесь определены все маршруты для административной панели.
| Все маршруты загружаются через RouteServiceProvider.
|
*/

// Группа маршрутов админки с проверкой роли admin
Route::middleware(['auth', 'status:admin'])->group(function () {
    // Дашборд
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::get('/analytics/data', [AdminController::class, 'getAnalyticsData'])->name('admin.analytics.data');
    Route::get('/dashboard-data', [AdminController::class, 'getDashboardData'])->name('admin.dashboard-data');
    
    // Управление пользователями
    Route::get('/users', [AdminController::class, 'user_admin'])->name('admin.users');
    Route::put('/users/{id}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/users/{id}/briefs', [AdminController::class, 'userBriefs'])->name('user.briefs');
    
    // Управление брифами - группировка для лучшей организации
    Route::prefix('brief')->name('admin.brief.')->group(function() {
        // Общие брифы
        Route::get('/common/{id}/edit', [AdminController::class, 'editCommonBrief'])->name('editCommon');
        Route::post('/common/{id}/update', [AdminController::class, 'updateCommonBrief'])->name('updateCommon');
        Route::delete('/common/{id}', [AdminController::class, 'destroyCommonBrief'])->name('common.destroy');
        
        // Коммерческие брифы
        Route::get('/commercial/{id}/edit', [AdminController::class, 'editCommercialBrief'])->name('editCommercial');
        Route::put('/commercial/{id}', [AdminController::class, 'updateCommercialBrief'])->name('updateCommercial');
        Route::delete('/commercial/{id}', [AdminController::class, 'destroyCommercialBrief'])->name('commercial.destroy');
    });
    
    // Маршрут для просмотра формы редактирования коммерческого брифа
    Route::get('/brief/commercial/edit/{id}', [App\Http\Controllers\AdminController::class, 'editCommercialBrief'])->name('admin.brief.editCommercial');

    // Маршрут для обновления коммерческого брифа
    Route::put('/brief/commercial/update/{id}', [App\Http\Controllers\AdminController::class, 'updateCommercialBrief'])->name('admin.brief.updateCommercial');
    
    // Добавляем явный маршрут для индекса брифов (если он понадобится в будущем)
    Route::get('/briefs', function() {
        // В будущем здесь можно будет реализовать отдельную страницу для всех брифов
        return redirect()->route('admin.dashboard');
    })->name('briefs.index');
    
    // Управление сделками
    Route::get('/deals', [AdminController::class, 'dealsList'])->name('admin.deals');
    Route::get('/deals/stats', [AdminController::class, 'dealsStats'])->name('admin.deals.stats');
    
    // Управление сметами
    Route::get('/estimates', [AdminController::class, 'estimatesList'])->name('admin.estimates');
    
    // Настройки системы
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    
  
});
