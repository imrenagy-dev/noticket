<?php

namespace App\Data;

use App\Models\Comment;
use Carbon\CarbonImmutable;

readonly class CommentDTO
{
    public function __construct(
        public int             $id,
        public string          $content,
        public MemberDTO       $user,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    public static function fromModel(Comment $comment): self
    {
        return new self(
            id:        $comment->id,
            content:   $comment->content,
            user:      MemberDTO::fromModel($comment->user),
            createdAt: CarbonImmutable::instance($comment->created_at),
            updatedAt: CarbonImmutable::instance($comment->updated_at),
        );
    }
}
