import { useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    open: boolean;
    onClose: () => void;
}

function generateKey(name: string): string {
    const words = name.trim().split(/\s+/);
    if (words.length === 1) {
        return words[0].slice(0, 4).toUpperCase();
    }
    return words.map(w => w[0]).join('').slice(0, 6).toUpperCase();
}

export function CreateProjectModal({ open, onClose }: Props) {
    const { currentTeam } = usePage().props as { currentTeam: { slug: string } };
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        key: '',
        description: '',
    });

    useEffect(() => {
        if (data.name) {
            setData('key', generateKey(data.name));
        }
    }, [data.name]);

    function submit(e: FormEvent) {
        e.preventDefault();
        post(`/${currentTeam.slug}/projects`, {
            onSuccess: () => { onClose(); reset(); },
        });
    }

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v) { onClose(); reset(); } }}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Create Project</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-1.5">
                        <Label htmlFor="proj-name">Name</Label>
                        <Input
                            id="proj-name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="My Project"
                            required
                        />
                        {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
                    </div>

                    <div className="space-y-1.5">
                        <Label htmlFor="proj-key">
                            Key
                            <span className="ml-1 text-xs text-muted-foreground">(2–10 uppercase letters)</span>
                        </Label>
                        <Input
                            id="proj-key"
                            value={data.key}
                            onChange={(e) => setData('key', e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, ''))}
                            placeholder="PROJ"
                            maxLength={10}
                            required
                        />
                        {errors.key && <p className="text-xs text-destructive">{errors.key}</p>}
                    </div>

                    <div className="space-y-1.5">
                        <Label htmlFor="proj-desc">Description</Label>
                        <textarea
                            id="proj-desc"
                            className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                            rows={3}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="What is this project about?"
                        />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => { onClose(); reset(); }}>Cancel</Button>
                        <Button type="submit" disabled={processing}>Create Project</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
