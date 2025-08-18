<?php

namespace App\Enums;

enum ClaimEnum: string
{
    case SCRIPT                  = 'exam_facilitation';
    case SCRIPT_OLD              = 'exam_facilitation_';
    case FACILITATION            = 'physical_facilitation';
    case INTERACTION             = 'online_facilitation';
    case EXAM_PAPER              = 'written';
    case EXAM_CBT                = 'cbt';
    case NON_EXAM                = 'non-exam';
    case COURSE_EXAM_TYPE        = 'exam_type';
    case DATA_ALLOWANCE          = 'data_allowance';
    case WEBINAR_EXCESS_WORK     = 'webinar_excess_work';
    case COURSE_CBT_QUESTION     = 'course_cbt_question';
    case DEPARTMENTAL_RUN_COST   = 'departmental_run_cost';
    case COURSE_AUTHOR_COMMITTEE = 'course_author_committee';
    case COURSE_MATERIAL         = 'course_material';
    case COURSE_REVISION         = 'course_revision';
    case LOGISTICS_ALLOWANCE     = 'logistics_allowance';

    /** @return array<string,string> */
    public static function labelMap(): array
    {
        return [
            self::SCRIPT->value                  => 'Essential Online Teaching and Assessment Components',
            self::SCRIPT_OLD->value              => 'Examination Facilitation',
            self::FACILITATION->value            => 'Physical Interactive',
            self::COURSE_EXAM_TYPE->value        => 'Exam Type',
            self::DATA_ALLOWANCE->value          => 'Data Allowance',
            self::WEBINAR_EXCESS_WORK->value     => 'DLC Webinar/Excess Work Load Allowance',
            self::DEPARTMENTAL_RUN_COST->value   => 'Departmental Examination Running Cost',
            self::COURSE_AUTHOR_COMMITTEE->value => 'Question Authoring Committee in Departments',
            self::COURSE_MATERIAL->value         => 'Writing of Course Materials',
            self::COURSE_REVISION->value         => 'Authorized Review of Course Materials',
            self::LOGISTICS_ALLOWANCE->value     => 'Logistics Allowance',
        ];
    }

    /**
     * @param string|ClaimEnum $type
     * @return string
     * @example echo ClaimEnum::getLabel(ClaimEnum::SCRIPT); echo ClaimEnum::getLabel('exam_type');
     */
    public static function getLabel(string|self $type): string
    {
        $value = $type instanceof self ? $type->value : $type;
        return self::labelMap()[$value] ?? $value;
    }

    public static function allClaimTypes(): array
    {
        return [
            self::FACILITATION,
            self::SCRIPT,
            self::WEBINAR_EXCESS_WORK,
            self::DATA_ALLOWANCE,
            self::COURSE_EXAM_TYPE,
        ];
    }

    /** Convenience: get just the string values
     *  @return array<int,string>
     */
    public static function ALL_CLAIM_TYPES(): array
    {
        return array_map(
            fn (self $c) => $c->value,
            self::allClaimTypes()
        );
    }
}
