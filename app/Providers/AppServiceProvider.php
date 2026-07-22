<?php

namespace App\Providers;

use App\Models\Attendee;
use App\Models\BeepCall;
use App\Models\EmailSenderIdentity;
use App\Models\Event;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Organization;
use App\Models\SocialPost;
use App\Models\Survey;
use App\Models\User;
use App\Models\Contact;
use App\Policies\AttendeePolicy;
use App\Policies\BeepCallPolicy;
use App\Policies\ContactPolicy;
use App\Policies\EmailSenderIdentityPolicy;
use App\Policies\EventPolicy;
use App\Policies\MessagePolicy;
use App\Policies\MessageTemplatePolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\SocialPostPolicy;
use App\Policies\SurveyPolicy;
use App\Policies\UserPolicy;
use App\Services\ConnectionService;
use App\Services\ContactService;
use App\Services\UsageLimitService;
use App\View\Composers\DashboardComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConnectionService::class, fn () => new ConnectionService);
        $this->app->singleton(ContactService::class, fn ($app) => new ContactService($app->make(UsageLimitService::class)));
        $this->app->singleton(UsageLimitService::class, fn () => new UsageLimitService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Attendee::class, AttendeePolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(MessageTemplate::class, MessageTemplatePolicy::class);
        Gate::policy(EmailSenderIdentity::class, EmailSenderIdentityPolicy::class);
        Gate::policy(BeepCall::class, BeepCallPolicy::class);
        Gate::policy(SocialPost::class, SocialPostPolicy::class);
        Gate::policy(Survey::class, SurveyPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);

        View::composer('layouts.dashboard', DashboardComposer::class);
    }
}
