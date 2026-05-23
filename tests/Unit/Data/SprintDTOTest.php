<?php

namespace Tests\Unit\Data;

use App\Data\SprintDTO;
use App\Enums\SprintStatus;
use App\Models\Sprint;
use Tests\TestCase;

class SprintDTOTest extends TestCase
{
    public function test_from_model_maps_scalar_fields(): void
    {
        $sprint = (new Sprint())->forceFill([
            'id'     => 1,
            'name'   => 'Sprint 1',
            'goal'   => 'Ship it',
            'status' => 'planned',
        ]);

        $dto = SprintDTO::fromModel($sprint);

        $this->assertSame(1, $dto->id);
        $this->assertSame('Sprint 1', $dto->name);
        $this->assertSame('Ship it', $dto->goal);
        $this->assertSame(SprintStatus::Planned, $dto->status);
        $this->assertNull($dto->startsAt);
        $this->assertNull($dto->endsAt);
    }

    public function test_from_model_maps_active_status(): void
    {
        $sprint = (new Sprint())->forceFill([
            'id'     => 2,
            'name'   => 'Sprint 2',
            'goal'   => null,
            'status' => 'active',
        ]);

        $dto = SprintDTO::fromModel($sprint);

        $this->assertSame(SprintStatus::Active, $dto->status);
    }

    public function test_from_model_maps_dates(): void
    {
        $sprint = (new Sprint())->forceFill([
            'id'        => 3,
            'name'      => 'Sprint 3',
            'goal'      => null,
            'status'    => 'planned',
            'starts_at' => '2024-01-01',
            'ends_at'   => '2024-01-14',
        ]);

        $dto = SprintDTO::fromModel($sprint);

        $this->assertNotNull($dto->startsAt);
        $this->assertNotNull($dto->endsAt);
        $this->assertSame('2024-01-01', $dto->startsAt->toDateString());
        $this->assertSame('2024-01-14', $dto->endsAt->toDateString());
    }
}
