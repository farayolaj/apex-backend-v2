<?php

namespace App\Traits;

trait AdminModelTrait
{

    public static function ageRange(): array
    {
        return [
            [15, 19],
            [20, 24],
            [25, 29],
            [30, 34],
            [35, 39],
            [40, 'above'],
            [[], []],
        ];
    }

    public static function scoreRange(): array
    {
        return [
            [0, 25],
            [26, 35],
            [36, 45],
            [46, 55],
            [56, 65],
            [66, 100],
        ];
    }

    /**
     * Fill missing age with zero
     *
     * @param array $rangeData
     * @param array $data
     * @return array
     */
    public static function fillMissingListWithZero(array $rangeData = [], array $data = []): array
    {
        $content = [];
        if (! empty($data)) {
            $result = array_diff($rangeData, array_keys($data));
            if (! empty($result)) {
                foreach ($result as $item) {
                    $content[$item] = 0;
                }
            }
        }
        return $content;
    }

    /**
     * Implode the age range
     *
     * @param array $data
     * @param string $unknown
     * @return array
     */
    public static function implodeListRange(array $data, string $unknown = 'unknown'): array
    {
        $content = [];
        foreach ($data as $item) {
            $content[] = (! empty($item[0])) ? $item[0] . "-" . $item[1] : $unknown;
        }
        return $content;
    }

    /**
     * Get range list key
     *
     * @param $item
     * @param $range
     * @param null $unknown
     * @return string
     */
    public static function getRangeListKey($item, $range, $unknown = null): string
    {
        if ($item == null) {
            return $unknown ?? 'unknown';
        }

        foreach ($range as $val) {
            $min = $val[0];
            $max = $val[1];
            if ($item >= $min && $item <= $max) {
                return implode('-', [$min, $max]);
            }
        }
        return '40-above';
    }

    /**
     * Group data by age
     *
     * @param array $data
     * @param string $unknown
     * @return array
     */
    public static function groupDataByAge(array $data, string $unknown = 'unknown'): array
    {
        $range = self::ageRange();
        return self::processDataToRange($data, $range, $unknown);
    }

    /**
     * Format range to standard
     *
     * @param array $data
     * @return array
     */
    public static function formatRangeToStandard(array $data = []): array
    {
        $content = [];
        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                if ($key !== 'Null') {
                    $payload = [
                        'name'  => $key,
                        'total' => $val,
                    ];
                    $content[] = $payload;
                }
            }
        }

        return $content;
    }

    /**
     * Format group age category
     *
     * @param array $programmeAgeData
     * @return array
     */
    public static function formatGroupAgeCategory(array $programmeAgeData = []): array
    {
        $ranges  = self::implodeListRange(self::ageRange());
        $content = [];

        foreach ($ranges as $range) {
            $result = self::groupByAgeCategory($programmeAgeData, $range);
            if (! empty($result)) {
                $content[] = $result;
            }
        }
        return $content;
    }

    private static function groupByAgeCategory(array $data = [], ?string $type = null): array
    {
        $content = [];
        if (! empty($data)) {
            foreach ($data as $d) {
                if (array_key_exists($type, $d['value']) !== false) {
                    if (array_key_exists($type, $content) !== false) {
                        $content[$type][] = $d['value'][$type];
                    } else {
                        $content[$type][] = $d['value'][$type];
                    }
                }
            }
        }
        return ['name' => $type, 'data' => $content[$type]];
    }

    /**
     * Group related data to associative array
     *
     * @param array $data
     * @param string $key
     * @return array
     */
    public static function groupRelatedDataToAssoc(array $data = [], string $key = 'name'): array
    {
        $return = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                if (array_key_exists($item[$key], $return) !== false) {
                    $return[$item[$key]][] = $item;
                } else {
                    $return[$item[$key]][] = $item;
                }
            }
        }

        return $return;
    }

    /**
     * Get unique name from associative array
     *
     * @param array $data
     * @return array
     */
    public static function getUniqueNameFromAssoc(array $data = []): array
    {
        $content = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                if (in_array($item['name'], $content) === false) {
                    $content[] = $item['name'];
                }
            }
        }

        return $content;
    }

    /**
     * Remove single programme prefix
     *
     * @param string|null $data
     * @return string
     */
    public static function removeSingleProgrammePrefix(string $data = null): string
    {
        return preg_replace('/^.*(\(.*\)).*$/', '$1', $data);
    }

    /**
     * Group data by gender
     *
     * @param $data
     * @param $type
     * @return array
     */
    public static function groupDataByGender($data, $type): array
    {
        $content = [];
        if (! empty($data)) {
            foreach ($data as $d) {
                if ($d['gender'] == $type) {
                    $content[] = [
                        'name'  => self::removeSingleProgrammePrefix($d['name']),
                        'total' => $d['total'],
                    ];
                }
            }
        }
        return $content;
    }

    /**
     * Process the stats for the PWDs
     *
     * @param array $data
     * @return array
     */
    public static function processPWDStats(array $data): array
    {
        $disabilityCounts = [];
        foreach ($data as $result) {
            $temp = explode(',', $result['disabilities']);
            foreach ($temp as $disability) {
                $disability = trim($disability);
                if (! array_key_exists($disability, $disabilityCounts)) {
                    $disabilityCounts[$disability] = 0;
                }
                $disabilityCounts[$disability]++;
            }
        }

        return array_map(function ($name, $total) {
            return ['name' => $name, 'total' => $total];
        }, array_keys($disabilityCounts), $disabilityCounts);
    }

    public static function groupDataByScore(array $data, string $unknown = 'unknown'): array
    {
        $range = self::scoreRange();
        return self::processDataToRange($data, $range, $unknown, 'label');
    }

    /**
     * @param array $data
     * @param array $range
     * @param string $unknown
     * @param string $label
     * @return array
     */
    private static function processDataToRange(array $data, array $range, string $unknown, string $label = 'age'): array
    {
        $ageRangeData = self::implodeListRange($range, $unknown);

        $return = [];
        foreach ($data as $res) {
            $age = $res[$label];
            $num = $res['total'];
            $key = self::getRangeListKey($age, $range, $unknown);
            if (array_key_exists($key, $return) === false) {
                $return[$key] = 0;
            }
            $return[$key] = $return[$key] + $num;

        }

        $result = self::fillMissingListWithZero($ageRangeData, $return);
        $return = array_merge($return, $result);
        ksort($return);
        return $return;
    }

}