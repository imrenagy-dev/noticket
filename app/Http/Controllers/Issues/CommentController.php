<?php

namespace App\Http\Controllers\Issues;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use App\Services\CommentServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private CommentServiceInterface $commentService) {}

    public function store(Request $request, Team $current_team, Project $project, Issue $issue): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $this->commentService->create($issue, $validated['content'], $request->user()->id);

        return back();
    }

    public function update(Request $request, Team $current_team, Project $project, Issue $issue, Comment $comment): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);
        abort_if($comment->issue_id !== $issue->id, 404);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $this->commentService->update($comment, $validated['content'], $request->user()->id);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Project $project, Issue $issue, Comment $comment): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);
        abort_if($comment->issue_id !== $issue->id, 404);

        $this->commentService->delete($comment, $request->user()->id);

        return back();
    }
}
