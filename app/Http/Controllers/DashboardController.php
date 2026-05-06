<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, Team $current_team): Response
    {
        $user = $request->user();
        $projectIds = $current_team->projects()->pluck('id');

        $stats = [
            'projects' => $projectIds->count(),
            'open_issues' => $projectIds->isEmpty() ? 0 : Issue::whereIn('project_id', $projectIds)->where('status', '!=', 'done')->count(),
            'my_issues' => $projectIds->isEmpty() ? 0 : Issue::whereIn('project_id', $projectIds)->where('assignee_id', $user->id)->where('status', '!=', 'done')->count(),
            'active_sprints' => $projectIds->isEmpty() ? 0 : Sprint::whereIn('project_id', $projectIds)->where('status', 'active')->count(),
        ];

        $myIssues = $projectIds->isEmpty() ? [] : Issue::whereIn('project_id', $projectIds)
            ->where('assignee_id', $user->id)
            ->where('status', '!=', 'done')
            ->with(['project:id,name,key'])
            ->orderByRaw("FIELD(priority, 'highest', 'high', 'medium', 'low', 'lowest')")
            ->limit(10)
            ->get()
            ->map(fn (Issue $i) => [
                'id' => $i->id,
                'issue_key' => $i->project->key . '-' . $i->number,
                'title' => $i->title,
                'type' => $i->type,
                'status' => $i->status,
                'priority' => $i->priority,
                'project' => ['id' => $i->project->id, 'name' => $i->project->name, 'key' => $i->project->key],
                'updated_at' => $i->updated_at?->toISOString(),
            ]);

        $recentIssues = $projectIds->isEmpty() ? [] : Issue::whereIn('project_id', $projectIds)
            ->with(['project:id,name,key', 'assignee:id,name'])
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn (Issue $i) => [
                'id' => $i->id,
                'issue_key' => $i->project->key . '-' . $i->number,
                'title' => $i->title,
                'type' => $i->type,
                'status' => $i->status,
                'priority' => $i->priority,
                'project' => ['id' => $i->project->id, 'name' => $i->project->name, 'key' => $i->project->key],
                'assignee' => $i->assignee ? ['id' => $i->assignee->id, 'name' => $i->assignee->name] : null,
                'updated_at' => $i->updated_at?->toISOString(),
            ]);

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'myIssues' => $myIssues,
            'recentIssues' => $recentIssues,
        ]);
    }
}
