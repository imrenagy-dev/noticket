import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, CheckSquare, Clock, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { IssueChecklist } from '@/components/project/issue-checklist';
import { IssueTypeIcon } from '@/components/project/issue-type-icon';
import { PriorityIcon, priorityLabel } from '@/components/project/priority-icon';
import type { ChecklistItem, Comment, IssueDetail, IssueHistory, IssueUser, Project, Sprint } from '@/types';

interface Props {
    project: Project;
    issue: IssueDetail;
    members: IssueUser[];
    sprints: Pick<Sprint, 'id' | 'name' | 'status'>[];
}

const STATUS_LABELS: Record<string, string> = {
    todo: 'To Do',
    in_progress: 'In Progress',
    in_review: 'In Review',
    done: 'Done',
};

const STATUS_COLORS: Record<string, string> = {
    todo: 'bg-muted text-muted-foreground',
    in_progress: 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
    in_review: 'bg-yellow-500/15 text-yellow-700 dark:text-yellow-400',
    done: 'bg-green-500/15 text-green-700 dark:text-green-400',
};

function EditableText({ value, onSave, multiline = false }: {
    value: string; onSave: (v: string) => void; multiline?: boolean;
}) {
    const [editing, setEditing] = useState(false);
    const [draft, setDraft] = useState(value);

    if (!editing) {
        return (
            <div
                className="cursor-text rounded px-1 py-0.5 hover:bg-accent"
                onClick={() => { setDraft(value); setEditing(true); }}
            >
                {value || <span className="italic text-muted-foreground">Click to edit</span>}
            </div>
        );
    }

    function save() {
        setEditing(false);
        if (draft !== value) onSave(draft);
    }

    if (multiline) {
        return (
            <textarea
                className="w-full rounded-md border border-ring bg-background p-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                onBlur={save}
                autoFocus
                rows={6}
            />
        );
    }

    return (
        <input
            className="w-full rounded-md border border-ring bg-background px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
            value={draft}
            onChange={(e) => setDraft(e.target.value)}
            onBlur={save}
            onKeyDown={(e) => { if (e.key === 'Enter') save(); if (e.key === 'Escape') setEditing(false); }}
            autoFocus
        />
    );
}

function CommentItem({ comment, currentUserId, issueUrl }: {
    comment: Comment; currentUserId: number; issueUrl: string;
}) {
    const [editing, setEditing] = useState(false);
    const { data, setData, patch, processing } = useForm({ content: comment.content });

    function save() {
        patch(`${issueUrl}/comments/${comment.id}`, {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    }

    return (
        <div className="flex gap-3">
            <div className="flex size-8 flex-none items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground">
                {comment.user.name.slice(0, 2).toUpperCase()}
            </div>
            <div className="flex-1 min-w-0">
                <div className="mb-1 flex flex-wrap items-center gap-2">
                    <span className="text-sm font-medium">{comment.user.name}</span>
                    <span className="text-xs text-muted-foreground">
                        {new Date(comment.created_at).toLocaleString()}
                    </span>
                    {comment.user.id === currentUserId && (
                        <div className="ml-auto flex gap-2">
                            <button className="text-xs text-muted-foreground hover:text-foreground" onClick={() => setEditing(e => !e)}>
                                {editing ? 'Cancel' : 'Edit'}
                            </button>
                            <button
                                className="text-xs text-muted-foreground hover:text-destructive"
                                onClick={() => router.delete(`${issueUrl}/comments/${comment.id}`, { preserveScroll: true })}
                            >
                                Delete
                            </button>
                        </div>
                    )}
                </div>
                {editing ? (
                    <div className="space-y-2">
                        <textarea
                            className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            value={data.content}
                            onChange={(e) => setData('content', e.target.value)}
                            rows={3}
                        />
                        <Button size="sm" onClick={save} disabled={processing}>Save</Button>
                    </div>
                ) : (
                    <p className="whitespace-pre-wrap text-sm">{comment.content}</p>
                )}
            </div>
        </div>
    );
}

const FIELD_LABELS: Record<string, string> = {
    title: 'title', type: 'type', priority: 'priority', status: 'status',
    story_points: 'story points', assignee: 'assignee', sprint: 'sprint',
};

function historyActionText(h: IssueHistory): string {
    if (h.action === 'created') return 'created this issue';
    if (h.action === 'deleted') return 'deleted this issue';
    if (h.field === 'description') return 'updated the description';
    if (h.field === 'checklist') return 'updated the checklist';
    return `changed ${FIELD_LABELS[h.field ?? ''] ?? h.field}`;
}

function wordDiffExcerpt(a: string, b: string, context = 4): string {
    const wa = a.split(/\s+/);
    const wb = b.split(/\s+/);
    let lo = 0;
    while (lo < wa.length && lo < wb.length && wa[lo] === wb[lo]) lo++;
    let hi_a = wa.length - 1, hi_b = wb.length - 1;
    while (hi_a > lo && hi_b > lo && wa[hi_a] === wb[hi_b]) { hi_a--; hi_b--; }
    const start = Math.max(0, lo - context);
    const end_a = Math.min(wa.length - 1, hi_a + context);
    const prefix = start > 0 ? '…' : '';
    const suffix = end_a < wa.length - 1 ? '…' : '';
    return prefix + wa.slice(start, end_a + 1).join(' ') + suffix;
}

const LONG = 50;

function displayValue(val: string | null, other: string | null): string {
    if (!val) return '—';
    if (val.length <= LONG) return val;
    if (other) return wordDiffExcerpt(val, other);
    return val.slice(0, LONG).trimEnd() + '…';
}

function HistoryFeed({ histories }: { histories: IssueHistory[] }) {
    const [open, setOpen] = useState(false);
    if (histories.length === 0) return null;
    return (
        <div>
            <button
                type="button"
                className="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground"
                onClick={() => setOpen(v => !v)}
            >
                <Clock className="size-3.5" />
                History
                <span className="ml-1 rounded-full bg-muted px-1.5 py-0.5 text-xs font-medium">
                    {histories.length}
                </span>
                <span className="ml-auto text-xs font-normal">{open ? '▲' : '▼'}</span>
            </button>
            {open && (
                <ul className="mt-3 space-y-3">
                    {histories.map((h) => {
                        const hasValues = h.old_value !== null || h.new_value !== null;
                        const oldDisplay = h.old_value !== null ? displayValue(h.old_value, h.new_value) : null;
                        const newDisplay = h.new_value !== null ? displayValue(h.new_value, h.old_value) : null;
                        return (
                            <li key={h.id} className="flex items-start gap-2 text-sm">
                                <span className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-muted text-[9px] font-bold text-muted-foreground">
                                    {h.user.name.slice(0, 1).toUpperCase()}
                                </span>
                                <div className="min-w-0 flex-1">
                                    <div>
                                        <span className="font-medium">{h.user.name}</span>
                                        <span className="text-muted-foreground"> {historyActionText(h)}</span>
                                        <span className="ml-2 text-xs text-muted-foreground/70">
                                            {new Date(h.created_at).toLocaleString()}
                                        </span>
                                    </div>
                                    {hasValues && (
                                        <div className="mt-0.5 flex flex-wrap items-center gap-1 text-xs">
                                            {oldDisplay !== null && (
                                                <span className="rounded bg-red-500/10 px-1.5 py-0.5 text-red-600 line-through dark:text-red-400">
                                                    {oldDisplay}
                                                </span>
                                            )}
                                            {oldDisplay !== null && newDisplay !== null && (
                                                <span className="text-muted-foreground">→</span>
                                            )}
                                            {newDisplay !== null && (
                                                <span className="rounded bg-green-500/10 px-1.5 py-0.5 text-green-700 dark:text-green-400">
                                                    {newDisplay}
                                                </span>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </li>
                        );
                    })}
                </ul>
            )}
        </div>
    );
}

export default function IssuePage({ project, issue, members, sprints }: Props) {
    const { currentTeam, auth } = usePage().props as {
        currentTeam: { slug: string };
        auth: { user: { id: number } };
    };
    const baseUrl = `/${currentTeam.slug}/projects/${project.id}`;
    const issueUrl = `${baseUrl}/issues/${issue.id}`;
    const lastView = localStorage.getItem(`noticket_view_${project.id}`) ?? 'backlog';
    const backHref = `${baseUrl}/${lastView}`;
    const backLabel = lastView === 'board' ? 'Back to Board' : 'Back to Backlog';

    const [checklist, setChecklist] = useState<ChecklistItem[]>(issue.checklist ?? []);
    const [checklistVisible, setChecklistVisible] = useState(issue.checklist.length > 0);

    function updateChecklist(items: ChecklistItem[]) {
        setChecklist(items);
        router.patch(issueUrl, { checklist: items }, { preserveScroll: true });
    }

    const {
        data: commentData, setData: setCommentData,
        post: postComment, processing: commentProcessing, reset: resetComment,
    } = useForm({ content: '' });

    function patchField(field: string, value: unknown) {
        router.patch(issueUrl, { [field]: value }, { preserveScroll: true });
    }

    function deleteIssue() {
        if (!confirm('Delete this issue?')) return;
        router.delete(issueUrl, {
            onSuccess: () => router.visit(backHref),
        });
    }

    function submitComment(e: React.FormEvent) {
        e.preventDefault();
        postComment(`${issueUrl}/comments`, {
            preserveScroll: true,
            onSuccess: () => resetComment(),
        });
    }

    return (
        <>
            <Head title={`${issue.issue_key} – ${issue.title}`} />

            <div className="relative flex flex-1 flex-col">

                {/* Dot grid */}
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

                {/* ── Hero header ── */}
                <header className="relative z-10 border-b border-border bg-card/70 px-4 py-4 backdrop-blur-sm sm:px-6 sm:py-5">

                    {/* Nav row */}
                    <div className="mb-3 flex items-center justify-between">
                        <Link
                            href={backHref}
                            className="flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <ArrowLeft className="size-4" />
                            <span className="hidden sm:inline">{backLabel}</span>
                            <span className="sm:hidden">{lastView === 'board' ? 'Board' : 'Backlog'}</span>
                        </Link>
                        <Button
                            size="sm"
                            variant="ghost"
                            className="text-muted-foreground hover:text-destructive"
                            onClick={deleteIssue}
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    </div>

                    {/* Issue meta row */}
                    <div className="mb-2 flex flex-wrap items-center gap-2">
                        <IssueTypeIcon type={issue.type} />
                        <span className="font-mono text-sm font-medium text-muted-foreground">{issue.issue_key}</span>
                        <span className="text-muted-foreground/50">·</span>
                        <span className="text-sm text-muted-foreground">{project.name}</span>
                        <Badge
                            variant="outline"
                            className={`ml-auto text-xs ${STATUS_COLORS[issue.status]}`}
                        >
                            {STATUS_LABELS[issue.status]}
                        </Badge>
                    </div>

                    {/* Title */}
                    <h1 className="text-xl font-bold leading-snug sm:text-2xl">
                        <EditableText value={issue.title} onSave={(v) => patchField('title', v)} />
                    </h1>

                    {/* Compact metadata strip — visible only on mobile */}
                    <div className="mt-3 flex flex-wrap items-center gap-2 lg:hidden">
                        <Badge variant="outline" className="gap-1 text-xs">
                            <PriorityIcon priority={issue.priority} />
                            {priorityLabel(issue.priority)}
                        </Badge>
                        {issue.assignee && (
                            <Badge variant="outline" className="gap-1.5 text-xs">
                                <span className="flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-bold text-primary-foreground">
                                    {issue.assignee.name.slice(0, 1).toUpperCase()}
                                </span>
                                {issue.assignee.name}
                            </Badge>
                        )}
                    </div>
                </header>

                {/* ── Content ── */}
                <div className="relative z-10 flex-1 p-4 sm:p-6">
                    <div className="grid gap-6 lg:grid-cols-[1fr_280px]">

                        {/* ── Left: main content ── */}
                        <div className="space-y-6">

                            <div>
                                <p className="mb-1 text-xs font-medium text-muted-foreground">Description</p>
                                <div className="rounded-md bg-muted/30 p-2 text-sm">
                                    <EditableText
                                        value={issue.description ?? ''}
                                        onSave={(v) => patchField('description', v)}
                                        multiline
                                    />
                                </div>
                            </div>

                            <div>
                                {checklistVisible ? (
                                    <IssueChecklist items={checklist} onChange={updateChecklist} />
                                ) : (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="text-muted-foreground"
                                        onClick={() => setChecklistVisible(true)}
                                    >
                                        <CheckSquare className="size-4" />
                                        Add checklist
                                    </Button>
                                )}
                            </div>

                            <HistoryFeed histories={issue.histories} />

                            <div>
                                <p className="mb-3 text-sm font-semibold">
                                    Comments ({issue.comments.length})
                                </p>
                                <div className="space-y-4">
                                    {issue.comments.map((c) => (
                                        <CommentItem
                                            key={c.id}
                                            comment={c}
                                            currentUserId={auth.user.id}
                                            issueUrl={issueUrl}
                                        />
                                    ))}
                                </div>
                                <Separator className="my-4" />
                                <form onSubmit={submitComment} className="space-y-2">
                                    <textarea
                                        className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                                        rows={3}
                                        placeholder="Add a comment..."
                                        value={commentData.content}
                                        onChange={(e) => setCommentData('content', e.target.value)}
                                        required
                                    />
                                    <Button type="submit" size="sm" disabled={commentProcessing}>
                                        Add Comment
                                    </Button>
                                </form>
                            </div>
                        </div>

                        {/* ── Right: details sidebar ── */}
                        <div className="h-fit space-y-4 rounded-xl border border-border bg-card/80 p-4 backdrop-blur-sm">
                            <h3 className="text-sm font-semibold">Details</h3>
                            <Separator />

                            <div className="space-y-3">
                                <div>
                                    <p className="mb-1 text-xs text-muted-foreground">Status</p>
                                    <Select value={issue.status} onValueChange={(v) => patchField('status', v)}>
                                        <SelectTrigger className="h-8 text-xs"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="todo">To Do</SelectItem>
                                            <SelectItem value="in_progress">In Progress</SelectItem>
                                            <SelectItem value="in_review">In Review</SelectItem>
                                            <SelectItem value="done">Done</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <p className="mb-1 text-xs text-muted-foreground">Type</p>
                                    <Select value={issue.type} onValueChange={(v) => patchField('type', v)}>
                                        <SelectTrigger className="h-8 text-xs"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="task">Task</SelectItem>
                                            <SelectItem value="bug">Bug</SelectItem>
                                            <SelectItem value="story">Story</SelectItem>
                                            <SelectItem value="epic">Epic</SelectItem>
                                            <SelectItem value="subtask">Subtask</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <p className="mb-1 text-xs text-muted-foreground">Priority</p>
                                    <Select value={issue.priority} onValueChange={(v) => patchField('priority', v)}>
                                        <SelectTrigger className="h-8 text-xs">
                                            <SelectValue>
                                                <div className="flex items-center gap-1.5">
                                                    <PriorityIcon priority={issue.priority} />
                                                    {priorityLabel(issue.priority)}
                                                </div>
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="lowest">Lowest</SelectItem>
                                            <SelectItem value="low">Low</SelectItem>
                                            <SelectItem value="medium">Medium</SelectItem>
                                            <SelectItem value="high">High</SelectItem>
                                            <SelectItem value="highest">Highest</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <div className="mb-1 flex items-center justify-between">
                                        <p className="text-xs text-muted-foreground">Assignee</p>
                                        {members.some((m) => m.id === auth.user.id) && issue.assignee?.id !== auth.user.id && (
                                            <button
                                                type="button"
                                                className="text-xs text-muted-foreground hover:text-foreground"
                                                onClick={() => patchField('assignee_id', auth.user.id)}
                                            >
                                                Assign to me
                                            </button>
                                        )}
                                    </div>
                                    <Select
                                        value={issue.assignee?.id?.toString() ?? 'none'}
                                        onValueChange={(v) => patchField('assignee_id', v === 'none' ? null : Number(v))}
                                    >
                                        <SelectTrigger className="h-8 text-xs">
                                            <SelectValue placeholder="Unassigned" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Unassigned</SelectItem>
                                            {members.map((m) => (
                                                <SelectItem key={m.id} value={String(m.id)}>{m.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <p className="mb-1 text-xs text-muted-foreground">Sprint</p>
                                    <Select
                                        value={issue.sprint_id?.toString() ?? 'none'}
                                        onValueChange={(v) => patchField('sprint_id', v === 'none' ? null : Number(v))}
                                    >
                                        <SelectTrigger className="h-8 text-xs">
                                            <SelectValue placeholder="Backlog" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Backlog</SelectItem>
                                            {sprints.map((s) => (
                                                <SelectItem key={s.id} value={String(s.id)}>{s.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <p className="mb-1 text-xs text-muted-foreground">Story Points</p>
                                    <input
                                        type="number"
                                        className="h-8 w-full rounded-md border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                        defaultValue={issue.story_points ?? ''}
                                        min={1}
                                        max={100}
                                        placeholder="—"
                                        onBlur={(e) => {
                                            const v = e.target.value ? Number(e.target.value) : null;
                                            if (v !== issue.story_points) patchField('story_points', v);
                                        }}
                                    />
                                </div>

                                <Separator />

                                <div className="group/meta space-y-1 text-xs text-muted-foreground">
                                    <p>Reporter: <span className="text-foreground">{issue.reporter?.name ?? '—'}</span></p>
                                    <p>
                                        Created:{' '}
                                        <span className="text-foreground">{new Date(issue.created_at).toLocaleDateString()}</span>
                                        <span className="text-foreground/0 transition-colors duration-150 group-hover/meta:text-foreground">
                                            {' '}{new Date(issue.created_at).toLocaleTimeString()}
                                        </span>
                                    </p>
                                    <p>
                                        Updated:{' '}
                                        <span className="text-foreground">{new Date(issue.updated_at).toLocaleDateString()}</span>
                                        <span className="text-foreground/0 transition-colors duration-150 group-hover/meta:text-foreground">
                                            {' '}{new Date(issue.updated_at).toLocaleTimeString()}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </>
    );
}
