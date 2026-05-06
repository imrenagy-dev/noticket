import { Head, Link, router, usePage } from '@inertiajs/react';
import { CheckCircle2, Layers, ListTodo } from 'lucide-react';
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

    return (
        <>
            <Head title={`${project.name} – Board`} />

            <div className="flex h-full flex-col gap-4 p-6">
                <div className="flex items-center justify-between">
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
                    <div className="flex items-center gap-3 rounded-lg border border-border bg-card px-4 py-2.5">
                        <CheckCircle2 className="size-4 text-blue-500" />
                        <span className="font-medium">{activeSprint.name}</span>
                        {activeSprint.ends_at && (
                            <span className="text-sm text-muted-foreground">
                                · Due {new Date(activeSprint.ends_at).toLocaleDateString()}
                            </span>
                        )}
                        {activeSprint.goal && (
                            <span className="text-sm text-muted-foreground">· {activeSprint.goal}</span>
                        )}
                        <div className="ml-auto">
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => router.post(`${baseUrl}/sprints/${activeSprint.id}/complete`, {}, { preserveScroll: true })}
                            >
                                Complete Sprint
                            </Button>
                        </div>
                    </div>
                )}

                {!activeSprint ? (
                    <div className="flex flex-1 flex-col items-center justify-center gap-4">
                        <Layers className="size-12 text-muted-foreground/40" />
                        <div className="text-center">
                            <p className="font-medium">No active sprint</p>
                            <p className="text-sm text-muted-foreground">Go to Backlog to create and start a sprint</p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`${baseUrl}/backlog`}>Go to Backlog</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="flex-1 overflow-hidden">
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
