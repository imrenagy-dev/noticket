<?php

namespace Tests\Unit\Services;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\CommentRepositoryInterface;
use App\Services\CommentService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    private CommentRepositoryInterface|MockInterface $repo;
    private CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo    = Mockery::mock(CommentRepositoryInterface::class);
        $this->service = new CommentService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_throws_403_when_requester_is_not_owner(): void
    {
        $comment = (new Comment())->forceFill(['id' => 1, 'user_id' => 5, 'content' => 'old']);

        $this->assertHttpException(403, fn () => $this->service->update($comment, 'new', requestingUserId: 99));
    }

    public function test_delete_throws_403_when_requester_is_not_owner(): void
    {
        $comment = (new Comment())->forceFill(['id' => 1, 'user_id' => 5, 'content' => 'old']);

        $this->assertHttpException(403, fn () => $this->service->delete($comment, requestingUserId: 99));
    }

    public function test_update_delegates_to_repo_when_owner(): void
    {
        $user = (new User())->forceFill(['id' => 5, 'name' => 'Alice']);

        $original = (new Comment())->forceFill(['id' => 1, 'user_id' => 5, 'content' => 'old']);
        $updated  = (new Comment())->forceFill([
            'id'         => 1,
            'user_id'    => 5,
            'content'    => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $updated->setRelation('user', $user);

        $this->repo->shouldReceive('update')
            ->once()
            ->with($original, 'new')
            ->andReturn($updated);

        $dto = $this->service->update($original, 'new', requestingUserId: 5);

        $this->assertSame(1, $dto->id);
        $this->assertSame('new', $dto->content);
        $this->assertSame(5, $dto->user->id);
    }

    public function test_delete_delegates_to_repo_when_owner(): void
    {
        $comment = (new Comment())->forceFill(['id' => 1, 'user_id' => 5, 'content' => 'bye']);

        $this->repo->shouldReceive('delete')->once()->with($comment);

        $this->service->delete($comment, requestingUserId: 5);
    }

    private function assertHttpException(int $status, callable $action): void
    {
        try {
            $action();
            $this->fail("Expected HTTP {$status} exception was not thrown.");
        } catch (HttpException $e) {
            $this->assertSame($status, $e->getStatusCode());
        }
    }
}
