import { router, usePage } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Play, Square, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import type { Issue, IssueUser, SprintWithIssues, Sprint } from '@/types';
import { IssueCard } from './issue-card';
import { IssueModal } from './issue-modal';

interface Props {
    projectId: number;
    sprint: SprintWithIssues;
    members: IssueUser[];
    allSprints: Pick<Sprint, 'id' | 'name' | 'status'>[];
    canStart?: boolean;
}

export function SprintSection({ projectId, sprint, members, allSprints, canStart = false }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const [collapsed, setCollapsed] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);

    const baseUrl = `/${currentTeam.slug}/projects/${projectId}`;

    function startSprint() {
        router.post(`${baseUrl}/sprints/${sprint.id}/start`, {}, { preserveScroll: true });
    }

    function completeSprint() {
        if (!confirm('Complete sprint? Incomplete issues will move to the backlog.')) return;
        router.post(`${baseUrl}/sprints/${sprint.id}/complete`, {}, { preserveScroll: true });
    }

    function deleteSprint() {
        if (!confirm('Delete sprint? Issues will move to the backlog.')) return;
        router.delete(`${baseUrl}/sprints/${sprint.id}`, { preserveScroll: true });
    }

    const doneCount = sprint.issues.filter(i => i.status === 'done').length;
    const isActive = sprint.status === 'active';

    return (
        <div className="mb-4 rounded-lg border border-border bg-card">
            <div
                className="flex cursor-pointer items-center gap-2 px-4 py-3"
                onClick={() => setCollapsed(c => !c)}
            >
                {collapsed ? <ChevronRight className="size-4 text-muted-foreground" /> : <ChevronDown className="size-4 text-muted-foreground" />}

                <div className="flex flex-1 items-center gap-3">
                    <span className="font-semibold">{sprint.name}</span>
                    {isActive && (
                        <span className="rounded-full bg-blue-500/15 px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400">
                            Active
                        </span>
                    )}
                    <span className="text-sm text-muted-foreground">
                        {sprint.issues.length} issues
                        {sprint.issues.length > 0 && ` · ${doneCount} done`}
                    </span>
                </div>

                <div className="flex items-center gap-2" onClick={(e) => e.stopPropagation()}>
                    <Button size="sm" variant="ghost" onClick={() => setCreateOpen(true)} className="h-7 text-xs">
                        + Issue
                    </Button>
                    {!isActive && canStart && (
                        <Button size="sm" variant="outline" onClick={startSprint} className="h-7 gap-1 text-xs">
                            <Play className="size-3" />
                            Start Sprint
                        </Button>
                    )}
                    {isActive && (
                        <Button size="sm" variant="outline" onClick={completeSprint} className="h-7 gap-1 text-xs">
                            <Square className="size-3" />
                            Complete Sprint
                        </Button>
                    )}
                    {!isActive && (
                        <Button size="sm" variant="ghost" onClick={deleteSprint} className="size-7 p-0 text-muted-foreground hover:text-destructive">
                            <Trash2 className="size-3.5" />
                        </Button>
                    )}
                </div>
            </div>

            {!collapsed && (
                <div className="border-t border-border">
                    {sprint.issues.length === 0 ? (
                        <p className="px-4 py-6 text-center text-sm text-muted-foreground">
                            No issues in this sprint.{' '}
                            <button className="underline" onClick={() => setCreateOpen(true)}>Add one</button>
                        </p>
                    ) : (
                        <div className="divide-y divide-border">
                            {sprint.issues.map((issue) => (
                                <div key={issue.id} className="px-4 py-2">
                                    <IssueCard issue={issue} projectId={projectId} compact />
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            <IssueModal
                open={createOpen}
                onClose={() => setCreateOpen(false)}
                projectId={projectId}
                members={members}
                sprints={allSprints}
                defaultSprintId={sprint.id}
            />
        </div>
    );
}
