import { router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { FormEvent, KeyboardEvent, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { ChecklistItem, Issue, IssueUser, Sprint } from '@/types';

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
    const { currentTeam, auth } = usePage().props as { currentTeam: { slug: string }; auth: { user: { id: number } } };
    const me = members.find((m) => m.id === auth.user.id);
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
    const [checklist, setChecklist] = useState<ChecklistItem[]>((issue as any)?.checklist ?? []);
    const [newItemText, setNewItemText] = useState('');
    const [editingId, setEditingId] = useState<string | null>(null);
    const [editDraft, setEditDraft] = useState('');
    const newItemRef = useRef<HTMLInputElement>(null);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    function addItem() {
        const text = newItemText.trim();
        if (!text) return;
        const id = crypto.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`;
        setChecklist(prev => [...prev, { id, text, done: false }]);
        setNewItemText('');
        newItemRef.current?.focus();
    }

    function toggleItem(id: string) {
        setChecklist(prev => prev.map(i => i.id === id ? { ...i, done: !i.done } : i));
    }

    function removeItem(id: string) {
        setChecklist(prev => prev.filter(i => i.id !== id));
    }

    function startEdit(item: ChecklistItem) {
        setEditingId(item.id);
        setEditDraft(item.text);
    }

    function commitEdit(id: string) {
        const text = editDraft.trim();
        if (text) setChecklist(prev => prev.map(i => i.id === id ? { ...i, text } : i));
        setEditingId(null);
    }

    function handleNewItemKey(e: KeyboardEvent<HTMLInputElement>) {
        if (e.key === 'Enter') { e.preventDefault(); addItem(); }
    }

    function set(field: string, value: string) {
        setForm(prev => ({ ...prev, [field]: value }));
    }

    function reset() {
        setForm(makeEmpty());
        setChecklist((issue as any)?.checklist ?? []);
        setNewItemText('');
        setEditingId(null);
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
            checklist: checklist.length ? checklist : null,
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
            <DialogContent>
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

                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
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

                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
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
                        <div className="flex items-center justify-between">
                            <Label>Assignee</Label>
                            {me && form.assignee_id !== String(me.id) && (
                                <button
                                    type="button"
                                    className="text-xs text-muted-foreground hover:text-foreground"
                                    onClick={() => set('assignee_id', String(me.id))}
                                >
                                    Assign to me
                                </button>
                            )}
                        </div>
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

                    <div className="space-y-2">
                        <Label>Checklist</Label>
                        {checklist.length > 0 && (
                            <ul className="space-y-1">
                                {checklist.map((item) => (
                                    <li key={item.id} className="group flex items-center gap-2">
                                        <Checkbox
                                            checked={item.done}
                                            onCheckedChange={() => toggleItem(item.id)}
                                            className="shrink-0"
                                        />
                                        {editingId === item.id ? (
                                            <Input
                                                autoFocus
                                                value={editDraft}
                                                onChange={(e) => setEditDraft(e.target.value)}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter') { e.preventDefault(); commitEdit(item.id); }
                                                    if (e.key === 'Escape') setEditingId(null);
                                                }}
                                                onBlur={() => commitEdit(item.id)}
                                                className="h-7 flex-1 text-sm"
                                            />
                                        ) : (
                                            <span
                                                className={`flex-1 rounded px-1 py-0.5 text-sm ${item.done ? 'line-through text-muted-foreground' : ''}`}
                                            >
                                                {item.text}
                                            </span>
                                        )}
                                        {!item.done && editingId !== item.id && (
                                            <button
                                                type="button"
                                                className="shrink-0 opacity-0 transition-opacity group-hover:opacity-100 text-muted-foreground hover:text-foreground"
                                                onClick={() => startEdit(item)}
                                                aria-label="Edit item"
                                            >
                                                <Pencil className="size-3.5" />
                                            </button>
                                        )}
                                        <button
                                            type="button"
                                            className="shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                                            onClick={() => removeItem(item.id)}
                                        >
                                            <Trash2 className="size-3.5 text-muted-foreground hover:text-destructive" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="flex items-center gap-2">
                            <Input
                                ref={newItemRef}
                                value={newItemText}
                                onChange={(e) => setNewItemText(e.target.value)}
                                onKeyDown={handleNewItemKey}
                                placeholder="Add an item..."
                                className="h-8 text-sm"
                            />
                            <Button type="button" size="sm" variant="outline" className="shrink-0 px-2" onClick={addItem}>
                                <Plus className="size-3.5" />
                            </Button>
                        </div>
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
