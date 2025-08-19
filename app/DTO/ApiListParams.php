<?php

namespace App\DTO;

final class ApiListParams
{
    public string $q = '';
    public int $page = 1;
    public int $perPage = 0;
    public string $sort = 'id';
    public string $dir = 'up';
    private bool $usePaging = false;
    /**
     * Your internal column=>value filters (e.g., ['a.code'=>'eco'])
     */
    public array $filters = [];

    public static function fromArray(array $input, array $defaults=[]): self
    {
        $p = new self();
        $p->usePaging = array_key_exists('len', $input) || array_key_exists('start', $input);

        $p->q       = trim((string)($input['q'] ?? ''));
        $p->sort = strtolower((string)($input['sortBy'] ?? $defaults['sort'] ?? 'id'));
        $p->dir  = strtolower((string)($input['sortDirection'] ?? $defaults['dir'] ?? 'up')) === 'down' ? 'down' : 'up';

        if ($p->usePaging) {
            $pageDefault    = (int)($defaults['page'] ?? 1);
            $perPageDefault = (int)($defaults['perPage'] ?? 25);
            $maxPerPage     = (int)($defaults['maxPerPage'] ?? 100);

            $p->page    = max(1, (int)($input['start'] ?? $pageDefault));
            $p->perPage = min($maxPerPage, max(1, (int)($input['len'] ?? $perPageDefault)));
        } else {
            $p->page    = 1;
            $p->perPage = 0;
        }

        $p->filters = (array)($input['filters'] ?? []); // you can overwrite this later with repo mapping
        return $p;
    }

    public function isPaging(): bool
    {
        return $this->usePaging && $this->perPage > 0;
    }

    public function offset(): int
    {
        return $this->isPaging() ? ($this->page - 1) * $this->perPage : 0;
    }
}