import { Head, Link, router, usePage } from '@inertiajs/react';
import { Layers, ListTodo, Plus, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { IssueCard } from '@/components/project/issue-card';
import { IssueModal } from '@/components/project/issue-modal';
import { SprintSection } from '@/components/project/sprint-section';
import type { Issue, IssueUser, Project, Sprint, SprintWithIssues } from '@/types';

interface Props {
    project: Project;
    sprints: SprintWithIssues[];
    backlog: Issue[];
    members: IssueUser[];
}

export default function Backlog({ project, sprints, backlog, members }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const [createOpen, setCreateOpen] = useState(false);
    const [backlogCollapsed, setBacklogCollapsed] = useState(false);
    const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
    const [bulkSprintId, setBulkSprintId] = useState<string>('backlog');

    const baseUrl = `/${currentTeam.slug}/projects/${project.id}`;
    const hasActiveSprint = sprints.some(s => s.status === 'active');

    function toggleSelect(id: number) {
        setSelectedIds(prev => {
            const next = new Set(prev);
            next.has(id) ? next.delete(id) : next.add(id);
            return next;
        });
    }

    function clearSelection() {
        setSelectedIds(new Set());
        setBulkSprintId('backlog');
    }

    function applyBulkMove() {
        router.patch(`${baseUrl}/issues/bulk`, {
            issue_ids: Array.from(selectedIds),
            sprint_id: bulkSprintId === 'backlog' ? null : Number(bulkSprintId),
        }, {
            preserveScroll: true,
            onSuccess: clearSelection,
        });
    }

    useEffect(() => {
        localStorage.setItem(`noticket_view_${project.id}`, 'backlog');
    }, [project.id]);

    const allSprints: Pick<Sprint, 'id' | 'name' | 'status'>[] = sprints.map(s => ({
        id: s.id,
        name: s.name,
        status: s.status,
    }));

    return (
        <>
            <Head title={`${project.name} – Backlog`} />

            <div className="relative flex flex-1 flex-col gap-4 p-4 sm:p-6">

                {/* Dot grid background */}
                <div
                    aria-hidden
                    className="pointer-events-none absolute inset-0"
                    style={{
                        backgroundImage: 'radial-gradient(circle, color-mix(in oklch, var(--border) 70%, transparent) 1px, transparent 1px)',
                        backgroundSize: '28px 28px',
                    }}
                />

                {/* Gradient orb */}
                <div aria-hidden className="pointer-events-none absolute inset-0 overflow-hidden">
                    <div className="absolute -top-40 right-0 size-[450px] rounded-full bg-primary/8 blur-3xl" />
                </div>

                {/* Header */}
                <div className="relative z-10 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <h1 className="text-xl font-bold">{project.name}</h1>
                        <div className="flex rounded-md border border-border text-sm">
                            <Link
                                href={`${baseUrl}/board`}
                                className="flex items-center gap-1.5 border-r border-border px-3 py-1.5 text-muted-foreground hover:bg-accent hover:text-foreground"
                            >
                                <Layers className="size-3.5" /> Board
                            </Link>
                            <Link
                                href={`${baseUrl}/backlog`}
                                className="flex items-center gap-1.5 bg-accent px-3 py-1.5 font-medium"
                            >
                                <ListTodo className="size-3.5" /> Backlog
                            </Link>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button size="sm" variant="outline" onClick={() => router.post(`${baseUrl}/sprints`, {}, { preserveScroll: true })}>
                            <Plus className="size-4" /> Create Sprint
                        </Button>
                        <Button size="sm" onClick={() => setCreateOpen(true)}>
                            <Plus className="size-4" /> Create Issue
                        </Button>
                    </div>
                </div>

                <div className="relative z-10 flex flex-col gap-4">
                    {sprints.map((sprint) => (
                        <SprintSection
                            key={sprint.id}
                            projectId={project.id}
                            sprint={sprint}
                            members={members}
                            allSprints={allSprints}
                            canStart={!hasActiveSprint}
                            selectedIds={selectedIds}
                            onToggleSelect={toggleSelect}
                        />
                    ))}

                    <div className="rounded-xl border border-border bg-card/80 shadow-sm backdrop-blur-sm">
                        <div
                            className="flex cursor-pointer items-center gap-2 px-4 py-3"
                            onClick={() => setBacklogCollapsed(c => !c)}
                        >
                            <span className="font-semibold">Backlog</span>
                            <span className="text-sm text-muted-foreground">{backlog.length} issues</span>
                            <div className="ml-auto" onClick={(e) => e.stopPropagation()}>
                                <Button size="sm" variant="ghost" onClick={() => setCreateOpen(true)} className="h-7 text-xs">
                                    + Issue
                                </Button>
                            </div>
                        </div>

                        {!backlogCollapsed && (
                            <div className="border-t border-border">
                                {backlog.length === 0 ? (
                                    <p className="px-4 py-8 text-center text-sm text-muted-foreground">
                                        No issues in the backlog.{' '}
                                        <button className="underline" onClick={() => setCreateOpen(true)}>Create one</button>
                                    </p>
                                ) : (
                                    <div className="divide-y divide-border">
                                        {backlog.map((issue) => (
                                            <div key={issue.id} className="px-4 py-1.5">
                                                <IssueCard
                                                    issue={issue}
                                                    projectId={project.id}
                                                    compact
                                                    selected={selectedIds.has(issue.id)}
                                                    onToggleSelect={toggleSelect}
                                                />
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                {selectedIds.size > 0 && (
                    <div className="fixed bottom-6 left-1/2 z-50 -translate-x-1/2">
                        <div className="flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-3 shadow-xl">
                            <span className="text-sm font-medium text-muted-foreground">
                                {selectedIds.size} selected
                            </span>
                            <div className="h-4 w-px bg-border" />
                            <Select value={bulkSprintId} onValueChange={setBulkSprintId}>
                                <SelectTrigger className="h-8 w-44 text-xs">
                                    <SelectValue placeholder="Move to…" />
                                </SelectTrigger>
                                <SelectContent side="top">
                                    <SelectItem value="backlog">Backlog</SelectItem>
                                    {allSprints.map(s => (
                                        <SelectItem key={s.id} value={String(s.id)}>
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Button size="sm" className="h-8 text-xs" onClick={applyBulkMove}>
                                Move
                            </Button>
                            <button onClick={clearSelection} className="text-muted-foreground hover:text-foreground">
                                <X className="size-4" />
                            </button>
                        </div>
                    </div>
                )}
            </div>

            <IssueModal
                open={createOpen}
                onClose={() => setCreateOpen(false)}
                projectId={project.id}
                members={members}
                sprints={allSprints}
            />
        </>
    );
}
