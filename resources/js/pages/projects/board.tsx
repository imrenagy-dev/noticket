import { Head, Link, router, usePage } from '@inertiajs/react';
import { CheckCircle2, Layers, ListTodo, Zap } from 'lucide-react';
import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { KanbanBoard } from '@/components/project/kanban-board';
import type { BoardColumns, IssueUser, Project, Sprint } from '@/types';

interface Props {
    project: Project;
    activeSprint: Sprint | null;
    columns: BoardColumns;
    members: IssueUser[];
    sprints: Pick<Sprint, 'id' | 'name' | 'status'>[];
}

export default function Board({ project, activeSprint, columns, members, sprints }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const baseUrl = `/${currentTeam.slug}/projects/${project.id}`;

    useEffect(() => {
        localStorage.setItem(`noticket_view_${project.id}`, 'board');
    }, [project.id]);

    return (
        <>
            <Head title={`${project.name} – Board`} />

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
                    <div className="absolute -top-40 -right-32 size-[450px] rounded-full bg-primary/8 blur-3xl" />
                </div>

                {/* Header */}
                <div className="relative z-10 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <h1 className="text-xl font-bold">{project.name}</h1>
                        <div className="flex rounded-md border border-border text-sm">
                            <Link
                                href={`${baseUrl}/board`}
                                className="flex items-center gap-1.5 border-r border-border bg-accent px-3 py-1.5 font-medium"
                            >
                                <Layers className="size-3.5" /> Board
                            </Link>
                            <Link
                                href={`${baseUrl}/backlog`}
                                className="flex items-center gap-1.5 px-3 py-1.5 text-muted-foreground hover:bg-accent hover:text-foreground"
                            >
                                <ListTodo className="size-3.5" /> Backlog
                            </Link>
                        </div>
                    </div>
                </div>

                {activeSprint && (
                    <div className="relative z-10 flex items-center gap-3 rounded-xl border border-border bg-card/80 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                            <Zap className="size-4 text-primary" />
                        </div>
                        <div className="min-w-0 flex-1">
                            <span className="font-semibold">{activeSprint.name}</span>
                            {activeSprint.ends_at && (
                                <span className="ml-2 text-sm text-muted-foreground">
                                    · Due {new Date(activeSprint.ends_at).toLocaleDateString()}
                                </span>
                            )}
                            {activeSprint.goal && (
                                <span className="ml-2 text-sm text-muted-foreground">· {activeSprint.goal}</span>
                            )}
                        </div>
                        <span className="rounded-full bg-green-500/15 px-2.5 py-1 text-xs font-semibold text-green-600 dark:text-green-400">
                            Active
                        </span>
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={() => router.post(`${baseUrl}/sprints/${activeSprint.id}/complete`, {}, { preserveScroll: true })}
                        >
                            <CheckCircle2 className="size-4" />
                            Complete Sprint
                        </Button>
                    </div>
                )}

                {!activeSprint ? (
                    <div className="relative z-10 flex flex-1 flex-col items-center justify-center gap-4">
                        <div className="flex size-16 items-center justify-center rounded-2xl bg-primary/10">
                            <Layers className="size-8 text-primary/60" />
                        </div>
                        <div className="text-center">
                            <p className="font-semibold">No active sprint</p>
                            <p className="text-sm text-muted-foreground">Go to Backlog to create and start a sprint</p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`${baseUrl}/backlog`}>Go to Backlog</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="relative z-10 flex-1 overflow-hidden">
                        <KanbanBoard
                            projectId={project.id}
                            columns={columns}
                            members={members}
                            sprints={sprints}
                            activeSprintId={activeSprint.id}
                        />
                    </div>
                )}
            </div>
        </>
    );
}
