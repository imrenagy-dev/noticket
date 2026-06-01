<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Routing\Route as RouteElement;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\PotentiallyTranslatedString;

class TeamName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = strtolower(trim($value));

        if (in_array($name, $this->reservedNames(), true)) {
            $fail(__('This team name is reserved and cannot be used.'));
        }
    }

    protected function reservedNames(): array
    {
        return once(fn () => collect($this->routesPrefixes())
            ->merge(config('reserved-names'))
            ->unique()
            ->sort()
            ->values()
            ->toArray());
    }

    /**
     * Get a list of reserved names from the application's route prefixes.
     */
    protected function routesPrefixes(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map(fn (RouteElement $route) => $route->uri)
            ->map(fn (string $uri) => explode('/', $uri)[0])
            ->reject(fn (string $uri) => str_contains($uri, '{'))
            ->filter(fn (string $uri) => $uri !== '')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
