import { useState } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { ChecklistItem } from '@/types/projects';

interface Props {
    items: ChecklistItem[];
    onChange: (items: ChecklistItem[]) => void;
}

export function IssueChecklist({ items, onChange }: Props) {
    const [newText, setNewText] = useState('');

    const done = items.filter((i) => i.done).length;
    const progress = items.length ? Math.round((done / items.length) * 100) : 0;

    function toggle(id: string) {
        onChange(items.map((i) => (i.id === id ? { ...i, done: !i.done } : i)));
    }

    function remove(id: string) {
        onChange(items.filter((i) => i.id !== id));
    }

    function add() {
        const text = newText.trim();
        if (!text) return;
        onChange([...items, { id: crypto.randomUUID(), text, done: false }]);
        setNewText('');
    }

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <p className="text-sm font-semibold">
                    Checklist
                    {items.length > 0 && (
                        <span className="ml-2 text-xs font-normal text-muted-foreground">
                            {done}/{items.length}
                        </span>
                    )}
                </p>
            </div>

            {items.length > 0 && (
                <div className="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        className="h-full rounded-full bg-green-500 transition-all duration-300"
                        style={{ width: `${progress}%` }}
                    />
                </div>
            )}

            <div className="space-y-1.5">
                {items.map((item) => (
                    <div key={item.id} className="group flex items-center gap-2">
                        <Checkbox
                            id={`checklist-${item.id}`}
                            checked={item.done}
                            onCheckedChange={() => toggle(item.id)}
                        />
                        <label
                            htmlFor={`checklist-${item.id}`}
                            className={`flex-1 cursor-pointer text-sm ${item.done ? 'text-muted-foreground line-through' : ''}`}
                        >
                            {item.text}
                        </label>
                        <button
                            className="opacity-0 transition-opacity group-hover:opacity-100 text-muted-foreground hover:text-destructive"
                            onClick={() => remove(item.id)}
                            aria-label="Remove item"
                        >
                            <Trash2 className="size-3.5" />
                        </button>
                    </div>
                ))}
            </div>

            <div className="flex gap-2">
                <Input
                    value={newText}
                    onChange={(e) => setNewText(e.target.value)}
                    onKeyDown={(e) => { if (e.key === 'Enter') add(); }}
                    placeholder="Add an item..."
                    className="h-8 text-sm"
                />
                <Button size="sm" variant="outline" onClick={add} disabled={!newText.trim()}>
                    <Plus className="size-4" />
                </Button>
            </div>
        </div>
    );
}
