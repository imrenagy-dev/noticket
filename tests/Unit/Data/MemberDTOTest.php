<?php

namespace Tests\Unit\Data;

use App\Data\MemberDTO;
use App\Models\User;
use Tests\TestCase;

class MemberDTOTest extends TestCase
{
    public function test_from_model_maps_id_and_name(): void
    {
        $user = (new User())->forceFill(['id' => 5, 'name' => 'Alice']);

        $dto = MemberDTO::fromModel($user);

        $this->assertSame(5, $dto->id);
        $this->assertSame('Alice', $dto->name);
    }
}
