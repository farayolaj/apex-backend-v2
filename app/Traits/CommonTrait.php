<?php

namespace App\Traits;

use App\Enums\CommonEnum as CommonSlug;
use DateInterval;
use DateTime;

trait CommonTrait
{
    public static function isPaymentValid($value): bool
    {
        return $value === '00' || $value === '01';
    }

    public static function moveStudentToNewLevel($entryMode, $currentLevel): array
    {
        helper('string');
        $level = $currentLevel;
        $mode = $entryMode;
        $OLevelstruct = [
            '5' => 501,
            '501' => 502,
            '502' => 503,
        ];
        $fastTrackStruct = [
            '4' => 401,
            '401' => 402,
        ];

        if ($entryMode === CommonSlug::O_LEVEL->value || $entryMode === CommonSlug::DIRECT_ENTRY->value) {
            if (isNonGraduate($currentLevel)) {
                $level = $currentLevel + 1;
            } else {
                $level = @$OLevelstruct[$currentLevel] ?: $currentLevel;
            }
        }

        if ($entryMode === CommonSlug::FAST_TRACK->value) {
            if (isProperNonGraduate($currentLevel)) {
                $level = $currentLevel + 1;
            } else {
                $level = @$fastTrackStruct[$currentLevel] ?: $currentLevel;
            }
        }
        return ['level' => $level, 'mode' => $mode];
    }

    public static function inferPreviousLevel($entryMode, $currentLevel): int
    {
        $level = $currentLevel;
        $OLevelstruct = [
            '5' => 4,
            '501' => 5,
            '502' => 501,
            '503' => 502,
        ];
        $fastTrackStruct = [
            '4' => 3,
            '401' => 4,
            '402' => 401,
        ];

        if ($entryMode === CommonSlug::O_LEVEL->value || $entryMode === CommonSlug::DIRECT_ENTRY->value) {
            if (isNonGraduate($currentLevel)) {
                $level = $currentLevel - 1;
            } else {
                $level = @$OLevelstruct[$currentLevel] ?: $currentLevel;
            }
        } else {
            if ($entryMode === CommonSlug::FAST_TRACK->value) {
                if (isProperNonGraduate($currentLevel)) {
                    $level = $currentLevel - 1;
                } else {
                    $level = @$fastTrackStruct[$currentLevel] ?: $currentLevel;
                }
            }
        }
        return $level;
    }

    public static function extractApplicantEntity($uuid): array
    {
        $parts = explode('-', $uuid);

        if (count($parts) == 2) {
            $id = $parts[0];
            $entity = $parts[1];
        } else {
            $id = $uuid;
            $entity = CommonSlug::APPLICANT->value;
        }

        if (!is_numeric($id)) {
            $entity = CommonSlug::APPLICANT->value;
            $id = $entity;
        }

        $entity = $entity === CommonSlug::APPLICANT_PUTME->value ? 'applicant_post_utme' : 'applicants';
        return [$id, $entity];
    }

    public static function transformGender($gender): string
    {
        $gender = strtolower($gender);
        $content = [
            'm' => 'Male',
            'f' => 'Female',
        ];
        return $content[$gender];
    }

    public static function getReservationExpiry(string $interval = '48'): string
    {
        return (new DateTime())
            ->add(new DateInterval("PT{$interval}H"))
            ->format('Y-m-d H:i:s');
    }
}