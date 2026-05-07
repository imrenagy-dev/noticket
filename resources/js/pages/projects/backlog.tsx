import { Head, Link, router, usePage } from '@inertiajs/react';
import { Layers, ListTodo, Plus } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
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

    const baseUrl = `/${currentTeam.slug}/projects/${project.id}`;
    const hasActiveSprint = sprints.some(s => s.status === 'active');

    const allSprints: Pick<Sprint, 'id' | 'name' | 'status'>[] = sprints.map(s => ({
        id: s.id,
        name: s.name,
        status: s.status,
    }));

    return (
        <>
            <Head title={`${project.name} – Backlog`} />

            <div className="relative flex flex-col gap-4 p-6">

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
                                            <div key={issue.id} className="px-4 py-2">
                                                <IssueCard issue={issue} projectId={project.id} compact />
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
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
