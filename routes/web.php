<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BrifsController;
use App\Http\Controllers\DealFeedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\DealModalController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SmetsController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RatingController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

// Главная страница
Route::get('/', function () {
    return redirect('login/password');
});


Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/reply/{ticket}', [SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/create', [SupportController::class, 'create'])->name('support.create');
    
    // Убрать дублирование - оставляем один маршрут с именем profile.index
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/view/{id}', [ProfileController::class, 'viewProfile'])->name('profile.view');
    Route::post('/profile/avatar/update', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('/profile/send-code', [ProfileController::class, 'sendVerificationCode'])->name('profile.send-code');
    Route::post('/profile/verify-code', [ProfileController::class, 'verifyCode'])->name('profile.verify-code');
    Route::post('/delete-account', [ProfileController::class, 'deleteAccount'])->name('delete_account');
    Route::post('/profile/update-all', [ProfileController::class, 'updateProfileAll'])->name('profile.update_all');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    
    Route::get('/brifs', [BrifsController::class, 'index'])->name('brifs.index');
    Route::post('/brifs/store', [BrifsController::class, 'store'])->name('brifs.store');
    Route::delete('/brifs/{brif}', [BrifsController::class, 'destroy'])->name('brifs.destroy');
    
    Route::get('/common/questions/{id}/{page}', [CommonController::class, 'questions'])->name('common.questions');
    Route::post('/common/questions/{id}/{page}', [CommonController::class, 'saveAnswers'])->name('common.saveAnswers');
    Route::get('/common/create', [BrifsController::class, 'common_create'])->name('common.create');
    Route::post('/common', [BrifsController::class, 'common_store'])->name('common.store');
    Route::get('/common/{id}', [BrifsController::class, 'common_show'])->name('common.show');
    Route::get('/common/{id}/download-pdf', [BrifsController::class, 'common_download_pdf'])->name('common.download.pdf');
    
    Route::get('/commercial/questions/{id}/{page}', [CommercialController::class, 'questions'])->name('commercial.questions');
    Route::post('/commercial/questions/{id}/{page}', [CommercialController::class, 'saveAnswers'])->name('commercial.saveAnswers');
    Route::get('/commercial/create', [BrifsController::class, 'commercial_create'])->name('commercial.create');
    Route::post('/commercial', [BrifsController::class, 'commercial_store'])->name('commercial.store');
    Route::get('/commercial/{id}', [BrifsController::class, 'commercial_show'])->name('commercial.show');
    Route::get('/commercial/{id}/download-pdf', [BrifsController::class, 'commercial_download_pdf'])->name('commercial.download.pdf');

    // Сделка для пользователя
    Route::get('/deal-user', [DealsController::class, 'dealUser'])->name('deal.user');

    // Маршрут для удаления файла из брифа
    Route::post('/common/{id}/delete-file', [CommonController::class, 'deleteFile'])->name('common.delete-file');
    
    // Маршрут для удаления файла из коммерческого брифа
    Route::post('/commercial/{id}/delete-file', [CommercialController::class, 'deleteFile'])->name('commercial.delete-file');
    
    // Маршрут для пропуска страницы в брифе
    Route::post('/common/{id}/skip/{page}', [CommonController::class, 'skipPage'])->name('common.skipPage');
});

// Маршруты для рейтингов исполнителей - убираем дублирование
Route::prefix('ratings')->middleware(['auth'])->group(function () {
    Route::post('/store', [RatingController::class, 'store'])->name('ratings.store');
    Route::get('/check-pending', [RatingController::class, 'checkPendingRatings'])->name('ratings.check-pending');
    Route::get('/check-completed', [RatingController::class, 'checkAllRatingsComplete'])->name('ratings.check-completed');
});

// Маршрут для страницы рейтингов специалистов
Route::middleware(['auth'])->group(function () {
    Route::get('/ratings/specialists', 'App\Http\Controllers\RatingViewController@index')->name('ratings.specialists');
});

Route::middleware(['auth', 'status:partner'])->group(function () {
    Route::get('/estimate', [SmetsController::class, 'estimate'])->name('estimate');
    Route::get('/estimate/service', [SmetsController::class, 'allService'])->name('estimate.service');
    Route::get('/estimate/default', [SmetsController::class, 'defaultValueBD'])->name('estimate.default');
    Route::get('/estimate/create/{id?}', [SmetsController::class, 'createEstimate'])->name('estimate.create');
    Route::post('/estimate/createcoefs', [SmetsController::class, 'addCoefs'])->name('estimate.createcoefs');
    Route::post('/estimate/save/{id?}', [SmetsController::class, 'saveEstimate'])->name('estimate.save');
    Route::post('/estimate/pdf/{id?}', [SmetsController::class, 'savePdf'])->name('estimate.pdf');
    Route::post('/estimate/del/{id}', [SmetsController::class, 'delEstimate'])->name('estimate.del');
    Route::post('/estimate/chenge/{id}/{slot}/{value}/{type}/{stage}', [SmetsController::class, 'changeService'])->name('estimate.change');
    Route::get('/estimate/preview', [SmetsController::class, 'previewEstimate'])->name('estimate.preview');
    Route::get('/estimate/defaultServices', [SmetsController::class, 'defaultServices'])->name('estimate.defaultServices');
    Route::get('/estimate/copy/{id?}', [SmetsController::class, 'copyEstimate'])->name('estimate.copy');
    Route::get('/estimate/change-estimate/{id?}', [SmetsController::class, 'changeEstimate'])->name('estimate.changeEstimate');
});

Route::middleware(['auth', 'status:coordinator,admin,partner,visualizer,architect,designer'])->group(function () {
    Route::get('/deal-cardinator', [DealsController::class, 'dealCardinator'])->name('deal.cardinator');
    Route::get('/deal/{deal}/modal', [DealModalController::class, 'getDealModal'])->name('deal.modal');
});


Route::middleware(['auth', 'status:coordinator,admin,partner'])->group(function () {
    Route::get('/deals/create', [DealsController::class, 'createDeal'])->name('deals.create');
    Route::post('/deal/store', [DealsController::class, 'storeDeal'])->name('deals.store');
    Route::put('/deal/update/{id}', [DealsController::class, 'updateDeal'])->name('deal.update');
    Route::get('/deals/{deal}/edit', [DealsController::class, 'editDeal'])->name('deal.edit');
    Route::put('/deals/{deal}', [DealsController::class, 'updateDeal'])->name('deal.update');
});

Route::middleware(['auth', 'status:coordinator,admin'])->group(function () {
    Route::get('/deal/change-logs', [DealsController::class, 'changeLogs'])->name('deal.change_logs');
    Route::get('/deal/{deal}/change-logs', [DealsController::class, 'changeLogsForDeal'])->name('deal.change_logs.deal');
});

Route::post('/deal/{deal}/feed', [DealFeedController::class, 'store'])
    ->name('deal.feed.store');

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');

Route::prefix('admin')->group(base_path('routes/admin.php'));

Route::get('/register_by_deal/{token}', [AuthController::class, 'registerByDealLink'])->name('register_by_deal');
Route::post('/complete-registration-by-deal/{token}', [AuthController::class, 'completeRegistrationByDeal'])->name('auth.complete_registration_by_deal');
Route::get('', [AuthController::class, 'showLoginFormByPassword'])->name('login.password');
Route::post('login/password', [AuthController::class, 'loginByPassword'])->name('login.password.post');
Route::get('login/code', [AuthController::class, 'showLoginFormByCode'])->name('login.code');
Route::post('login/code', [AuthController::class, 'loginByCode'])->name('login.code.post');
Route::post('/send-code', [AuthController::class, 'sendCode'])->name('send.code');
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.post');
Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register/executor', [AuthController::class, 'showRegistrationFormForExecutors'])->name('register.executor');
Route::post('/register/executor', [AuthController::class, 'registerExecutor'])->name('register.executor.post');

if (app()->environment('production')) {
    URL::forceScheme('https');
}

// Если URL без id, перенаправляем на карточку сделок
Route::get('/deal/update', function () {
    return redirect()->route('deal.cardinator');
});

// Определяем маршрут для обновления сделки (POST), где {id} — идентификатор сделки
Route::post('/deal/update/{id}', [DealsController::class, 'updateDeal'])
    ->name('deal.update');

// Чаты - объединяем дублирующиеся маршруты
// Route::middleware(['auth', 'check.chat.access'])->group(function () {
//     Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
//     Route::get('/chat', [ChatController::class, 'index'])->name('chat');
// });

// // API для чатов
// Route::middleware(['auth', 'check.chat.access'])->prefix('api')->group(function () {
//     Route::get('/contacts', [ChatController::class, 'getContacts']);
//     Route::get('/chats/{id}/messages', [ChatController::class, 'getMessages']);
//     Route::get('/chats/{id}/new-messages', [ChatController::class, 'getNewMessages']);
//     Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
//     Route::get('/messages/unread-count', [ChatController::class, 'getUnreadCount']);
//     Route::get('/chat-groups', [ChatController::class, 'getChatGroups']);
//     Route::post('/chat-groups', [ChatController::class, 'createChatGroup']);
//     Route::get('/chat-groups/{id}', [ChatController::class, 'getChatGroup']);
//     Route::put('/chat-groups/{id}', [ChatController::class, 'updateChatGroup']);
//     Route::delete('/chat-groups/{id}', [ChatController::class, 'deleteChatGroup']);
//     Route::post('/chat-groups/{id}/users', [ChatController::class, 'addChatGroupUser']);
//     Route::delete('/chat-groups/{id}/users/{user_id}', [ChatController::class, 'removeChatGroupUser']);
//     Route::get('/chat-groups/{id}/messages', [ChatController::class, 'getGroupMessages']);
//     Route::post('/chat-groups/{id}/messages', [ChatController::class, 'sendGroupMessage']);
//     Route::get('/chat-groups/{id}/new-messages', [ChatController::class, 'getNewGroupMessages']);
//     Route::get('/messages/search', [ChatController::class, 'searchMessages']);
// });


// Маршрут для поиска пользователей через AJAX
Route::get('/search-users', function (Illuminate\Http\Request $request) {
    $query = $request->input('q');
    $role = $request->input('role');
    $status = $request->input('status');
    
    $users = \App\Models\User::where('name', 'like', '%' . $query . '%')
        ->when($role, function ($q) use ($role) {
            return $q->where('role', $role);
        })
        ->when($status, function ($q) use ($status) {
            if (is_array($status)) {
                return $q->whereIn('status', $status);
            } else {
                return $q->where('status', $status);
            }
        })
        ->limit(30)
        ->get(['id', 'name', 'email', 'status', 'avatar_url']);
    
    return response()->json($users);
})->middleware('auth')->name('search.users');

// Маршрут для получения данных пользователя по ID
Route::get('/get-user/{id}', function ($id) {
    $user = \App\Models\User::find($id, ['id', 'name', 'email', 'status', 'avatar_url']);
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    return response()->json($user);
})->middleware('auth')->name('get.user');

// Маршрут для проверки существования сделки
Route::get('/deal/{id}/exists', function ($id) {
    $exists = \App\Models\Deal::where('id', $id)->exists();
    return response()->json(['exists' => $exists]);
})->middleware('auth')->name('deal.exists');