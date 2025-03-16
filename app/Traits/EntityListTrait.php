<?php

namespace App\Traits;

use App\Models\FormConfig;

trait EntityListTrait
{
    private int $defaultLength = 100;

    /**
     * @return array<string,mixed>
     */
    public function list(string $entity, array $extraFilter = []): array
    {
        $totalLength = 0;
        $orderBy = 'ID asc';
        $param = request()->getGet(null);

        // get the parameter for paging
        $start = array_key_exists('start', $_GET) ? $param['start'] : 0;
        $len = array_key_exists('len', $_GET) ? $param['len'] : $this->defaultLength;
        $q = array_key_exists('q', $param) ? $param['q'] : false;
        $sortBy = array_key_exists('sortBy', $param) ? $param['sortBy'] : false;
        $sortDirection = array_key_exists('sortDirection', $param) ? $param['sortDirection'] : false;

        $filterList = $param;
        unset($filterList['q']);
        unset($filterList['st']);
        unset($filterList['start']);
        unset($filterList['len']);
        unset($filterList['sortBy']);
        unset($filterList['sortDirection']);

        if ($extraFilter !== []) {
            $filterList = array_merge($filterList, $extraFilter);
        }

        // perform some form of validation here to know what needs to be include in the list
        // and also how to perform
        $filterList = $this->validateEntityFilters($entity, $filterList);
        $entityObject = loadClass($entity);
        $queryString = false;

        if ($q) {
            // if there is a search query just build a query search to get the result with the other parameters
            $queryString = $this->buildWhereString($entity, $q);
        }

        if ($sortBy) {
            $sortDirection = ($sortDirection == 'down') ? 'desc' : 'asc';
            $orderBy = " $sortBy $sortDirection ";
        }

        $tempR = method_exists($entityObject, 'APIList') ?
            $entityObject->APIList($filterList, $queryString, $start, $len, $orderBy) :
            $entityObject->allListFiltered($filterList, $totalLength, $start, $len, true, " order by  {$orderBy}", $queryString);

        return $this->buildApiListResponse($tempR);
    }

    public function buildApiListResponse(array $data): array
    {
        $toReturn = array();
        if (empty($data)) {
            return [
                'length' => 0,
                'table_data' => null,
            ];
        }
        $paging = ($data[1] && is_array($data[1])) ? $data[1][0]['totalCount'] : $data[1];
        $toReturn['length'] = (int)$paging;
        $toReturn['table_data'] = $data[0];

        return $toReturn;
    }

    /**
     * @return string|<missing>
     */
    public function buildWhereString(string $entity, string $query): string
    {
        $formConfig = new FormConfig(true, true);
        $config = $formConfig->getInsertConfig($entity);
        if (!$config) {
            return '';
        }
        $list = array_key_exists('search', $config) ? $config['search'] : false;
        if (!$list) {
            //use all the fields here then
            $entity = loadClass($entity);
            $list = array_keys($entity::$labelArray);
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