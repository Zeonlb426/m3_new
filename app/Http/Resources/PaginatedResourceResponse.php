<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Support\Arr;

/**
 * Class PaginatedResourceResponse
 * @package App\Http\Resources
 */
final class PaginatedResourceResponse extends \Illuminate\Http\Resources\Json\PaginatedResourceResponse
{
    /**
     * Get the pagination links for the response.
     *
     * @param array $paginated
     *
     * @return array
     */
    protected function paginationLinks($paginated): array
    {
        return [
            'first' => $paginated['first_page_url'] ?? null,
            'last' => $paginated['last_page_url'] ?? null,
            'prev' => $paginated['prev_page_url'] ?? null,
            'next' => $paginated['next_page_url'] ?? null,
        ];
    }

    /**
     * Gather the meta data for the response.
     *
     * @param array $paginated
     *
     * @return array
     */
    protected function meta($paginated): array
    {
        return Arr::except($paginated, [
            'data',
            'first_page_url',
            'last_page_url',
            'prev_page_url',
            'next_page_url',
            'links',
            'from',
            'to',
        ]);
    }
}
