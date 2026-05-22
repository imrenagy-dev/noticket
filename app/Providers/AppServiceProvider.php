<?php

namespace App\Providers;

use App\Http\Presenters\ProjectPresenter;
use App\Http\Presenters\ProjectPresenterInterface;
use App\Repositories\CommentRepository;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\DashboardRepository;
use App\Repositories\DashboardRepositoryInterface;
use App\Repositories\IssueHistoryRepository;
use App\Repositories\IssueHistoryRepositoryInterface;
use App\Repositories\IssueRepository;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\ProjectRepositoryInterface;
use App\Repositories\SprintRepository;
use App\Repositories\SprintRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\CaptchaServiceInterface;
use App\Services\CaptchaService;
use App\Services\CommentService;
use App\Services\CommentServiceInterface;
use App\Services\DashboardService;
use App\Services\DashboardServiceInterface;
use App\Services\IssueHistoryServiceInterface;
use App\Services\IssueHistoryService;
use App\Services\IssueService;
use App\Services\IssueServiceInterface;
use App\Services\ProjectService;
use App\Services\ProjectServiceInterface;
use App\Services\SprintService;
use App\Services\SprintServiceInterface;
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
        $this->app->bind(CaptchaServiceInterface::class, CaptchaService::class);
        $this->app->bind(IssueHistoryServiceInterface::class, IssueHistoryService::class);
        $this->app->bind(IssueServiceInterface::class, IssueService::class);
        $this->app->bind(SprintServiceInterface::class, SprintService::class);
        $this->app->bind(ProjectServiceInterface::class, ProjectService::class);
        $this->app->bind(CommentServiceInterface::class, CommentService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(ProjectPresenterInterface::class, ProjectPresenter::class);
        $this->app->bind(TeamSlugGeneratorInterface::class, TeamSlugGenerator::class);

        $this->app->bind(IssueRepositoryInterface::class, IssueRepository::class);
        $this->app->bind(SprintRepositoryInterface::class, SprintRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
        $this->app->bind(IssueHistoryRepositoryInterface::class, IssueHistoryRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
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
