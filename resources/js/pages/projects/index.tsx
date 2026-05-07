import { Head, Link, usePage } from '@inertiajs/react';
import { FolderKanban, Plus, Sparkles } from 'lucide-react';
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
            className="group flex flex-col gap-3 rounded-xl border border-border bg-card/80 p-5 shadow-sm backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg"
        >
            <div className="flex items-start justify-between">
                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-sm font-bold text-primary ring-1 ring-primary/20 transition-colors group-hover:bg-primary/15">
                    {project.key}
                </div>
            </div>
            <div>
                <h3 className="font-semibold transition-colors group-hover:text-primary">{project.name}</h3>
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

            <div className="relative flex flex-1 flex-col gap-6 p-4 sm:p-6">

                {/* Dot grid */}
                <div
                    aria-hidden
                    className="pointer-events-none absolute inset-0"
                    style={{
                        backgroundImage: 'radial-gradient(circle, color-mix(in oklch, var(--border) 70%, transparent) 1px, transparent 1px)',
                        backgroundSize: '28px 28px',
                    }}
                />

                {/* Gradient orbs */}
                <div aria-hidden className="pointer-events-none absolute inset-0 overflow-hidden">
                    <div className="absolute -top-40 right-0 size-[500px] rounded-full bg-primary/8 blur-3xl" />
                    <div className="absolute top-[55%] -left-48 size-[380px] rounded-full bg-primary/5 blur-3xl" />
                </div>

                {/* Header */}
                <div className="relative z-10 flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-extrabold tracking-tight sm:text-3xl">
                            <span className="bg-gradient-to-br from-foreground to-foreground/60 bg-clip-text text-transparent">
                                Projects
                            </span>
                        </h1>
                        <p className="mt-0.5 text-sm text-muted-foreground">{currentTeam.name}</p>
                    </div>
                    <Button onClick={() => setCreating(true)} className="shrink-0">
                        <Plus className="size-4" />
                        <span className="hidden sm:inline">New Project</span>
                        <span className="sm:hidden">New</span>
                    </Button>
                </div>

                {/* Content */}
                <div className="relative z-10">
                    {projects.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-border bg-card/40 py-20 backdrop-blur-sm sm:py-28">
                            <div className="flex size-16 items-center justify-center rounded-2xl bg-primary/10">
                                <FolderKanban className="size-8 text-primary/60" />
                            </div>
                            <div className="text-center">
                                <p className="font-semibold">No projects yet</p>
                                <p className="text-sm text-muted-foreground">Create your first project to get started</p>
                            </div>
                            <Button onClick={() => setCreating(true)}>
                                <Sparkles className="size-4" />
                                Create Project
                            </Button>
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {projects.map((p) => (
                                <ProjectCard key={p.id} project={p} teamSlug={currentTeam.slug} />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            <CreateProjectModal open={creating} onClose={() => setCreating(false)} />
        </>
    );
}
