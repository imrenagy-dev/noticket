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

function IssueRow({ issue, teamSlug }: { issue: DashboardIssue; teamSlug: string }) {
    return (
        <Link
            href={`/${teamSlug}/projects/${issue.project.id}/issues/${issue.id}`}
            className="flex items-start gap-3 rounded-lg px-2 py-2 hover:bg-accent transition-colors"
        >
            <div className="mt-0.5 shrink-0">
                <IssueTypeIcon type={issue.type} />
            </div>
            <div className="min-w-0 flex-1">
                <div className="flex items-center gap-1.5 text-xs text-muted-foreground mb-0.5">
                    <span className="font-mono">{issue.issue_key}</span>
                    <span>·</span>
                    <span>{issue.project.name}</span>
                </div>
                <p className="truncate text-sm font-medium">{issue.title}</p>
            </div>
            <div className="flex shrink-0 items-center gap-2">
                <PriorityIcon priority={issue.priority} />
                <Badge variant="outline" className={`text-xs ${STATUS_COLORS[issue.status]}`}>
                    {STATUS_LABELS[issue.status]}
                </Badge>
                <span className="text-xs text-muted-foreground w-14 text-right">{timeAgo(issue.updated_at)}</span>
            </div>
        </Link>
    );
}

function StatCard({ title, value, icon: Icon, color }: { title: string; value: number; icon: React.ElementType; color: string }) {
    return (
        <Card>
            <CardContent className="flex items-center gap-4 pt-6">
                <div className={`flex size-10 items-center justify-center rounded-lg ${color}`}>
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
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-6 p-6">

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard title="Projects" value={stats.projects} icon={FolderKanban} color="bg-violet-500/10 text-violet-600 dark:text-violet-400" />
                    <StatCard title="Open Issues" value={stats.open_issues} icon={CircleDot} color="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
                    <StatCard title="Assigned to Me" value={stats.my_issues} icon={User} color="bg-orange-500/10 text-orange-600 dark:text-orange-400" />
                    <StatCard title="Active Sprints" value={stats.active_sprints} icon={Zap} color="bg-green-500/10 text-green-600 dark:text-green-400" />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">My Open Issues</CardTitle>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-2">
                            {myIssues.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">No open issues assigned to you.</p>
                            ) : (
                                <div className="space-y-0.5">
                                    {myIssues.map((issue) => (
                                        <IssueRow key={issue.id} issue={issue} teamSlug={currentTeam.slug} />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
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
