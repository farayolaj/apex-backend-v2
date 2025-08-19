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

    protected function applySort(BaseBuilder $builder, string $sort, string $dir): void
    {
        $dir = ($dir == 'down') ? 'desc' : 'asc';
        $col = $this->sortable[$sort] ?? null;

        if (!$col) {
            $col = reset($this->sortable) ?: 'a.id';
        }
        $builder->orderBy($col, $dir === 'desc' ? 'DESC' : 'ASC');
    }

    /**
     * @param array $columns e.g. ['a.id','a.title','a.code','a.active']
     */
    public function listApi(array $columns, ApiListParams $p): array
    {
        $builder = $this->db->table($this->getTableName() . ' a');

        $this->applyBaseFilters($builder);
        $this->applySearch($builder, $p->q);
        $this->applyCustomFilters($builder, $p->filters);

        $countBuilder = clone $builder;
        $total = (int) $countBuilder->countAllResults();

        $this->applySort($builder, $p->sort, $p->dir);
        if ($p->isPaging()) {
            $builder->limit($p->perPage, $p->offset());
        }

        $builder->select(implode(',', $columns));
        $rows = $builder->get()->getResultArray();

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


}