<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApiPagination
{
    public static function perPage(Request $request, int $default = 50, int $max = 100): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return max(1, min($perPage, $max));
    }

    public static function response(LengthAwarePaginator $page): array
    {
        return [
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ];
    }

    public static function paginate(Builder $query, Request $request, int $default = 50, int $max = 100): array
    {
        return self::response($query->paginate(self::perPage($request, $default, $max)));
    }
}
