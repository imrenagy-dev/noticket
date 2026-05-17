<?php

namespace App\Support;

use App\Contracts\TeamSlugGeneratorContract;
use App\Models\Team;
use Illuminate\Support\Str;

class TeamSlugGenerator implements TeamSlugGeneratorContract
{
    public function generate(string $name, ?int $excludeId = null): string
    {
        $defaultSlug = Str::slug($name);

        $query = Team::withTrashed()
            ->where(function ($query) use ($defaultSlug) {
                $query->where('slug', $defaultSlug)
                    ->orWhere('slug', 'like', $defaultSlug . '-%');
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingSlugs = $query->pluck('slug');

        $maxSuffix = $existingSlugs
            ->map(function (string $slug) use ($defaultSlug): ?int {
                if ($slug === $defaultSlug) {
                    return 0;
                } elseif (preg_match('/^' . preg_quote($defaultSlug, '/') . '-(\d+)$/', $slug, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter(fn (?int $suffix) => $suffix !== null)
            ->max() ?? 0;

        return $existingSlugs->isEmpty()
            ? $defaultSlug
            : $defaultSlug . '-' . ($maxSuffix + 1);
    }
}
