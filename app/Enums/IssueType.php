<?php

namespace App\Enums;

enum IssueType: string
{
    case Epic    = 'epic';
    case Story   = 'story';
    case Task    = 'task';
    case Bug     = 'bug';
    case Subtask = 'subtask';

    public function label(): string
    {
        return match ($this) {
            self::Epic    => 'Epic',
            self::Story   => 'Story',
            self::Task    => 'Task',
            self::Bug     => 'Bug',
            self::Subtask => 'Subtask',
        };
    }
}
