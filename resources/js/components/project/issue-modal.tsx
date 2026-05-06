import { router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Issue, IssueUser, Sprint } from '@/types';

interface Props {
    open: boolean;
    onClose: () => void;
    projectId: number;
    members: IssueUser[];
    sprints: Pick<Sprint, 'id' | 'name' | 'status'>[];
    defaultSprintId?: number | null;
    defaultStatus?: string;
    issue?: Issue;
}

export function IssueModal({ open, onClose, projectId, members, sprints, defaultSprintId, defaultStatus, issue }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const isEdit = !!issue;
    const baseUrl = `/${currentTeam.slug}/projects/${projectId}`;

    const makeEmpty = () => ({
        title: issue?.title ?? '',
        type: issue?.type ?? 'task',
        priority: issue?.priority ?? 'medium',
        status: issue?.status ?? defaultStatus ?? 'todo',
        description: issue?.description ?? '',
        assignee_id: issue?.assignee?.id?.toString() ?? '',
        sprint_id: issue?.sprint_id?.toString() ?? defaultSprintId?.toString() ?? '',
        story_points: issue?.story_points?.toString() ?? '',
    });

    const [form, setForm] = useState(makeEmpty);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    function set(field: string, value: string) {
        setForm(prev => ({ ...prev, [field]: value }));
    }

    function reset() {
        setForm(makeEmpty());
        setErrors({});
    }

    function close() {
        onClose();
        reset();
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        const payload = {
            title: form.title,
            type: form.type,
            priority: form.priority,
            status: form.status,
            description: form.description || null,
            assignee_id: form.assignee_id ? Number(form.assignee_id) : null,
            sprint_id: form.sprint_id ? Number(form.sprint_id) : null,
            story_points: form.story_points ? Number(form.story_points) : null,
        };
        setProcessing(true);
        if (isEdit) {
            router.patch(`${baseUrl}/issues/${issue!.id}`, payload, {
                preserveScroll: true,
                onSuccess: () => { setProcessing(false); close(); },
                onError: (errs) => { setProcessing(false); setErrors(errs as Record<string, string>); },
            });
        } else {
            router.post(`${baseUrl}/issues`, payload, {
                preserveScroll: true,
                onSuccess: () => { setProcessing(false); close(); },
                onError: (errs) => { setProcessing(false); setErrors(errs as Record<string, string>); },
            });
        }
    }

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v) close(); }}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{isEdit ? 'Edit Issue' : 'Create Issue'}</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-1.5">
                        <Label htmlFor="issue-title">Title</Label>
                        <Input
                            id="issue-title"
                            value={form.title}
                            onChange={(e) => set('title', e.target.value)}
                            placeholder="Issue title"
                            required
                        />
                        {errors.title && <p className="text-xs text-destructive">{errors.title}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Type</Label>
                            <Select value={form.type} onValueChange={(v) => set('type', v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="task">Task</SelectItem>
                                    <SelectItem value="bug">Bug</SelectItem>
                                    <SelectItem value="story">Story</SelectItem>
                                    <SelectItem value="epic">Epic</SelectItem>
                                    <SelectItem value="subtask">Subtask</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-1.5">
                            <Label>Priority</Label>
                            <Select value={form.priority} onValueChange={(v) => set('priority', v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="lowest">Lowest</SelectItem>
                                    <SelectItem value="low">Low</SelectItem>
                                    <SelectItem value="medium">Medium</SelectItem>
                                    <SelectItem value="high">High</SelectItem>
                                    <SelectItem value="highest">Highest</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Status</Label>
                            <Select value={form.status} onValueChange={(v) => set('status', v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="todo">To Do</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="in_review">In Review</SelectItem>
                                    <SelectItem value="done">Done</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-1.5">
                            <Label>Story Points</Label>
                            <Input
                                type="number"
                                min={1}
                                max={100}
                                value={form.story_points}
                                onChange={(e) => set('story_points', e.target.value)}
                                placeholder="—"
                            />
                        </div>
                    </div>

                    <div className="space-y-1.5">
                        <Label>Assignee</Label>
                        <Select value={form.assignee_id || 'none'} onValueChange={(v) => set('assignee_id', v === 'none' ? '' : v)}>
                            <SelectTrigger><SelectValue placeholder="Unassigned" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Unassigned</SelectItem>
                                {members.map((m) => (
                                    <SelectItem key={m.id} value={String(m.id)}>{m.name}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-1.5">
                        <Label>Sprint</Label>
                        <Select value={form.sprint_id || 'none'} onValueChange={(v) => set('sprint_id', v === 'none' ? '' : v)}>
                            <SelectTrigger><SelectValue placeholder="Backlog" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Backlog</SelectItem>
                                {sprints.map((s) => (
                                    <SelectItem key={s.id} value={String(s.id)}>{s.name}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-1.5">
                        <Label htmlFor="issue-desc">Description</Label>
                        <textarea
                            id="issue-desc"
                            className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                            rows={3}
                            value={form.description}
                            onChange={(e) => set('description', e.target.value)}
                            placeholder="Add a description..."
                        />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={close}>Cancel</Button>
                        <Button type="submit" disabled={processing}>
                            {isEdit ? 'Save' : 'Create'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
