import { Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle2, KanbanSquare, LayoutDashboard, ListTodo, Users, Zap } from 'lucide-react';
import { dashboard, login, register } from '@/routes';

// ─── Kanban mockup ────────────────────────────────────────────────────────────

const COLUMNS = [
    {
        id: 'todo', label: 'To Do', count: 3,
        cards: [
            { key: 'NT-12', title: 'Fix login redirect on mobile', type: 'Bug', priority: 'medium', assignee: 'JD' },
            { key: 'NT-11', title: 'Update email notification templates', type: 'Task', priority: 'low', assignee: 'AM' },
            { key: 'NT-10', title: 'User onboarding flow redesign', type: 'Story', priority: 'high', assignee: null },
        ],
    },
    {
        id: 'in_progress', label: 'In Progress', count: 2,
        cards: [
            { key: 'NT-9', title: 'Dashboard analytics widgets', type: 'Task', priority: 'high', assignee: 'JD' },
            { key: 'NT-8', title: 'Sprint complete edge case fix', type: 'Bug', priority: 'medium', assignee: 'SW' },
        ],
    },
    {
        id: 'in_review', label: 'In Review', count: 1,
        cards: [
            { key: 'NT-7', title: 'Team invitation system', type: 'Story', priority: 'high', assignee: 'AM' },
        ],
    },
    {
        id: 'done', label: 'Done', count: 2,
        cards: [
            { key: 'NT-6', title: 'Kanban drag & drop', type: 'Task', priority: 'medium', assignee: 'JD' },
            { key: 'NT-5', title: 'Issue checklist component', type: 'Task', priority: 'low', assignee: 'SW' },
        ],
    },
];

const TYPE_STYLES: Record<string, string> = {
    Bug:   'bg-red-500/15 text-red-600 dark:text-red-400',
    Task:  'bg-blue-500/15 text-blue-600 dark:text-blue-400',
    Story: 'bg-green-500/15 text-green-600 dark:text-green-400',
};

const PRIORITY_COLOR: Record<string, string> = {
    high: 'text-orange-500', medium: 'text-yellow-500', low: 'text-sky-400',
};

const AVATAR_COLOR: Record<string, string> = {
    JD: 'bg-violet-500', AM: 'bg-emerald-500', SW: 'bg-orange-500',
};

function KanbanMockup() {
    return (
        <div className="overflow-hidden rounded-xl border border-border shadow-2xl">
            {/* Browser chrome */}
            <div className="flex items-center gap-2 border-b border-border bg-muted/70 px-4 py-2.5 backdrop-blur">
                <div className="flex gap-1.5">
                    <span className="size-2.5 rounded-full bg-red-400/80" />
                    <span className="size-2.5 rounded-full bg-yellow-400/80" />
                    <span className="size-2.5 rounded-full bg-green-400/80" />
                </div>
                <div className="ml-2 flex-1 rounded-md bg-background/80 px-3 py-1 text-[10px] text-muted-foreground">
                    localhost:8000 / my-team / projects / 1 / board
                </div>
            </div>

            {/* App shell */}
            <div className="bg-background">
                {/* Breadcrumb bar */}
                <div className="flex items-center gap-2 border-b border-border px-4 py-2 text-[11px]">
                    <span className="font-semibold">My Team</span>
                    <span className="text-muted-foreground">/</span>
                    <span className="text-muted-foreground">No Ticket App</span>
                    <span className="text-muted-foreground">/</span>
                    <span className="font-medium">Board</span>
                    <div className="ml-auto flex items-center gap-2">
                        <span className="rounded-full bg-green-500/15 px-2 py-0.5 text-[9px] font-semibold text-green-600 dark:text-green-400">
                            Sprint 1 · Active
                        </span>
                        <span className="rounded-md bg-primary px-2 py-0.5 text-[9px] font-semibold text-primary-foreground">
                            Complete sprint
                        </span>
                    </div>
                </div>

                {/* Kanban columns */}
                <div className="grid grid-cols-4 gap-3 p-4">
                    {COLUMNS.map((col) => (
                        <div key={col.id}>
                            <div className="mb-2 flex items-center gap-1.5">
                                <span className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                                    {col.label}
                                </span>
                                <span className="rounded-full bg-muted px-1.5 py-px text-[9px] font-medium text-muted-foreground">
                                    {col.count}
                                </span>
                            </div>
                            <div className="space-y-2">
                                {col.cards.map((card) => (
                                    <div key={card.key} className="rounded-lg border border-border bg-card p-2.5 shadow-sm transition-shadow hover:shadow-md">
                                        <p className="mb-2 text-[11px] font-medium leading-snug">{card.title}</p>
                                        <div className="flex items-center justify-between gap-1">
                                            <div className="flex items-center gap-1">
                                                <span className="font-mono text-[9px] text-muted-foreground">{card.key}</span>
                                                <span className={`rounded px-1 py-px text-[9px] font-medium ${TYPE_STYLES[card.type]}`}>
                                                    {card.type}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <span className={`text-[9px] leading-none ${PRIORITY_COLOR[card.priority]}`}>●</span>
                                                {card.assignee && (
                                                    <span className={`flex size-4 items-center justify-center rounded-full text-[8px] font-bold text-white ${AVATAR_COLOR[card.assignee]}`}>
                                                        {card.assignee[0]}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

// ─── Features ────────────────────────────────────────────────────────────────

const FEATURES = [
    { icon: KanbanSquare, title: 'Kanban Board', desc: 'Drag issues through To Do, In Progress, In Review, and Done with smooth native drag-and-drop.' },
    { icon: Zap,          title: 'Sprint Planning', desc: 'Create sprints, set goals, start them, and complete them. Unfinished work returns to the backlog automatically.' },
    { icon: Users,        title: 'Team Workspaces', desc: 'Invite members, assign Owner / Admin / Member roles, and manage multiple projects under a single team.' },
    { icon: CheckCircle2, title: 'Issue Tracking', desc: 'Epics, stories, tasks, bugs, and subtasks — with type, priority, assignee, story points, and checklists.' },
    { icon: LayoutDashboard, title: 'Live Dashboard', desc: 'See open issues assigned to you and recent team activity at a glance, sorted by priority.' },
    { icon: ListTodo,     title: 'Backlog Management', desc: 'Keep your backlog tidy. Assign issues to sprints or leave them for later — all in one organised view.' },
];

// ─── Page ────────────────────────────────────────────────────────────────────

export default function Welcome({ canRegister = true }: { canRegister?: boolean }) {
    const { auth, currentTeam } = usePage().props;
    const dashboardUrl = currentTeam ? dashboard((currentTeam as { slug: string }).slug) : '/';

    return (
        <>
            <Head title="No Ticket — Project management without the noise" />

            <div className="relative min-h-screen overflow-x-hidden bg-background text-foreground">

                {/* ── Dot grid background ── */}
                <div
                    aria-hidden
                    className="pointer-events-none absolute inset-0"
                    style={{
                        backgroundImage: 'radial-gradient(circle, color-mix(in oklch, var(--border) 80%, transparent) 1px, transparent 1px)',
                        backgroundSize: '28px 28px',
                    }}
                />

                {/* ── Gradient orbs ── */}
                <div aria-hidden className="pointer-events-none absolute inset-0 overflow-hidden">
                    <div className="absolute -top-40 right-0 size-[600px] rounded-full bg-primary/12 blur-3xl" />
                    <div className="absolute top-[60%] -left-32 size-[400px] rounded-full bg-primary/8 blur-3xl" />
                </div>

                {/* ── Nav ── */}
                <header className="relative z-20 flex items-center justify-between px-6 py-5 sm:px-12">
                    <span className="text-base font-bold tracking-tight">No Ticket</span>
                    <nav className="flex items-center gap-2">
                        {auth.user ? (
                            <Link href={dashboardUrl} className="rounded-lg bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90">
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={login()} className="rounded-lg px-4 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
                                    Log in
                                </Link>
                                {canRegister && (
                                    <Link href={register()} className="rounded-lg bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90">
                                        Get started
                                    </Link>
                                )}
                            </>
                        )}
                    </nav>
                </header>

                {/* ── Hero text ── */}
                <section className="relative z-10 mx-auto max-w-3xl px-6 pt-16 pb-14 text-center sm:pt-24 sm:pb-16">
                    <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-border bg-card/80 px-4 py-1.5 text-xs font-medium text-muted-foreground shadow-sm backdrop-blur">
                        <span className="size-1.5 rounded-full bg-primary" />
                        Jira-style project management · Open source
                    </div>

                    <h1 className="mb-5 text-5xl font-extrabold leading-[1.1] tracking-tight sm:text-6xl lg:text-7xl">
                        Project management{' '}
                        <span className="bg-gradient-to-br from-primary via-primary to-primary/40 bg-clip-text text-transparent">
                            without the noise
                        </span>
                    </h1>

                    <p className="mb-10 text-base text-muted-foreground sm:text-lg">
                        No Ticket gives your team a clean kanban board, sprint planning, and issue
                        tracking — all the essentials, nothing extra.
                    </p>

                    <div className="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        {auth.user ? (
                            <Link href={dashboardUrl} className="inline-flex items-center justify-center rounded-xl bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground shadow-lg transition-opacity hover:opacity-90">
                                Go to Dashboard
                            </Link>
                        ) : (
                            <>
                                {canRegister && (
                                    <Link href={register()} className="inline-flex items-center justify-center rounded-xl bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground shadow-lg transition-opacity hover:opacity-90">
                                        Create free account
                                    </Link>
                                )}
                                <Link href={login()} className="inline-flex items-center justify-center rounded-xl border border-border bg-card/80 px-8 py-3 text-sm font-semibold backdrop-blur transition-colors hover:bg-accent">
                                    Log in
                                </Link>
                            </>
                        )}
                    </div>
                </section>

                {/* ── Hero mockup ── */}
                <section className="relative z-10 mx-auto max-w-5xl px-4 sm:px-8">
                    <div className="relative" style={{ perspective: '1400px' }}>
                        <div style={{ transform: 'rotateX(7deg)', transformOrigin: 'center top', willChange: 'transform' }}>
                            <KanbanMockup />
                        </div>
                        {/* Bottom fade */}
                        <div className="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-background to-transparent" />
                    </div>
                </section>

                {/* ── Features ── */}
                <section className="relative z-10 mx-auto max-w-6xl px-6 pt-16 pb-24 sm:px-10">
                    <div className="mb-12 text-center">
                        <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">Everything your team needs</h2>
                        <p className="mt-2 text-muted-foreground">Built for teams that want to ship — not manage a tool.</p>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {FEATURES.map(({ icon: Icon, title, desc }) => (
                            <div key={title} className="group rounded-xl border border-border bg-card/80 p-6 backdrop-blur transition-shadow hover:shadow-lg">
                                <div className="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/18">
                                    <Icon className="size-5" />
                                </div>
                                <h3 className="mb-1.5 font-semibold">{title}</h3>
                                <p className="text-sm leading-relaxed text-muted-foreground">{desc}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* ── Footer ── */}
                <footer className="relative z-10 border-t border-border px-6 py-6 text-center text-xs text-muted-foreground">
                    No Ticket · Built with Laravel, React &amp; Inertia.js
                </footer>

            </div>
        </>
    );
}
