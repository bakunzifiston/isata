<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BeepCallController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\RsvpDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SocialPostController;
use App\Http\Controllers\QueueMonitorController;
use App\Http\Controllers\Auth\RegisterOrganizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('rsvp/thank-you', [RsvpController::class, 'thankYou'])->name('rsvp.thank-you');
Route::get('events/{event}/rsvp', [RsvpController::class, 'eventLanding'])->name('events.rsvp');
Route::get('rsvp/lookup', [RsvpController::class, 'lookup'])->name('rsvp.lookup');
Route::get('rsvp/{event}/{attendee}', [RsvpController::class, 'show'])->name('rsvp.show')->middleware('signed');
Route::post('rsvp/{event}/{attendee}/respond', [RsvpController::class, 'store'])->name('rsvp.store')->middleware('signed');
Route::post('webhook/sms/rsvp', [RsvpController::class, 'smsWebhook'])->name('webhook.sms.rsvp');

Route::get('feedback/thank-you', [FeedbackController::class, 'thankYou'])->name('feedback.thank-you');
Route::get('feedback/{event}/{attendee}', [FeedbackController::class, 'show'])->name('feedback.show')->middleware('signed');
Route::post('feedback/{event}/{attendee}', [FeedbackController::class, 'store'])->name('feedback.store')->middleware('signed');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('register', [RegisterOrganizationController::class, 'create'])->name('register');
    Route::post('register', [RegisterOrganizationController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::middleware('system_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('organizations', [\App\Http\Controllers\SuperAdminController::class, 'organizations'])->name('organizations');
        Route::post('organizations/{organization}/toggle', [\App\Http\Controllers\SuperAdminController::class, 'toggleOrganization'])->name('organizations.toggle');
        Route::post('organizations/{organization}/assign-plan', [\App\Http\Controllers\SuperAdminController::class, 'assignPlan'])->name('organizations.assign-plan');
        Route::get('events', [\App\Http\Controllers\SuperAdminController::class, 'events'])->name('events');
        Route::get('activity', [\App\Http\Controllers\SuperAdminController::class, 'activity'])->name('activity');
        Route::get('system-health', [\App\Http\Controllers\SuperAdminController::class, 'systemHealth'])->name('system-health');
        Route::get('users', [\App\Http\Controllers\SuperAdminController::class, 'users'])->name('users');
        Route::delete('users/{user}', [\App\Http\Controllers\SuperAdminController::class, 'destroyUser'])->name('users.destroy');
    });
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    Route::middleware('organization')->group(function () {
        Route::get('organization/profile', [OrganizationProfileController::class, 'edit'])->name('organization.profile.edit');
        Route::match(['put', 'post'], 'organization/profile', [OrganizationProfileController::class, 'update'])->name('organization.profile.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
        Route::get('subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
        Route::post('subscription/upgrade', [SubscriptionController::class, 'storeUpgrade'])->name('subscription.upgrade.store');

        Route::get('usage', [UsageController::class, 'index'])->name('usage.index');

        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::get('events/calendar', [EventController::class, 'calendar'])->name('events.calendar');
        Route::get('events/calendar/data', [EventController::class, 'calendarData'])->name('events.calendar.data');
        Route::get('events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('events', [EventController::class, 'store'])->name('events.store');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        Route::resource('events.messages', MessageController::class)->except(['show']);
        Route::post('events/{event}/messages/{message}/send-now', [MessageController::class, 'sendNow'])->name('events.messages.send-now');
        Route::resource('templates', MessageTemplateController::class)->except(['show']);
        Route::get('queue/monitor', [QueueMonitorController::class, 'index'])->name('queue.monitor');
        Route::get('rsvp/dashboard', [RsvpDashboardController::class, 'index'])->name('rsvp.dashboard');
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/events/{event}', [AnalyticsController::class, 'eventReport'])->name('analytics.event-report');

        Route::post('beep-calls/upload-audio', [BeepCallController::class, 'uploadAudio'])->name('beep-calls.upload-audio');
        Route::get('beep-calls', [BeepCallController::class, 'index'])->name('beep-calls.index');
        Route::get('beep-calls/create', [BeepCallController::class, 'create'])->name('beep-calls.create');
        Route::post('beep-calls', [BeepCallController::class, 'store'])->name('beep-calls.store');
        Route::delete('beep-calls/{beepCall}', [BeepCallController::class, 'destroy'])->name('beep-calls.destroy');
        Route::post('beep-calls/{beepCall}/call-now', [BeepCallController::class, 'callNow'])->name('beep-calls.call-now');

        Route::get('social', [SocialPostController::class, 'index'])->name('social.index');
        Route::get('social/accounts', [SocialAccountController::class, 'index'])->name('social.accounts');
        Route::post('social/accounts', [SocialAccountController::class, 'store'])->name('social.accounts.store');
        Route::delete('social/accounts/{account}', [SocialAccountController::class, 'destroy'])->name('social.accounts.destroy');
        Route::get('social/create', [SocialPostController::class, 'create'])->name('social.create');
        Route::post('social', [SocialPostController::class, 'store'])->name('social.store');
        Route::get('social/{post}/edit', [SocialPostController::class, 'edit'])->name('social.edit');
        Route::put('social/{post}', [SocialPostController::class, 'update'])->name('social.update');
        Route::delete('social/{post}', [SocialPostController::class, 'destroy'])->name('social.destroy');
        Route::post('social/{post}/publish-now', [SocialPostController::class, 'publishNow'])->name('social.publish-now');

        Route::get('surveys', [SurveyController::class, 'index'])->name('surveys.index');
        Route::get('surveys/create', [SurveyController::class, 'create'])->name('surveys.create');
        Route::post('surveys', [SurveyController::class, 'store'])->name('surveys.store');
        Route::get('surveys/{survey}/edit', [SurveyController::class, 'edit'])->name('surveys.edit');
        Route::put('surveys/{survey}', [SurveyController::class, 'update'])->name('surveys.update');
        Route::delete('surveys/{survey}', [SurveyController::class, 'destroy'])->name('surveys.destroy');
        Route::get('surveys/{survey}/responses', [SurveyController::class, 'responses'])->name('surveys.responses');
        Route::get('surveys/{survey}/report', [SurveyController::class, 'report'])->name('surveys.report');
        Route::get('certificates/{feedback}', [CertificateController::class, 'show'])->name('certificates.show');

        Route::get('events/{event}/attendees', [AttendeeController::class, 'index'])->name('events.attendees.index');
        Route::post('events/{event}/attendees', [AttendeeController::class, 'store'])->name('events.attendees.store');
        Route::put('events/{event}/attendees/{attendee}', [AttendeeController::class, 'update'])->name('events.attendees.update');
        Route::delete('events/{event}/attendees/{attendee}', [AttendeeController::class, 'destroy'])->name('events.attendees.destroy');
        Route::post('events/{event}/attendees/import/csv', [AttendeeController::class, 'importCsv'])->name('events.attendees.import.csv');
        Route::post('events/{event}/attendees/import/bulk', [AttendeeController::class, 'bulkImport'])->name('events.attendees.import.bulk');
    });
});
