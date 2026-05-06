import { Head, Link, usePage } from '@inertiajs/react';
import { FolderKanban, Plus } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { CreateProjectModal } from '@/components/project/create-project-modal';
import type { Project } from '@/types';

interface Props {
    projects: Project[];
}

function ProjectCard({ project, teamSlug }: { project: Project; teamSlug: string }) {
    return (
        <Link
            href={`/${teamSlug}/projects/${project.id}/backlog`}
            className="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 shadow-sm transition-shadow hover:shadow-md"
        >
            <div className="flex items-start justify-between">
                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-sm font-bold text-primary">
                    {project.key}
                </div>
            </div>
            <div>
                <h3 className="font-semibold group-hover:text-primary">{project.name}</h3>
                {project.description && (
                    <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">{project.description}</p>
                )}
            </div>
            <div className="mt-auto flex items-center gap-1 text-xs text-muted-foreground">
                <FolderKanban className="size-3.5" />
                <span>{project.issue_count ?? 0} issues</span>
            </div>
        </Link>
    );
}

export default function ProjectsIndex({ projects }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string; name: string } };
    const [creating, setCreating] = useState(false);

    return (
        <>
            <Head title="Projects" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Projects</h1>
                        <p className="text-sm text-muted-foreground">{currentTeam.name}</p>
                    </div>
                    <Button onClick={() => setCreating(true)}>
                        <Plus className="size-4" />
                        New Project
                    </Button>
                </div>

                {projects.length === 0 ? (
                    <div className="flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-border py-24">
                        <FolderKanban className="size-12 text-muted-foreground/50" />
                        <div className="text-center">
                            <p className="font-medium">No projects yet</p>
                            <p className="text-sm text-muted-foreground">Create your first project to get started</p>
                        </div>
                        <Button onClick={() => setCreating(true)}>Create Project</Button>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {projects.map((p) => (
                            <ProjectCard key={p.id} project={p} teamSlug={currentTeam.slug} />
                        ))}
                    </div>
                )}
            </div>

            <CreateProjectModal open={creating} onClose={() => setCreating(false)} />
        </>
    );
}
