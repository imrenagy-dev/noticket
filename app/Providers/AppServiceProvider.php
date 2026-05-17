<?php

namespace App\Providers;

use App\Contracts\CaptchaContract;
use App\Contracts\TeamSlugGeneratorContract;
use App\Contracts\IssueHistoryContract;
use App\Contracts\ProjectPresenterContract;
use App\Http\Presenters\ProjectPresenter;
use App\Services\CaptchaService;
use App\Support\TeamSlugGenerator;
use App\Services\IssueHistoryService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CaptchaContract::class, CaptchaService::class);
        $this->app->bind(IssueHistoryContract::class, IssueHistoryService::class);
        $this->app->bind(ProjectPresenterContract::class, ProjectPresenter::class);
        $this->app->bind(TeamSlugGeneratorContract::class, TeamSlugGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
