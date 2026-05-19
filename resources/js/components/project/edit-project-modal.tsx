import { useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Project } from '@/types';

interface Props {
    project: Project | null;
    onClose: () => void;
}

export function EditProjectModal({ project, onClose }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const { data, setData, patch, processing, errors, reset } = useForm({
        name: '',
        description: '',
    });

    useEffect(() => {
        if (project) {
            setData({ name: project.name, description: project.description ?? '' });
        }
    }, [project?.id]);

    function close() {
        onClose();
        reset();
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        if (!project) return;
        patch(`/${currentTeam.slug}/projects/${project.id}`, {
            onSuccess: close,
        });
    }

    return (
        <Dialog open={!!project} onOpenChange={(v) => { if (!v) close(); }}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Edit Project</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-1.5">
                        <Label htmlFor="edit-proj-name">Name</Label>
                        <Input
                            id="edit-proj-name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="My Project"
                            required
                        />
                        {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
                    </div>

                    <div className="space-y-1.5">
                        <Label htmlFor="edit-proj-desc">Description</Label>
                        <textarea
                            id="edit-proj-desc"
                            className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                            rows={3}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="What is this project about?"
                        />
                        {errors.description && <p className="text-xs text-destructive">{errors.description}</p>}
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={close}>Cancel</Button>
                        <Button type="submit" disabled={processing}>Save Changes</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
