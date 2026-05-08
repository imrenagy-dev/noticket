export type IssueType = 'epic' | 'story' | 'task' | 'bug' | 'subtask';
export type IssueStatus = 'todo' | 'in_progress' | 'in_review' | 'done';
export type IssuePriority = 'lowest' | 'low' | 'medium' | 'high' | 'highest';
export type SprintStatus = 'planned' | 'active' | 'completed';

export type Project = {
    id: number;
    name: string;
    key: string;
    description: string | null;
    issue_count?: number;
    created_at?: string;
};

export type Sprint = {
    id: number;
    name: string;
    goal: string | null;
    status: SprintStatus;
    starts_at: string | null;
    ends_at: string | null;
};

export type SprintWithIssues = Sprint & {
    issues: Issue[];
};

export type IssueUser = {
    id: number;
    name: string;
    avatar?: string | null;
};

export type Issue = {
    id: number;
    number: number;
    issue_key: string;
    title: string;
    description?: string | null;
    type: IssueType;
    status: IssueStatus;
    priority: IssuePriority;
    story_points: number | null;
    sprint_id: number | null;
    sprint?: Sprint | null;
    reporter: IssueUser | null;
    assignee: IssueUser | null;
    board_order: number;
    backlog_order: number;
    created_at: string;
    updated_at: string;
};

export type ChecklistItem = {
    id: string;
    text: string;
    done: boolean;
};

export type Comment = {
    id: number;
    content: string;
    user: IssueUser;
    created_at: string;
    updated_at: string;
};

export type IssueHistory = {
    id: number;
    user: IssueUser;
    action: 'created' | 'updated' | 'deleted';
    field: string | null;
    old_value: string | null;
    new_value: string | null;
    created_at: string;
};

export type IssueDetail = Issue & {
    checklist: ChecklistItem[];
    comments: Comment[];
    histories: IssueHistory[];
};

export type BoardColumns = {
    todo: Issue[];
    in_progress: Issue[];
    in_review: Issue[];
    done: Issue[];
};
