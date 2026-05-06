import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, CheckSquare, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { IssueChecklist } from '@/components/project/issue-checklist';
import { IssueTypeIcon } from '@/components/project/issue-type-icon';
import { PriorityIcon, priorityLabel } from '@/components/project/priority-icon';
import type { ChecklistItem, Comment, IssueDetail, IssueUser, Project, Sprint } from '@/types';

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

function EditableText({ value, onSave, multiline = false }: { value: string; onSave: (v: string) => void; multiline?: boolean }) {
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

function CommentItem({ comment, currentUserId, issueUrl }: { comment: Comment; currentUserId: number; issueUrl: string }) {
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
            <div className="flex-1">
                <div className="mb-1 flex items-center gap-2">
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

export default function IssuePage({ project, issue, members, sprints }: Props) {
    const { currentTeam, auth } = usePage().props as { currentTeam: { slug: string }; auth: { user: { id: number } } };
    const baseUrl = `/${currentTeam.slug}/projects/${project.id}`;
    const issueUrl = `${baseUrl}/issues/${issue.id}`;

    const [checklist, setChecklist] = useState<ChecklistItem[]>(issue.checklist ?? []);
    const [checklistVisible, setChecklistVisible] = useState(issue.checklist.length > 0);

    function updateChecklist(items: ChecklistItem[]) {
        setChecklist(items);
        router.patch(issueUrl, { checklist: items }, { preserveScroll: true });
    }

    const { data: commentData, setData: setCommentData, post: postComment, processing: commentProcessing, reset: resetComment } = useForm({ content: '' });

    function patchField(field: string, value: unknown) {
        router.patch(issueUrl, { [field]: value }, { preserveScroll: true });
    }

    function deleteIssue() {
        if (!confirm('Delete this issue?')) return;
        router.delete(issueUrl, {
            onSuccess: () => router.visit(`${baseUrl}/backlog`),
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

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <Link
                        href={`${baseUrl}/backlog`}
                        className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" /> Back to Backlog
                    </Link>
                    <Button size="sm" variant="ghost" className="text-muted-foreground hover:text-destructive" onClick={deleteIssue}>
                        <Trash2 className="size-4" />
                    </Button>
                </div>

                <div className="grid gap-8 lg:grid-cols-[1fr_280px]">
                    <div className="space-y-6">
                        <div className="flex items-center gap-2">
                            <IssueTypeIcon type={issue.type} />
                            <span className="text-sm font-medium text-muted-foreground">{issue.issue_key}</span>
                            <Badge variant="outline" className={`text-xs ${STATUS_COLORS[issue.status]}`}>
                                {STATUS_LABELS[issue.status]}
                            </Badge>
                        </div>

                        <div>
                            <p className="mb-1 text-xs font-medium text-muted-foreground">Title</p>
                            <h2 className="text-xl font-bold">
                                <EditableText value={issue.title} onSave={(v) => patchField('title', v)} />
                            </h2>
                        </div>

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

                        <div>
                            <p className="mb-3 text-sm font-semibold">Comments ({issue.comments.length})</p>
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
                                <Button type="submit" size="sm" disabled={commentProcessing}>Add Comment</Button>
                            </form>
                        </div>
                    </div>

                    <div className="space-y-4 rounded-xl border border-border bg-card p-4">
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
                                <p className="mb-1 text-xs text-muted-foreground">Assignee</p>
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

                            <div className="space-y-1 text-xs text-muted-foreground">
                                <p>Reporter: <span className="text-foreground">{issue.reporter?.name ?? '—'}</span></p>
                                <p>Created: <span className="text-foreground">{new Date(issue.created_at).toLocaleDateString()}</span></p>
                                <p>Updated: <span className="text-foreground">{new Date(issue.updated_at).toLocaleDateString()}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
