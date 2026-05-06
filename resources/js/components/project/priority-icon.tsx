import { ArrowDown, ArrowUp, ChevronsDown, ChevronsUp, Minus } from 'lucide-react';
import type { IssuePriority } from '@/types';

const config: Record<IssuePriority, { icon: React.ElementType; color: string; label: string }> = {
    lowest: { icon: ChevronsDown, color: 'text-gray-400', label: 'Lowest' },
    low: { icon: ArrowDown, color: 'text-blue-400', label: 'Low' },
    medium: { icon: Minus, color: 'text-yellow-500', label: 'Medium' },
    high: { icon: ArrowUp, color: 'text-orange-500', label: 'High' },
    highest: { icon: ChevronsUp, color: 'text-red-500', label: 'Highest' },
};

export function PriorityIcon({ priority, className = 'size-3.5' }: { priority: IssuePriority; className?: string }) {
    const { icon: Icon, color } = config[priority];
    return <Icon className={`${className} ${color}`} />;
}

export function priorityLabel(p: IssuePriority) {
    return config[p].label;
}
