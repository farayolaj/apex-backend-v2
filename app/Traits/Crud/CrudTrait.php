<?php
/**
 * This is a trait helper class for crud, such that methods that are peculiar
 * to the class is turned into a trait helper
 */
namespace App\Traits\Crud;

use App\Libraries\DictFilters;
use App\Support\Cache\ShowCacheSupport;
use App\Support\DTO\ApiListParams;
use App\Support\Entity\SelectSupport;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Cache\CacheInterface;

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

    protected function cacheNamespace(): string
    {
        return $this->getTableName();
    }

    /**
     * Array of columns OR a raw select string
     */
    public function defaultSelect():string|array
    {
        return ['a.*'];
    }

    protected function defaultShowSelect(): string
    {
        return 'a.*';
    }

    protected function applyIncludesForShow(BaseBuilder $b, array $include, string &$select): void
    {
        // no-op by default and can be overridden from the Entity Model
    }

    /**
     * Override if you need permanent constraints (e.g., active=1)
     */
    protected function applyBaseFilters(BaseBuilder $builder): void {}

    /**
     * Internal dict already validated/mapped at controller/repo level
     */
    protected function applyCustomFilters(BaseBuilder $builder, array $internalDict): void
    {
        DictFilters::apply($builder, $internalDict, 'a');
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

    protected function applyGroupBy(BaseBuilder $b, $groupBy): void
    {
        if (empty($groupBy)) return;
        $b->groupBy($groupBy);
    }

    /**
     * HAVING helper:
     * - string: raw (e.g. "COUNT(a.id) > 0")
     * - array:  ['SUM(a.score) >' => 50, 'AVG(a.x) >' => 1.2] OR ['raw having', 'another raw']
     * - callable: function (BaseBuilder $b) { $b->having('...'); ... }
     */
    protected function applyHaving(BaseBuilder $b, $having): void
    {
        if (empty($having)) return;

        if (is_callable($having)) { $having($b); return; }

        if (is_string($having))   { $b->having($having, null, false); return; }

        if (is_array($having)) {
            foreach ($having as $k => $v) {
                if (is_int($k))  $b->having($v, null, false); // raw string
                else             $b->having($k, $v);          // key/value
            }
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
     * Normalize a single record for API
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

        $this->applyGroupBy($builder, $p->groupBy);
        $this->applyHaving($builder, $p->having);

        if (!empty($groupBy)) {
            $countSql = (clone $builder)->select('1', false)->getCompiledSelect();
            $totalRow = $this->db->query("SELECT COUNT(*) AS total FROM ({$countSql}) t")->getRowArray();
            $total    = (int)($totalRow['total'] ?? 0);
        } else {
            $total = (int) (clone $builder)->countAllResults();
        }

        $this->applySort($builder, $p);
        if ($p->isPaging()) {
            $builder->limit($p->perPage, $p->offset());
        }

        $select = $select ?? $this->defaultSelect();
        if (is_array($select)) {
            $builder->select(implode(',', $select), $escape);
        } else {
            if ($select !== '') $builder->select($select, $escape); // pass $escape=false for raw functions/aliases
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
        bool $escape = false,
        array $cacheOptions = []
    ): ?array {
        $b = $this->baseBuilder();
        $this->applyBaseFilters($b);

        $selectStr = SelectSupport::normalizeSelect($select ?? $this->defaultShowSelect());
        $include   = SelectSupport::normalizeInclude($include);
        $this->applyIncludesForShow($b, $include, $selectStr);
        $selectStr = SelectSupport::ensureIdInSelect($selectStr);

        $cacheEnabled = (bool)($cacheOptions['enabled']   ?? true);
        $cacheBypass  = (bool)($cacheOptions['bypass']    ?? false);
        $cacheTtl     = (int) ($cacheOptions['ttl']       ?? $this->defaultShowTtl);
        $cacheNs      =        $cacheOptions['namespace'] ?? $this->cacheNamespace();
        $cacheExtra   = (string)($cacheOptions['extra']   ?? '');
        $selectTag    = isset($cacheOptions['select_tag']) ? (string)$cacheOptions['select_tag'] : null;

        $key = null;
        if ($cacheEnabled && !$cacheBypass && $cacheTtl > 0) {
            $key = ShowCacheSupport::buildShowKey(
                $this->cachePrefix,
                $cacheNs,
                $id,
                $include,
                $selectStr,
                $escape,
                $cacheExtra,
                $selectTag
            );

            $cached = ShowCacheSupport::cache()->get($key);
            if ($cached !== null) return $cached ?: null;
        }

        $b->select($selectStr, $escape)
            ->where('a.id', $id)
            ->limit(1);

        $row = $b->get()->getRowArray();
        if (!$row) return null;

        $row = $this->postProcessOne($row);

        if ($key) {
            ShowCacheSupport::cache()->save($key, $row, $cacheTtl);
        }

        return $row;
    }

    public function invalidateById(int $id): void
    {
        ShowCacheSupport::invalidateById($this->cacheNamespace(), $id);
    }

    public function invalidateAll(): void
    {
        ShowCacheSupport::invalidateAll($this->cacheNamespace());
    }


}