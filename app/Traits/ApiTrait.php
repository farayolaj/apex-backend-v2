<?php

namespace App\Traits;

trait ApiTrait
{
    /**
     * Group related data to associative array
     *
     * @param array $data
     * @param string $key
     * @return array
     */
    public static function groupRelatedDataToAssoc(array $data = [], string $key = 'name'): array {
        $return = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                if(isset($item['voters_path'])){
                    $item['voters_path'] = base_url($item['voters_path']);
                }

                if (array_key_exists($item[$key], $return) !== false) {
                    $return[$item[$key]][] = $item;
                } else {
                    $return[$item[$key]][] = $item;
                }
            }
        }

        return $return;
    }
}