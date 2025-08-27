<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Entities\Webinar_comments;
use App\Entities\Webinars;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use CodeIgniter\HTTP\ResponseInterface;

class WebinarCommentController extends BaseController
{
  private Webinars $webinars;
  private Webinar_comments $webinarComments;

  public function __construct()
  {
    $this->webinars = EntityLoader::loadClass(null, 'Webinars');
    $this->webinarComments = EntityLoader::loadClass(null, 'Webinar_comments');
  }

  public function getComments(int $webinarId)
  {
    $page = (int) $this->request->getGet('page') ?: 1;
    $perPage = (int) $this->request->getGet('perPage') ?: 10;

    ['comments' => $comments, 'totalCount' => $totalCount] = $this->webinarComments->getComments($webinarId, $perPage, ($page - 1) * $perPage);


    return ApiResponse::success(data: [
      'paging' => [
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => (int) ceil($totalCount / $perPage) ?: 1,
        'totalCount' => $totalCount
      ],
      'data' => $comments,
    ]);
  }

  public function newComment(int $webinarId)
  {
    $data = $this->request->getPost();

    $rules = [
      'content' => 'required|string|max_length[255]',
    ];

    if (!$this->validate($rules)) {
      $errors = $this->validator->getErrors();
      return ApiResponse::error(message: implode(", ", $errors), code: ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
    }

    if (!$this->webinars->webinarExists($webinarId)) {
      return ApiResponse::error(message: 'Webinar not found.', code: ResponseInterface::HTTP_NOT_FOUND);
    }

    $authorId = WebSessionManager::currentAPIUser()->id;

    if ($this->webinarComments->newComment($webinarId, $data['content'], $authorId, 'students')) {
      return ApiResponse::success();
    }

    return ApiResponse::error(message: 'Failed to create comment.', code: ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
  }

  public function deleteComment(int $commentId)
  {
    $userId = WebSessionManager::currentAPIUser()->id;
    $comment = $this->webinarComments->getCommentById($commentId);

    if (!$comment) {
      return ApiResponse::error(message: 'Comment not found.', code: ResponseInterface::HTTP_NOT_FOUND);
    }

    if ($comment['author_id'] !== $userId || $comment['author_table'] !== 'students') {
      return ApiResponse::error(message: 'You are not authorized to delete this comment.', code: ResponseInterface::HTTP_FORBIDDEN);
    }

    if ($this->webinarComments->deleteComment($commentId)) {
      return ApiResponse::success();
    }

    return ApiResponse::error(message: 'Failed to delete comment.', code: ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
  }
}
