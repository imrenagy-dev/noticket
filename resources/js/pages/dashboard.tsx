import { Head, Link, usePage } from '@inertiajs/react';
import { CircleDot, FolderKanban, User, Zap } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { IssueTypeIcon } from '@/components/project/issue-type-icon';
import { PriorityIcon } from '@/components/project/priority-icon';
import { dashboard } from '@/routes';
import type { IssueType, IssueStatus, IssuePriority } from '@/types';

interface DashboardIssue {
    id: number;
    issue_key: string;
    title: string;
    type: IssueType;
    status: IssueStatus;
    priority: IssuePriority;
    project: { id: number; name: string; key: string };
    assignee?: { id: number; name: string } | null;
    updated_at: string;
}

interface Stats {
    projects: number;
    open_issues: number;
    my_issues: number;
    active_sprints: number;
}

interface Props {
    stats: Stats;
    myIssues: DashboardIssue[];
    recentIssues: DashboardIssue[];
}

const STATUS_LABELS: Record<IssueStatus, string> = {
    todo: 'To Do',
    in_progress: 'In Progress',
    in_review: 'In Review',
    done: 'Done',
};

const STATUS_COLORS: Record<IssueStatus, string> = {
    todo: 'bg-muted text-muted-foreground',
    in_progress: 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
    in_review: 'bg-yellow-500/15 text-yellow-700 dark:text-yellow-400',
    done: 'bg-green-500/15 text-green-700 dark:text-green-400',
};

function timeAgo(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    return `${Math.floor(hrs / 24)}d ago`;
}

function greeting(): string {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
}

function IssueRow({ issue, teamSlug }: { issue: DashboardIssue; teamSlug: string }) {
    return (
        <Link
            href={`/${teamSlug}/projects/${issue.project.id}/issues/${issue.id}`}
            className="flex items-start gap-3 rounded-lg px-2 py-2 transition-colors hover:bg-accent"
        >
            <div className="mt-0.5 shrink-0">
                <IssueTypeIcon type={issue.type} />
            </div>
            <div className="min-w-0 flex-1">
                <div className="mb-0.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                    <span className="font-mono">{issue.issue_key}</span>
                    <span>·</span>
                    <span className="truncate">{issue.project.name}</span>
                </div>
                <p className="truncate text-sm font-medium">{issue.title}</p>
            </div>
            <div className="flex shrink-0 items-center gap-2">
                <PriorityIcon priority={issue.priority} />
                <Badge variant="outline" className={`hidden text-xs sm:inline-flex ${STATUS_COLORS[issue.status]}`}>
                    {STATUS_LABELS[issue.status]}
                </Badge>
                <span className="w-12 text-right text-xs text-muted-foreground">{timeAgo(issue.updated_at)}</span>
            </div>
        </Link>
    );
}

function StatCard({ title, value, icon: Icon, color }: {
    title: string; value: number; icon: React.ElementType; color: string;
}) {
    return (
        <Card className="bg-card/80 backdrop-blur-sm transition-shadow hover:shadow-lg">
            <CardContent className="flex items-center gap-4 pt-6">
                <div className={`flex size-11 shrink-0 items-center justify-center rounded-xl ${color}`}>
                    <Icon className="size-5" />
                </div>
                <div>
                    <p className="text-2xl font-bold">{value}</p>
                    <p className="text-xs text-muted-foreground">{title}</p>
                </div>
            </CardContent>
        </Card>
    );
}

export default function Dashboard({ stats, myIssues, recentIssues }: Props) {
    const { currentTeam, auth } = usePage().props as {
        currentTeam: { slug: string };
        auth: { user: { name: string } };
    };
    const firstName = auth.user.name.split(' ')[0];

    return (
        <>
            <Head title="Dashboard" />

            <div className="relative flex flex-col gap-6 p-4 sm:p-6">

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
                    <div className="absolute -top-40 -right-32 size-[520px] rounded-full bg-primary/8 blur-3xl" />
                    <div className="absolute top-[45%] -left-48 size-[400px] rounded-full bg-primary/6 blur-3xl" />
                </div>

                {/* Hero header */}
                <header className="relative z-10">
                    <h1 className="text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {greeting()},{' '}
                        <span className="bg-gradient-to-br from-primary to-primary/50 bg-clip-text text-transparent">
                            {firstName}
                        </span>
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Here's what's happening across your team today.
                    </p>
                </header>

                {/* Stat cards */}
                <div className="relative z-10 grid gap-3 grid-cols-2 sm:gap-4 lg:grid-cols-4">
                    <StatCard title="Projects"       value={stats.projects}       icon={FolderKanban} color="bg-violet-500/12 text-violet-600 dark:text-violet-400" />
                    <StatCard title="Open Issues"    value={stats.open_issues}    icon={CircleDot}    color="bg-blue-500/12 text-blue-600 dark:text-blue-400" />
                    <StatCard title="Assigned to Me" value={stats.my_issues}      icon={User}         color="bg-orange-500/12 text-orange-600 dark:text-orange-400" />
                    <StatCard title="Active Sprints" value={stats.active_sprints} icon={Zap}          color="bg-green-500/12 text-green-600 dark:text-green-400" />
                </div>

                {/* Issue panels */}
                <div className="relative z-10 grid gap-6 lg:grid-cols-2">
                    <Card className="bg-card/80 backdrop-blur-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">My Open Issues</CardTitle>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-2">
                            {myIssues.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    No open issues assigned to you.
                                </p>
                            ) : (
                                <div className="space-y-0.5">
                                    {myIssues.map((issue) => (
                                        <IssueRow key={issue.id} issue={issue} teamSlug={currentTeam.slug} />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="bg-card/80 backdrop-blur-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Recent Activity</CardTitle>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-2">
                            {recentIssues.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">No issues yet.</p>
                            ) : (
                                <div className="space-y-0.5">
                                    {recentIssues.map((issue) => (
                                        <IssueRow key={issue.id} issue={issue} teamSlug={currentTeam.slug} />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

            </div>
        </>
    );
}

Dashboard.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});
