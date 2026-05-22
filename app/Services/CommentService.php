<?php

namespace App\Services;

use App\Data\CommentDTO;
use App\Repositories\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\Issue;

class CommentService implements CommentServiceInterface
{
    public function __construct(private CommentRepositoryInterface $comments) {}

    public function create(Issue $issue, string $content, int $userId): CommentDTO
    {
        return CommentDTO::fromModel($this->comments->create($issue, $content, $userId));
    }

    public function update(Comment $comment, string $content, int $requestingUserId): CommentDTO
    {
        abort_if($comment->user_id !== $requestingUserId, 403);

        return CommentDTO::fromModel($this->comments->update($comment, $content));
    }

    public function delete(Comment $comment, int $requestingUserId): void
    {
        abort_if($comment->user_id !== $requestingUserId, 403);

        $this->comments->delete($comment);
    }
}
