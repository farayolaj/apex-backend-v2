<?php

namespace App\Entities;

use App\Models\Crud;

class Webinar_comments extends Crud
{
  protected static $tablename = 'webinar_comments';

  static $apiSelectClause = "wc.id, wc.webinar_id, wc.content, wc.author_id, wc.created_at, 
    COALESCE(CONCAT(s.firstname, ' ', s.lastname), CONCAT(st.firstname, ' ', st.lastname)) as author_name";

  public function getCommentById(int $commentId)
  {
    try {
      return $this->db
        ->table(self::$tablename)
        ->where('id', $commentId)
        ->get()
        ->getRowArray();
    } catch (\Exception $e) {
      log_message('error', 'Error Fetching Webinar Comment: ' . $e->getMessage(), [
        'stack' => $e->getTraceAsString()
      ]);
      return null;
    }
  }

  public function newComment(int $webinarId, string $content, int $authorId, string $authorTable)
  {
    try {
      $builder = $this->db->table(self::$tablename);
      $builder->insert([
        'webinar_id' => $webinarId,
        'content' => $content,
        'author_id' => $authorId,
        'author_table' => $authorTable
      ]);
      return true;
    } catch (\Exception $e) {
      log_message('error', 'Error Inserting Webinar Comment: ' . $e->getMessage(), [
        'stack' => $e->getTraceAsString()
      ]);
      return false;
    }
  }

  public function getComments(int $webinarId, int $limit = 10, int $offset = 0)
  {
    try {
      $builder = $this->db->table(self::$tablename . ' wc');
      $builder->select(self::$apiSelectClause);
      $builder->join('students s', 'wc.author_id = s.id AND wc.author_table = "students"', 'left');
      $builder->join('staffs st', 'wc.author_id = st.id AND wc.author_table = "staffs"', 'left');
      $builder->where('wc.webinar_id', $webinarId);
      $builder->orderBy('wc.created_at', 'DESC');
      $builder->limit($limit, $offset);
      $res = $builder->get();
      $comments = $res->getResultArray();

      $totalCount = $this->db->table(self::$tablename)
        ->where('webinar_id', $webinarId)
        ->countAllResults();
      return [
        'comments' => $comments,
        'totalCount' => $totalCount
      ];
    } catch (\Exception $e) {
      log_message('error', 'Error Fetching Webinar Comments: ' . $e->getMessage(), [
        'stack' => $e->getTraceAsString()
      ]);
      return [
        'comments' => [],
        'totalCount' => 0
      ];
    }
  }

  public function deleteComment(int $commentId): bool
  {
    try {
      $builder = $this->db->table(self::$tablename);
      $builder->where('id', $commentId);
      $res = $builder->delete();
      return $res;
    } catch (\Exception $e) {
      log_message('error', 'Error Deleting Webinar Comment: ' . $e->getMessage(), [
        'stack' => $e->getTraceAsString()
      ]);
      return false;
    }
  }
}
