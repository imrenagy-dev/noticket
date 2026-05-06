import { Link } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import type { Issue } from '@/types';
import { IssueTypeIcon } from './issue-type-icon';
import { PriorityIcon } from './priority-icon';

interface Props {
    issue: Issue;
    projectId: number;
    draggable?: boolean;
    onDragStart?: (id: number) => void;
    onDragEnd?: () => void;
    compact?: boolean;
}

function initials(name: string) {
    return name.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

export function IssueCard({ issue, projectId, draggable = false, onDragStart, onDragEnd, compact = false }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const href = `/${currentTeam.slug}/projects/${projectId}/issues/${issue.id}`;

    return (
        <div
            draggable={draggable}
            onDragStart={draggable && onDragStart ? () => onDragStart(issue.id) : undefined}
            onDragEnd={onDragEnd}
            className={`group rounded-md border border-border bg-card p-3 shadow-sm transition-shadow hover:shadow-md ${draggable ? 'cursor-grab active:cursor-grabbing' : 'cursor-pointer'}`}
        >
            <Link href={href} className="block">
                <div className="flex items-start justify-between gap-2">
                    <p className="text-sm font-medium leading-snug text-card-foreground line-clamp-2">
                        {issue.title}
                    </p>
                </div>

                {!compact && (
                    <div className="mt-2.5 flex items-center justify-between gap-2">
                        <div className="flex items-center gap-1.5">
                            <IssueTypeIcon type={issue.type} />
                            <span className="text-xs text-muted-foreground">{issue.issue_key}</span>
                            <PriorityIcon priority={issue.priority} />
                        </div>

                        <div className="flex items-center gap-1.5">
                            {issue.story_points != null && (
                                <span className="flex size-5 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground">
                                    {issue.story_points}
                                </span>
                            )}
                            {issue.assignee ? (
                                <span
                                    title={issue.assignee.name}
                                    className="flex size-6 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground"
                                >
                                    {initials(issue.assignee.name)}
                                </span>
                            ) : (
                                <span className="flex size-6 items-center justify-center rounded-full border-2 border-dashed border-muted-foreground/30" />
                            )}
                        </div>
                    </div>
                )}

                {compact && (
                    <div className="mt-1.5 flex items-center gap-1.5">
                        <IssueTypeIcon type={issue.type} className="size-3.5" />
                        <span className="text-xs text-muted-foreground">{issue.issue_key}</span>
                        <PriorityIcon priority={issue.priority} className="size-3" />
                    </div>
                )}
            </Link>
        </div>
    );
}
