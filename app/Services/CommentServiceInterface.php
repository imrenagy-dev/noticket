<?php

namespace App\Services;

use App\Data\CommentDTO;
use App\Models\Comment;
use App\Models\Issue;

interface CommentServiceInterface
{
    public function create(Issue $issue, string $content, int $userId): CommentDTO;

    public function update(Comment $comment, string $content, int $requestingUserId): CommentDTO;

    public function delete(Comment $comment, int $requestingUserId): void;
}
