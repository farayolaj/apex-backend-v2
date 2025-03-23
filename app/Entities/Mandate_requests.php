<?php

namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Mandate_requests extends Crud
{
    protected static $tablename = '';

    static $apiSelectClause = [];

    private function getTransactionUserRequest($userID)
    {
        $query = "SELECT batch_ref from transaction_request group by batch_ref";
        $result = $this->query($query);
        $payload = [];
        if ($result) {
            $result = useGenerators($result);
            foreach ($result as $item) {
                $payload[] = [
                    'batch_ref' => $item['batch_ref'],
                    'user_requests' => $item
                ];
            }
        }

        return $payload;
    }

    private function getUserRequestOnAssignee($requestID, $userID)
    {
        $query = "SELECT a.* from user_requests a join user_request_assignee b on b.user_request_id = a.id where b.user_request_id = ? and assign_to = ?";
        $result = $this->query($query, [$requestID, $userID]);
        return $result;
    }

    /**
     * @param mixed $filterList
     * @param mixed $queryString
     * @param mixed $start
     * @param mixed $len
     * @param mixed $orderBy
     * @return array
     */
    public function APIList($filterList, $queryString, $start, $len, $orderBy = null, $type = null): array
    {
        $q = request()->getGet('q') ?: null;
        if ($q) {
            $searchArr = ['a.admon_reference', 'a.rrr_code', 'a.destination_account_number', 'a.destination_account_name'];
            $queryString = buildCustomSearchString($searchArr, $q);
        }
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        if ($type === 'db-staff') {
            $currentUser = WebSessionManager::currentAPIUser();
            $filterQuery .= ($filterQuery ? " and " : " where ") . "b.assign_to = '$currentUser->id' and b.request_type = 'mandate' ";
        }

        if (!$filterValues) {
            $filterValues = [];
        }

        if ($type === 'db-staff') {
            $query = "SELECT SQL_CALC_FOUND_ROWS batch_ref,rrr_code,date(a.created_at) as created_at from transaction_request a join user_request_assignee b on b.user_request_id = a.user_request_id $filterQuery";
        } else {
            $query = "SELECT SQL_CALC_FOUND_ROWS batch_ref,rrr_code,date(a.created_at) as created_at from transaction_request a group by batch_ref,rrr_code,date(a.created_at) $filterQuery";
        }

        if ($type === 'db-staff') {
            $query .= "group by batch_ref,rrr_code,date(a.created_at)";
        }

        if (isset($_GET['sortBy']) && $orderBy) {
            $query .= " order by $orderBy ";
        } else {
            $query .= " order by created_at desc ";
        }

        if (request()->getGet('start') && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $query .= " limit $start, $len";
        }

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }

}
