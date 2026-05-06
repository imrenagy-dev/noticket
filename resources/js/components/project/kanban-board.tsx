import { router, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import type { BoardColumns, Issue, IssueStatus, IssueUser, Sprint } from '@/types';
import { IssueCard } from './issue-card';
import { IssueModal } from './issue-modal';

const COLUMNS: { key: IssueStatus; label: string; color: string }[] = [
    { key: 'todo', label: 'To Do', color: 'bg-muted' },
    { key: 'in_progress', label: 'In Progress', color: 'bg-blue-500/10' },
    { key: 'in_review', label: 'In Review', color: 'bg-yellow-500/10' },
    { key: 'done', label: 'Done', color: 'bg-green-500/10' },
];

interface Props {
    projectId: number;
    columns: BoardColumns;
    members: IssueUser[];
    sprints: Pick<Sprint, 'id' | 'name' | 'status'>[];
    activeSprintId?: number | null;
}

export function KanbanBoard({ projectId, columns, members, sprints, activeSprintId }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const [localColumns, setLocalColumns] = useState<BoardColumns>(columns);
    const [draggingId, setDraggingId] = useState<number | null>(null);
    const [dragOverCol, setDragOverCol] = useState<IssueStatus | null>(null);
    const [createInCol, setCreateInCol] = useState<IssueStatus | null>(null);

    // Sync with server when props change (only when not dragging)
    useEffect(() => {
        if (draggingId === null) {
            setLocalColumns(columns);
        }
    }, [columns]);

    function findIssue(id: number): Issue | undefined {
        for (const col of Object.values(localColumns)) {
            const found = col.find(i => i.id === id);
            if (found) return found;
        }
        return undefined;
    }

    function handleDragStart(id: number) {
        setDraggingId(id);
    }

    function handleDragOver(e: React.DragEvent, status: IssueStatus) {
        e.preventDefault();
        setDragOverCol(status);
    }

    function handleDrop(e: React.DragEvent, newStatus: IssueStatus) {
        e.preventDefault();
        if (draggingId === null) return;
        const issue = findIssue(draggingId);
        if (!issue || issue.status === newStatus) {
            setDraggingId(null);
            setDragOverCol(null);
            return;
        }

        // Optimistic update
        const next = { ...localColumns };
        for (const key of Object.keys(next) as IssueStatus[]) {
            next[key] = next[key].filter(i => i.id !== draggingId);
        }
        next[newStatus] = [...next[newStatus], { ...issue, status: newStatus }];
        setLocalColumns(next);
        setDraggingId(null);
        setDragOverCol(null);

        router.patch(
            `/${currentTeam.slug}/projects/${projectId}/issues/${draggingId}`,
            { status: newStatus },
            { preserveScroll: true, preserveState: true },
        );
    }

    function handleDragEnd() {
        setDraggingId(null);
        setDragOverCol(null);
    }

    return (
        <>
            <div className="flex h-full gap-4 overflow-x-auto pb-4">
                {COLUMNS.map((col) => {
                    const issues = localColumns[col.key];
                    const isOver = dragOverCol === col.key;

                    return (
                        <div
                            key={col.key}
                            className="flex w-72 flex-none flex-col rounded-lg"
                            onDragOver={(e) => handleDragOver(e, col.key)}
                            onDrop={(e) => handleDrop(e, col.key)}
                            onDragLeave={() => setDragOverCol(null)}
                        >
                            <div className={`mb-3 flex items-center justify-between rounded-md px-2 py-1.5 ${col.color}`}>
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-semibold">{col.label}</span>
                                    <span className="rounded-full bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground">
                                        {issues.length}
                                    </span>
                                </div>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    className="size-6 p-0 opacity-0 transition-opacity group-hover:opacity-100 hover:opacity-100"
                                    onClick={() => setCreateInCol(col.key)}
                                >
                                    <Plus className="size-3.5" />
                                </Button>
                            </div>

                            <div
                                className={`flex flex-1 flex-col gap-2 rounded-lg p-1 transition-colors ${isOver ? 'bg-accent/50 ring-2 ring-ring ring-offset-1' : ''}`}
                            >
                                {issues.map((issue) => (
                                    <IssueCard
                                        key={issue.id}
                                        issue={issue}
                                        projectId={projectId}
                                        draggable
                                        onDragStart={handleDragStart}
                                        onDragEnd={handleDragEnd}
                                    />
                                ))}

                                <button
                                    className="flex items-center gap-1 rounded-md px-2 py-1.5 text-sm text-muted-foreground opacity-0 transition-opacity hover:bg-accent hover:text-foreground hover:opacity-100 focus:opacity-100"
                                    onClick={() => setCreateInCol(col.key)}
                                >
                                    <Plus className="size-3.5" />
                                    Add issue
                                </button>
                            </div>
                        </div>
                    );
                })}
            </div>

            <IssueModal
                open={createInCol !== null}
                onClose={() => setCreateInCol(null)}
                projectId={projectId}
                members={members}
                sprints={sprints}
                defaultSprintId={activeSprintId}
                defaultStatus={createInCol ?? undefined}
            />
        </>
    );
}
