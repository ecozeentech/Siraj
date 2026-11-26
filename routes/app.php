<?php

use App\Http\Controllers\App\AboutController;
use App\Http\Controllers\App\AcademyController;
use App\Http\Controllers\App\AiController;
use App\Http\Controllers\App\CommunityController;
use App\Http\Controllers\App\DirectoryController;
use App\Http\Controllers\App\EventController;
use App\Http\Controllers\App\FamilyController;
use App\Http\Controllers\App\GroupController;
use App\Http\Controllers\App\HajjController;
use App\Http\Controllers\App\HomeController;
use App\Http\Controllers\App\KidsController;
use App\Http\Controllers\App\MessageController;
use App\Http\Controllers\App\MoreController;
use App\Http\Controllers\App\NotificationController;
use App\Http\Controllers\App\OnboardingController;
use App\Http\Controllers\App\PaymentController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\PushController;
use App\Http\Controllers\App\QuranAudioController;
use App\Http\Controllers\App\QuranController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\App\SubscriptionController;
use App\Http\Controllers\App\SupportController;
use App\Http\Controllers\App\SyncController;
use App\Http\Controllers\App\WalletController;
use App\Http\Controllers\App\WorshipController;
use App\Http\Controllers\App\ZakatController;
use App\Http\Controllers\Public\CertificateController;
use Illuminate\Support\Facades\Route;

// Public, same-origin audio proxy (the audio CDN sends no CORS headers) and public certificate verification.
Route::get('/quran-audio/{reciter}/{ayah}', QuranAudioController::class)->middleware('throttle:audio')->whereNumber('ayah')->name('quran.audio');
Route::get('/certificates/{code}', CertificateController::class)->name('certificate.verify');

Route::middleware(['auth', 'active'])->prefix('app')->name('app.')->group(function () {
    // Reachable before onboarding, and by kids mode.
    Route::post('/sync', SyncController::class)->middleware('throttle:sync')->name('sync');

    Route::middleware(['verified.required', 'kids.mode'])->group(function () {
        Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::post('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    });

    Route::middleware(['verified.required', 'onboarded', 'kids.mode'])->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/more', MoreController::class)->name('more');
        Route::get('/about', AboutController::class)->name('about');

        // Notifications & push
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/push/subscribe', [PushController::class, 'store'])->name('push.subscribe');
        Route::post('/push/unsubscribe', [PushController::class, 'destroy'])->name('push.unsubscribe');

        // Profile & settings
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/dashboard', [ProfileController::class, 'dashboard'])->name('profile.dashboard');
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/account', [SettingsController::class, 'account'])->name('account');
            Route::post('/account', [SettingsController::class, 'updateAccount'])->name('account.update');
            Route::get('/password', [SettingsController::class, 'password'])->name('password');
            Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
            Route::get('/security', [SettingsController::class, 'security'])->name('security');
            Route::post('/security/sessions', [SettingsController::class, 'endSessions'])->name('security.sessions');
            Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
            Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
            Route::get('/appearance', [SettingsController::class, 'appearance'])->name('appearance');
            Route::post('/appearance', [SettingsController::class, 'updateAppearance'])->name('appearance.update');
            Route::get('/quran', [SettingsController::class, 'quran'])->name('quran');
            Route::post('/quran', [SettingsController::class, 'updateQuran'])->name('quran.update');
            Route::get('/privacy', [SettingsController::class, 'privacy'])->name('privacy');
            Route::post('/privacy', [SettingsController::class, 'updatePrivacy'])->name('privacy.update');
            Route::get('/export', [SettingsController::class, 'export'])->name('export');
            Route::get('/delete', [SettingsController::class, 'delete'])->name('delete');
            Route::post('/delete', [SettingsController::class, 'destroy'])->name('delete.destroy');
        });
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [SupportController::class, 'index'])->name('index');
            Route::post('/', [SupportController::class, 'store'])->name('store');
            Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
        });

        // Quran
        Route::prefix('quran')->name('quran.')->group(function () {
            Route::get('/', [QuranController::class, 'index'])->name('index');
            Route::get('/surah/{surah}', [QuranController::class, 'surah'])->name('surah');
            Route::get('/juz/{juz}', [QuranController::class, 'juz'])->name('juz');
            Route::get('/data/{surah}', [QuranController::class, 'data'])->name('data');
            Route::get('/search', [QuranController::class, 'search'])->name('search');
            Route::get('/bookmarks', [QuranController::class, 'bookmarks'])->name('bookmarks');
            Route::delete('/notes/{note}', [QuranController::class, 'destroyNote'])->name('notes.destroy');
            Route::get('/goals', [QuranController::class, 'goals'])->name('goals');
            Route::post('/goals', [QuranController::class, 'saveGoal'])->name('goals.save');
            Route::delete('/goals', [QuranController::class, 'clearGoal'])->name('goals.clear');
            Route::get('/memorisation', [QuranController::class, 'memorisation'])->name('memorisation');
            Route::post('/prefs', [QuranController::class, 'prefs'])->name('prefs');
            Route::get('/offline', [QuranController::class, 'offline'])->name('offline');
        });

        // Worship
        Route::prefix('worship')->name('worship.')->group(function () {
            Route::get('/', [WorshipController::class, 'index'])->name('index');
            Route::get('/prayer-times', [WorshipController::class, 'prayerTimes'])->name('prayer-times');
            Route::get('/timetable', [WorshipController::class, 'timetable'])->name('timetable');
            Route::get('/settings', [WorshipController::class, 'settings'])->name('settings');
            Route::post('/settings', [WorshipController::class, 'saveSettings'])->name('settings.save');
            Route::get('/qibla', [WorshipController::class, 'qibla'])->name('qibla');
            Route::post('/qibla', [WorshipController::class, 'saveQibla'])->name('qibla.save');
            Route::get('/tracker', [WorshipController::class, 'tracker'])->name('tracker');
            Route::get('/adhkar', [WorshipController::class, 'adhkar'])->name('adhkar');
            Route::get('/adhkar/favorites', [WorshipController::class, 'favorites'])->name('adhkar.favorites');
            Route::post('/adhkar/entries/{entry}/favorite', [WorshipController::class, 'toggleFavorite'])->name('adhkar.favorite');
            Route::get('/adhkar/{category:slug}', [WorshipController::class, 'adhkarShow'])->name('adhkar.show');
            Route::get('/tasbih', [WorshipController::class, 'tasbih'])->name('tasbih');
            Route::get('/tajweed', [WorshipController::class, 'tajweed'])->name('tajweed');
            Route::get('/tajweed/{resource:slug}', [WorshipController::class, 'tajweedShow'])->name('tajweed.show');
        });
        Route::prefix('zakat')->name('zakat.')->group(function () {
            Route::get('/', [ZakatController::class, 'index'])->name('index');
            Route::post('/', [ZakatController::class, 'store'])->name('store');
            Route::delete('/{calculation}', [ZakatController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('hajj-umrah')->name('hajj.')->group(function () {
            Route::get('/', [HajjController::class, 'index'])->name('index');
            Route::post('/checklist/{item}', [HajjController::class, 'toggleItem'])->name('item');
            Route::get('/{guide:slug}', [HajjController::class, 'show'])->name('show');
            Route::post('/{guide:slug}/save', [HajjController::class, 'toggleSave'])->name('save');
        });

        // Academy
        Route::prefix('academy')->name('academy.')->group(function () {
            Route::get('/', [AcademyController::class, 'index'])->name('index');
            Route::get('/certificate/{code}', [AcademyController::class, 'certificate'])->name('certificate');
            Route::get('/quiz/{quiz}', [AcademyController::class, 'quiz'])->name('quiz');
            Route::post('/quiz/{quiz}', [AcademyController::class, 'submitQuiz'])->name('quiz.submit');
            Route::get('/attempts/{attempt}', [AcademyController::class, 'result'])->name('quiz.result');
            Route::post('/lessons/{lesson}/position', [AcademyController::class, 'position'])->name('lesson.position');
            Route::get('/{course:slug}', [AcademyController::class, 'course'])->name('course');
            Route::post('/{course:slug}/enrol', [AcademyController::class, 'enrol'])->name('enrol');
            Route::get('/{course:slug}/lessons/{lesson}', [AcademyController::class, 'lesson'])->name('lesson');
            Route::post('/{course:slug}/lessons/{lesson}/complete', [AcademyController::class, 'complete'])->name('lesson.complete');
        });

        // Family (parent-only; deliberately NOT under app.kids.* so a child session can never reach it)
        Route::prefix('family')->name('family.')->group(function () {
            Route::get('/', [FamilyController::class, 'index'])->name('index');
            Route::post('/children', [FamilyController::class, 'store'])->name('children.store');
            Route::put('/children/{child}', [FamilyController::class, 'update'])->name('children.update');
            Route::delete('/children/{child}', [FamilyController::class, 'destroy'])->name('children.destroy');
            Route::post('/children/{child}/enter', [FamilyController::class, 'enter'])->name('enter');
        });

        // AI
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/', [AiController::class, 'index'])->name('index');
            Route::get('/{conversation}', [AiController::class, 'show'])->whereNumber('conversation')->name('show');
            Route::delete('/{conversation}', [AiController::class, 'destroy'])->whereNumber('conversation')->name('destroy');
        });

        // Community
        Route::prefix('community')->name('community.')->group(function () {
            Route::get('/', [CommunityController::class, 'index'])->name('index');
            Route::post('/posts', [CommunityController::class, 'store'])->middleware('throttle:posting')->name('posts.store');
            Route::get('/posts/{post}', [CommunityController::class, 'show'])->name('posts.show');
            Route::delete('/posts/{post}', [CommunityController::class, 'destroy'])->name('posts.destroy');
            Route::post('/posts/{post}/comments', [CommunityController::class, 'comment'])->middleware('throttle:posting')->name('comments.store');
            Route::delete('/comments/{comment}', [CommunityController::class, 'destroyComment'])->name('comments.destroy');
            Route::post('/react/{type}/{id}', [CommunityController::class, 'react'])->whereIn('type', ['post', 'comment'])->whereNumber('id')->name('react');
            Route::post('/posts/{post}/share', [CommunityController::class, 'share'])->name('posts.share');
            Route::post('/report/{type}/{id}', [CommunityController::class, 'report'])->whereIn('type', ['post', 'comment', 'user'])->whereNumber('id')->name('report');
            Route::get('/people', [CommunityController::class, 'people'])->name('people');
            Route::get('/people/{user}', [CommunityController::class, 'profile'])->whereNumber('user')->name('profile');
            Route::post('/people/{user}/follow', [CommunityController::class, 'follow'])->whereNumber('user')->name('follow');
            Route::post('/people/{user}/block', [CommunityController::class, 'block'])->whereNumber('user')->name('block.store');
            Route::delete('/people/{user}/block', [CommunityController::class, 'unblock'])->whereNumber('user')->name('block');
            Route::post('/appeals', [CommunityController::class, 'appeal'])->name('appeal');
            Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
            Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
            Route::get('/groups/{group:slug}', [GroupController::class, 'show'])->name('groups.show');
            Route::post('/groups/{group:slug}/join', [GroupController::class, 'join'])->name('groups.join');
            Route::delete('/groups/{group:slug}/join', [GroupController::class, 'leave'])->name('groups.leave');
            Route::post('/groups/{group:slug}/members/{member}/approve', [GroupController::class, 'approve'])->name('groups.approve');
        });

        // Messages
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::post('/', [MessageController::class, 'start'])->middleware('throttle:posting')->name('start');
            Route::get('/{conversation}', [MessageController::class, 'show'])->whereNumber('conversation')->name('show');
            Route::post('/{conversation}', [MessageController::class, 'send'])->middleware('throttle:posting')->whereNumber('conversation')->name('send');
            Route::post('/{conversation}/respond', [MessageController::class, 'respond'])->whereNumber('conversation')->name('respond');
            Route::post('/{conversation}/mute', [MessageController::class, 'mute'])->whereNumber('conversation')->name('mute');
            Route::get('/attachments/{attachment}', [MessageController::class, 'attachment'])->whereNumber('attachment')->name('attachment');
            Route::post('/m/{message}/report', [MessageController::class, 'report'])->whereNumber('message')->name('report');
        });

        // Events
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('index');
            Route::get('/mine', [EventController::class, 'mine'])->name('mine');
            Route::get('/{event:slug}', [EventController::class, 'show'])->name('show');
            Route::post('/{event:slug}/register', [EventController::class, 'register'])->name('register');
            Route::delete('/{event:slug}/register', [EventController::class, 'cancel'])->name('cancel');
        });

        // Directory
        Route::prefix('directory')->name('directory.')->group(function () {
            Route::get('/', [DirectoryController::class, 'index'])->name('index');
            Route::get('/submit', [DirectoryController::class, 'create'])->name('submit');
            Route::post('/submit', [DirectoryController::class, 'store'])->middleware('throttle:posting')->name('submit.store');
            Route::get('/mosques/{mosque:slug}', [DirectoryController::class, 'mosque'])->name('mosque');
            Route::get('/businesses/{business:slug}', [DirectoryController::class, 'business'])->name('business');
            Route::post('/businesses/{business:slug}/reviews', [DirectoryController::class, 'review'])->middleware('throttle:posting')->name('review');
            Route::post('/report/{type}/{id}', [DirectoryController::class, 'report'])->whereIn('type', ['mosque', 'business'])->whereNumber('id')->name('report');
        });

        // Wallet, subscription, payments
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/top-up', [WalletController::class, 'topUp'])->name('wallet.topup');
        Route::get('/wallet/receipt/{transaction}', [WalletController::class, 'receipt'])->name('wallet.receipt');
        Route::post('/wallet/refunds/{transaction}', [WalletController::class, 'requestRefund'])->name('wallet.refund');
        Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('/subscription/{plan:slug}', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::delete('/subscription', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');

        // Kids Deen World
        Route::prefix('kids')->name('kids.')->group(function () {
            Route::get('/', [KidsController::class, 'index'])->name('index');
            Route::get('/home', [KidsController::class, 'home'])->name('home');
            Route::post('/heartbeat', [KidsController::class, 'heartbeat'])->name('heartbeat');
            Route::get('/limit', [KidsController::class, 'limit'])->name('limit');
            Route::post('/exit', [KidsController::class, 'exit'])->name('exit');
            Route::get('/courses/{course:slug}', [KidsController::class, 'course'])->name('course');
            Route::get('/courses/{course:slug}/lessons/{lesson}', [KidsController::class, 'lesson'])->name('lesson');
            Route::post('/courses/{course:slug}/lessons/{lesson}/complete', [KidsController::class, 'completeLesson'])->name('lesson.complete');
            Route::get('/stories/{story:slug}', [KidsController::class, 'story'])->name('story');
            Route::get('/games/{game:slug}', [KidsController::class, 'game'])->name('game');
            Route::post('/games/{game:slug}/complete', [KidsController::class, 'completeGame'])->name('game.complete');
        });
    });
});
