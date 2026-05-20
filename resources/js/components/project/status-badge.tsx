import type { IssueStatus } from '@/types';

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

export function StatusBadge({ status, className = '' }: { status: IssueStatus; className?: string }) {
    return (
        <span className={`inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium ${STATUS_COLORS[status]} ${className}`}>
            {STATUS_LABELS[status]}
        </span>
    );
}
