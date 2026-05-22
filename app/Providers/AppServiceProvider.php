<?php

namespace App\Providers;

use App\Http\Presenters\ProjectPresenter;
use App\Http\Presenters\ProjectPresenterInterface;
use App\Repositories\CommentRepository;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\IssueRepository;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\ProjectRepositoryInterface;
use App\Repositories\SprintRepository;
use App\Repositories\SprintRepositoryInterface;
use App\Services\CaptchaInterface;
use App\Services\CaptchaService;
use App\Services\IssueHistoryInterface;
use App\Services\IssueHistoryService;
use App\Support\TeamSlugGenerator;
use App\Support\TeamSlugGeneratorInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CaptchaInterface::class, CaptchaService::class);
        $this->app->bind(IssueHistoryInterface::class, IssueHistoryService::class);
        $this->app->bind(ProjectPresenterInterface::class, ProjectPresenter::class);
        $this->app->bind(TeamSlugGeneratorInterface::class, TeamSlugGenerator::class);

        $this->app->bind(IssueRepositoryInterface::class, IssueRepository::class);
        $this->app->bind(SprintRepositoryInterface::class, SprintRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
    }

    public function boot(): void
    {
        $this->configureDefaults();
    }

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
