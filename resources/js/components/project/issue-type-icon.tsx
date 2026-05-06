import { BookMarked, Bug, Circle, Layers, Zap } from 'lucide-react';
import type { IssueType } from '@/types';

const config: Record<IssueType, { icon: React.ElementType; color: string }> = {
    epic: { icon: Zap, color: 'text-purple-500' },
    story: { icon: BookMarked, color: 'text-green-500' },
    task: { icon: Circle, color: 'text-blue-500' },
    bug: { icon: Bug, color: 'text-red-500' },
    subtask: { icon: Layers, color: 'text-gray-500' },
};

export function IssueTypeIcon({ type, className = 'size-4' }: { type: IssueType; className?: string }) {
    const { icon: Icon, color } = config[type];
    return <Icon className={`${className} ${color}`} />;
}
