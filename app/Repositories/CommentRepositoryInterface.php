<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Issue;

interface CommentRepositoryInterface
{
    public function create(Issue $issue, string $content, int $userId): Comment;

    public function update(Comment $comment, string $content): Comment;

    public function delete(Comment $comment): void;
}
