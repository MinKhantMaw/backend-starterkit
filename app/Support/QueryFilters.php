<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class QueryFilters
{
    public function __construct(private readonly array $filters) {}

    public static function from(array $filters): self
    {
        return new self($filters);
    }

    /**
     * @param  array<int, string>  $columns
     */
    public function applySearch(Builder $query, array $columns): Builder
    {
        $search = $this->string('search');

        if ($search === null || $columns === []) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($columns, $search) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    public function applyDateRange(Builder $query, string $column = 'created_at'): Builder
    {
        return $query
            ->when($this->string('date_from'), fn (Builder $query, string $date) => $query->whereDate($column, '>=', $date))
            ->when($this->string('date_to'), fn (Builder $query, string $date) => $query->whereDate($column, '<=', $date));
    }

    /**
     * @param  array<int, string>  $allowedSorts
     */
    public function applySort(
        Builder $query,
        array $allowedSorts,
        string $defaultSort = 'created_at',
        string $defaultDirection = 'desc',
    ): Builder {
        $sortBy = $this->string('sort_by') ?? $defaultSort;
        $sortDirection = strtolower($this->string('sort_direction') ?? $defaultDirection);

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = $defaultSort;
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = $defaultDirection;
        }

        return $query->orderBy($sortBy, $sortDirection);
    }

    public function perPage(int $default = 15, int $max = 100): int
    {
        $perPage = (int) ($this->filters['perPage'] ?? $this->filters['per_page'] ?? $default);

        return max(1, min($perPage, $max));
    }

    public function string(string $key): ?string
    {
        $value = $this->filters[$key] ?? null;

        if (blank($value)) {
            return null;
        }

        return (string) $value;
    }

    public function value(string $key): mixed
    {
        return $this->filters[$key] ?? null;
    }
}
