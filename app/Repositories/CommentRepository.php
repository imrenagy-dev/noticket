<?php

namespace App\Repositories;

use App\Repositories\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\Issue;

class CommentRepository implements CommentRepositoryInterface
{
    public function create(Issue $issue, string $content, int $userId): Comment
    {
        $comment = $issue->comments()->create([
            'content' => $content,
            'user_id' => $userId,
        ]);

        $comment->load('user');

        return $comment;
    }

    public function update(Comment $comment, string $content): Comment
    {
        $comment->update(['content' => $content]);
        $comment->load('user');

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
