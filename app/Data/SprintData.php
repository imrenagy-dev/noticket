<?php

namespace App\Data;

use App\Enums\SprintStatus;
use App\Models\Sprint;
use Carbon\CarbonImmutable;

readonly class SprintData
{
    public function __construct(
        public int             $id,
        public string          $name,
        public ?string         $goal,
        public SprintStatus    $status,
        public ?CarbonImmutable $startsAt,
        public ?CarbonImmutable $endsAt,
    ) {}

    public static function fromModel(Sprint $sprint): self
    {
        return new self(
            id:       $sprint->id,
            name:     $sprint->name,
            goal:     $sprint->goal,
            status:   SprintStatus::from($sprint->status),
            startsAt: $sprint->starts_at instanceof CarbonImmutable
                ? $sprint->starts_at
                : ($sprint->starts_at ? CarbonImmutable::instance($sprint->starts_at) : null),
            endsAt:   $sprint->ends_at instanceof CarbonImmutable
                ? $sprint->ends_at
                : ($sprint->ends_at ? CarbonImmutable::instance($sprint->ends_at) : null),
        );
    }
}
