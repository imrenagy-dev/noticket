<?php

namespace App\Http\Controllers;

use App\Data\IssueDTO;
use App\Models\Team;
use App\Services\DashboardServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardServiceInterface $dashboard) {}

    public function index(Request $request, Team $current_team): Response
    {
        $userId = $request->user()->id;

        $myIssues = $this->dashboard->getMyIssues($current_team, $userId)
            ->map(fn (IssueDTO $i) => [
                'id'         => $i->id,
                'issue_key'  => $i->issueKey,
                'title'      => $i->title,
                'type'       => $i->type->value,
                'status'     => $i->status->value,
                'priority'   => $i->priority->value,
                'project'    => $i->project ? ['id' => $i->project->id, 'name' => $i->project->name, 'key' => $i->project->key] : null,
                'updated_at' => $i->updatedAt->toISOString(),
            ]);

        $recentIssues = $this->dashboard->getRecentIssues($current_team)
            ->map(fn (IssueDTO $i) => [
                'id'         => $i->id,
                'issue_key'  => $i->issueKey,
                'title'      => $i->title,
                'type'       => $i->type->value,
                'status'     => $i->status->value,
                'priority'   => $i->priority->value,
                'project'    => $i->project ? ['id' => $i->project->id, 'name' => $i->project->name, 'key' => $i->project->key] : null,
                'assignee'   => $i->assignee ? ['id' => $i->assignee->id, 'name' => $i->assignee->name] : null,
                'updated_at' => $i->updatedAt->toISOString(),
            ]);

        return Inertia::render('dashboard', [
            'stats'        => $this->dashboard->getStats($current_team, $userId),
            'myIssues'     => $myIssues,
            'recentIssues' => $recentIssues,
        ]);
    }
}
