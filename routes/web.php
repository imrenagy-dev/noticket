<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Issues\CommentController;
use App\Http\Controllers\Issues\IssueController;
use App\Http\Controllers\Projects\BacklogController;
use App\Http\Controllers\Projects\BoardController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Sprints\SprintController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Projects
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::patch('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        // Board & Backlog
        Route::get('projects/{project}/board', [BoardController::class, 'show'])->name('projects.board');
        Route::get('projects/{project}/backlog', [BacklogController::class, 'show'])->name('projects.backlog');

        // Issues
        Route::post('projects/{project}/issues', [IssueController::class, 'store'])->name('issues.store');
        Route::get('projects/{project}/issues/{issue}', [IssueController::class, 'show'])->name('issues.show');
        Route::patch('projects/{project}/issues/{issue}', [IssueController::class, 'update'])->name('issues.update');
        Route::delete('projects/{project}/issues/{issue}', [IssueController::class, 'destroy'])->name('issues.destroy');

        // Comments
        Route::post('projects/{project}/issues/{issue}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::patch('projects/{project}/issues/{issue}/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('projects/{project}/issues/{issue}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // Sprints
        Route::post('projects/{project}/sprints', [SprintController::class, 'store'])->name('sprints.store');
        Route::patch('projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('sprints.update');
        Route::post('projects/{project}/sprints/{sprint}/start', [SprintController::class, 'start'])->name('sprints.start');
        Route::post('projects/{project}/sprints/{sprint}/complete', [SprintController::class, 'complete'])->name('sprints.complete');
        Route::delete('projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('sprints.destroy');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
