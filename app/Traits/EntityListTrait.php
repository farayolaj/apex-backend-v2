<?php

namespace App\Traits;

use App\Libraries\EntityLoader;
use App\Models\FormConfig;
use App\Support\DTO\ApiListParams;

trait EntityListTrait
{
    private int $defaultLength = 100;

    /**
     * @return array<string,mixed>
     */
    public function list(string $entity): array
    {
        $totalLength = 0;
        $orderBy = 'ID desc';
        $param = request()->getGet();

        // get the parameter for paging
        $start = array_key_exists('start', $_GET) ? $param['start'] : 0;
        $len = array_key_exists('len', $_GET) ? $param['len'] : $this->defaultLength;
        $q = array_key_exists('q', $param) ? $param['q'] : null;
        $sortBy = array_key_exists('sortBy', $param) ? $param['sortBy'] : null;
        $sortDirection = array_key_exists('sortDirection', $param) ? $param['sortDirection'] : null;

        $filterList = $param;
        unset($filterList['q']);
        unset($filterList['st']);
        unset($filterList['start']);
        unset($filterList['len']);
        unset($filterList['sortBy']);
        unset($filterList['sortDirection']);

        $filterList = $this->validateEntityFilters($entity, $filterList);
        $entityObject = EntityLoader::loadClass($this, $entity);
        $queryString = null;

        if ($q) {
            $queryString = $this->buildWhereSearchString($entity, $q);
        }

        if ($sortBy) {
            $sortDirection = ($sortDirection == 'down') ? 'desc' : 'asc';
            $orderBy = " $sortBy $sortDirection ";
        }

        $tempR = method_exists($entityObject, 'APIList') ?
            $entityObject->APIList($filterList, $queryString, $start, $len, $orderBy) :
            $entityObject->allListFiltered($filterList, $totalLength, $start, $len, true, " order by {$orderBy}", $queryString);

        return $this->buildApiListResponse($tempR);
    }

    public function listApiEntity(string $entity){
        $request = request()->getGet();
        $filterList = $request;
        unset($filterList['q']);
        unset($filterList['start']);
        unset($filterList['len']);
        unset($filterList['sortBy']);
        unset($filterList['sortDirection']);

        $filterList = $this->validateEntityFilters($entity, $filterList);
        $entityObject = EntityLoader::loadClass($this, $entity);

        if(method_exists($entityObject, 'APIList')){
            return $entityObject->APIList($request, $filterList);
        }

        $params = ApiListParams::fromArray($request, [
            'perPage'    => 25,
            'maxPerPage' => 100,
            'sort'       => 'code',
        ]);

        $params->filters = $filterList;
        return $entityObject->listApi($entityObject::$apiSelectClause,
            $params
        );
    }

    public function showListEntity(
        string $entity,
        int $id,
        array $include = [],
        array|string|null $select = null,
        bool $escape = false): array
    {
        $entityObject = EntityLoader::loadClass($this, $entity);
        return $entityObject->detail($id, $include, $select, $escape);
    }

    public function buildApiListResponse(array $data): array
    {
        $toReturn = array();
        if (empty($data)) {
            return [
                'paging' => 0,
                'table_data' => null,
            ];
        }
        $paging = ($data[1] && is_array($data[1])) ? $data[1][0]['totalCount'] : $data[1];
        $toReturn['paging'] = (int)$paging;
        $toReturn['table_data'] = $data[0];

        return $toReturn;
    }

    public function buildExternalApiListResponse(array $data): array
    {
        $toReturn = array();
        if (empty($data)) {
            return [
                'total' => 0,
                'table_data' => null,
            ];
        }
        $paging = ($data[1] && is_array($data[1])) ? $data[1][0]['totalCount'] : $data[1];
        $toReturn['total'] = (int)$paging;
        $toReturn['table_data'] = $data[0];

        return $toReturn;
    }

    /**
     * @param string $entity
     * @param string $query
     * @return string
     */
    public function buildWhereSearchString(string $entity, string $query): string
    {
        $formConfig = new FormConfig(true, true);
        $config = $formConfig->getInsertConfig($entity);
        if (!$config) {
            return '';
        }
        $list = array_key_exists('search', $config) ? $config['search'] : false;
        if (!$list) {
            $entityModel = EntityLoader::loadClass($this, $entity);
            $list = array_keys($entityModel::$labelArray);
        }
        return buildCustomSearchString($list, $query);
    }

    /**
     * @param string $entity
     * @param array<int,mixed> $filters
     * @return array
     */
    public function validateEntityFilters(string $entity, ?array $filters): array
    {
        if (!$filters) {
            return [];
        }

        $formConfig = new FormConfig(true, true);
        $filterSettings = $formConfig->getInsertConfig($entity);
        if (!$filterSettings) {
            return [];
        }
        $result = array();
        foreach ($filters as $key => $value) {
            if (!$value && $value != '0') {
                continue;
            }
            $realKey = $this->getRealKey($key, $filterSettings);
            if (!$realKey) {
                continue;
            }
            $result[$realKey] = $value;
        }
        return $result;
    }

    /**
     * @param string $key
     * @param array<int,mixed> $filterSettings
     * @return string|null
     */
    private function getRealKey(string $key, array $filterSettings): ?string
    {
        // check if there is a key like this in the filter settings
        if (array_key_exists('filter', $filterSettings) !== false) {
            foreach ($filterSettings['filter'] as $value) {
                if ($value['filter_display'] == $key || $value['filter_label'] == $key) {
                    return $value['filter_label'];
                }
            }
        }
        return null;
    }
}