<?php

namespace Tests\Unit\Data;

use App\Data\CommentDTO;
use App\Models\Comment;
use App\Models\User;
use Tests\TestCase;

class CommentDTOTest extends TestCase
{
    public function test_from_model_maps_all_fields(): void
    {
        $user = (new User())->forceFill(['id' => 3, 'name' => 'Bob']);

        $comment = (new Comment())->forceFill([
            'id'         => 7,
            'content'    => 'Hello world',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 10:00:00',
        ]);
        $comment->setRelation('user', $user);

        $dto = CommentDTO::fromModel($comment);

        $this->assertSame(7, $dto->id);
        $this->assertSame('Hello world', $dto->content);
        $this->assertSame(3, $dto->user->id);
        $this->assertSame('Bob', $dto->user->name);
        $this->assertSame('2024-01-01', $dto->createdAt->toDateString());
        $this->assertSame('2024-01-02', $dto->updatedAt->toDateString());
    }
}
