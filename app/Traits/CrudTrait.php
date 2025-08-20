<?php
/**
 * This is a trait helper class for crud, such that methods that are peculiar
 * to the class is turned into a trait helper
 */
namespace App\Traits;

use App\DTO\ApiListParams;
use App\Libraries\DictFilters;
use CodeIgniter\Database\BaseBuilder;

trait CrudTrait {

    public function totalEntityCount(string $tablename, string $queryclause = '')
    {
        $tablename = strtolower($tablename);
        $query = "SELECT count(*) as total from $tablename $queryclause";
        $result = $this->query($query);
        return ($result) ? $result[0]['total'] : 0;
    }

    public function totalEntitySum(string $tablename, string $column, string $queryclause = '')
    {
        $tablename = strtolower($tablename);
        $query = "SELECT sum($column) as total from $tablename $queryclause";
        $result = $this->query($query);
        return ($result) ? $result[0]['total'] : 0;
    }

    /**
     * Override for joins/grouping specific to a resource
     */
    protected function baseBuilder(): BaseBuilder
    {
        return $this->db->table($this->getTableName() . ' a');
    }

    /**
     * Array of columns OR a raw select string
     */
    protected function defaultSelect(): array
    {
        return ['a.*'];
    }

    protected function defaultShowSelect(): string
    {
        return 'a.*';
    }

    protected function applyIncludesForShow(BaseBuilder $b, array $include, string &$select): void
    {
        // no-op by default
    }

    /**
     * Override if you need permanent constraints (e.g., active=1)
     */
    protected function applyBaseFilters($b): void {}

    /**
     * Internal dict already validated/mapped at controller/repo level
     */
    protected function applyCustomFilters($b, array $internalDict): void
    {
        DictFilters::apply($b, $internalDict, 'a');
    }

    /**
     * Override for resource-specific default ordering (multi-column allowed)
     */
    protected function applyDefaultOrder(BaseBuilder $builder): void
    {
        $builder->orderBy('a.id', 'ASC');
    }

    /**
     * Optionally override to change how search runs
     */
    protected function applySearch(BaseBuilder $builder, string $q): void
    {
        if ($q === '' || empty($this->searchable)) return;
        $builder->groupStart();
        foreach ($this->searchable as $col) {
            $builder->orLike($col, $q, 'both'); // use 'after' for index-friendly prefix search
        }
        $builder->groupEnd();
    }

    protected function applySort(BaseBuilder $builder, ApiListParams $p): void
    {
        $dir = ($p->dir == 'down') ? 'DESC' : 'ASC';
        if (!empty($p->rawOrder)) {
            // Raw order string that YOU build server-side
            $builder->orderBy($p->rawOrder, '', false);
            return;
        }

        $col = $this->sortable[$p->sort] ?? null;
        if ($col) {
            $builder->orderBy($col, $dir);
        } else {
            $this->applyDefaultOrder($builder);
        }
    }

    /**
     * Override if you need to post-process rows (like the former processList)
     */
    protected function postProcess(array $rows): array
    {
        return $rows;
    }

    /**
     * Normalize single record for API
     */
    protected function postProcessOne(array $row): array
    {
        return $row;
    }

    /**
     * @param $select
     * @param ApiListParams $p
     * @param bool $escape
     * @return array
     */
    public function listApi($select, ApiListParams $p, bool $escape = true): array
    {
        $builder = $this->baseBuilder();

        $this->applyBaseFilters($builder);
        $this->applySearch($builder, $p->q);
        $this->applyCustomFilters($builder, $p->filters);

        $total = (int) (clone $builder)->countAllResults();

        $this->applySort($builder, $p);
        if ($p->isPaging()) {
            $builder->limit($p->perPage, $p->offset());
        }

        $select = $select ?? $this->defaultSelect();
        if (is_array($select)) {
            $builder->select(implode(',', $select), $escape);
        } else {
            $builder->select($select, $escape); // pass $escape=false for raw functions/aliases
        }
        $rows = $builder->get()->getResultArray();
        $rows = $this->postProcess($rows);

        return [
            'paging'      => $total,
            'table_data'  => $rows,
            'meta' => [
                'page'        => $p->isPaging() ? $p->page    : null,
                'per_page'    => $p->isPaging() ? $p->perPage : null,
                'total_pages' => $p->isPaging() ? (int) ceil($total / $p->perPage) : 1,
            ],
        ];
    }

    public function listFromSubquery(
        \Closure $makeSubquery,          // fn(BaseConnection $db): BaseBuilder
        ApiListParams $p,
        array $searchableCols,
        array $sortableMap,
        string $alias = 'x',
        string $select = '*'             // what to project from the subquery in the outer query
    ): array {
        $sub = $makeSubquery($this->db);
        if (! $sub instanceof BaseBuilder) {
            throw new \InvalidArgumentException('Subquery factory must return a BaseBuilder');
        }

        $sql = $sub->getCompiledSelect();
        $b   = $this->db->table("({$sql}) {$alias}", false);

        if ($p->q !== '' && $searchableCols) {
            $b->groupStart();
            foreach ($searchableCols as $col) {
                $qcol = (strpos($col, '.') === false) ? "{$alias}.{$col}" : $col;
                $b->orLike($qcol, $p->q, 'both');
            }
            $b->groupEnd();
        }

        DictFilters::apply($b, $p->filters, $alias);
        $total = (int) (clone $b)->countAllResults();

        $dir = ($p->dir == 'down') ? 'DESC' : 'ASC';
        $sortCol = $sortableMap[$p->sort] ?? reset($sortableMap) ?? "{$alias}.id";
        $b->orderBy($sortCol, $dir);

        if ($p->isPaging()) {
            $b->limit($p->perPage, $p->offset());
        }

        $b->select($select, false);
        $rows = $b->get()->getResultArray();

        return [
            'paging'      => $total,
            'table_data'  => $rows,
            'meta' => [
                'page'        => $p->isPaging() ? $p->page    : null,
                'per_page'    => $p->isPaging() ? $p->perPage : null,
                'total_pages' => $p->isPaging() ? (int) ceil($total / $p->perPage) : 1,
            ],
        ];
    }

    /**
     * Convenience: same as above, but when you already have a RAW SQL string.
     * (E.g., UNION queries that are easier to write by hand.)
     */
    public function listFromSQL(
        string $rawSQL,                  // must be a complete SELECT (no trailing semicolon)
        ApiListParams $p,
        array $searchableCols,
        array $sortableMap,
        string $alias = 'x',
        string $select = '*'
    ): array {
        // Wrap the raw SQL as a subquery table
        $b = $this->db->table("({$rawSQL}) {$alias}", false);

        if ($p->q !== '' && $searchableCols) {
            $b->groupStart();
            foreach ($searchableCols as $col) {
                $qcol = (strpos($col, '.') === false) ? "{$alias}.{$col}" : $col;
                $b->orLike($qcol, $p->q, 'both');
            }
            $b->groupEnd();
        }

        DictFilters::apply($b, $p->filters, $alias);
        $total = (int) (clone $b)->countAllResults();

        $dir = ($p->dir == 'down') ? 'DESC' : 'ASC';
        $sortCol = $sortableMap[$p->sort] ?? reset($sortableMap) ?? "{$alias}.id";
        $b->orderBy($sortCol, $dir);

        if ($p->isPaging()) {
            $b->limit($p->perPage, $p->offset());
        }

        $b->select($select, false);
        $rows = $b->get()->getResultArray();

        return [
            'paging'      => $total,
            'table_data'  => $rows,
            'meta' => [
                'page'        => $p->isPaging() ? $p->page    : null,
                'per_page'    => $p->isPaging() ? $p->perPage : null,
                'total_pages' => $p->isPaging() ? (int) ceil($total / $p->perPage) : 1,
            ],
        ];
    }

    /**
     * Fetch a single row by id.
     * $select: null => use defaultShowSelect(); string or array allowed; raw expressions allowed.
     * $escape: set false when $select contains raw expressions/aliases.
     */
    public function detail(
        int $id,
        array $include = [],
        array|string|null $select = null,
        bool $escape = false
    ): ?array {
        $b = $this->baseBuilder();
        $this->applyBaseFilters($b);

        $selectStr = $this->normalizeSelect($select ?? $this->defaultShowSelect());
        $this->applyIncludesForShow($b, $include, $selectStr);
        $selectStr = $this->ensureIdInSelect($selectStr);

        $b->select($selectStr, $escape)
            ->where('a.id', $id)
            ->limit(1);

        $row = $b->get()->getRowArray();
        if (! $row) {
            return null;
        }
        return $this->postProcessOne($row);
    }

    protected function normalizeSelect(array|string $select): string
    {
        if (is_array($select)) {
            $select = implode(',', array_filter(array_map('trim', $select), static fn($s) => $s !== ''));
        }
        return trim($select);
    }

    protected function ensureIdInSelect(string $select): string
    {
        // if a.* is present, id is implied
        if (preg_match('/\ba\.\*\b/i', $select)) {
            return $select;
        }
        // if a.id already present
        if (preg_match('/\ba\.id\b/i', $select)) {
            return $select;
        }

        return 'a.id,' . $select;
    }


}