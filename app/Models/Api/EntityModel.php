<?php
/**
 * The class that managers entity related data generally
 */

namespace App\Models\Api;

use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Traits\Crud\EntityListTrait;
use App\Traits\UploadTrait;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EntityModel
{
    use EntityListTrait, UploadTrait;

    protected BaseConnection $db;

    private string $crudNameSpace = 'App\Models\Crud';

    protected ?RequestInterface $request;

    protected ?ResponseInterface $response;

    function __construct(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->defaultLength = 30;
        $this->db = db_connect();
        $this->request = $request;
        $this->response = $response;
    }

    // process all the crud operations
    public function process($entity, $args, object $entityObject = null)
    {
        try {
            if (!$args) {
                // this handles /entity GET, will get list of entities and POST will insert a new one
                if ($this->request->getMethod() === 'GET') {
                    // intercept/overload similar GET method that exist and call it first
                    // the idea is to overload a method that work directly with the entity[APIList] method
                    // such that we can manually define the method in webApiModel where it would be called.
                    if ($entityObject && is_callable([$entityObject, $entity])) {
                        return $entityObject->$entity($args);
                    } else {
                        $result = $this->listEntity($entity);
                        return ApiResponse::success('Success', $result);
                    }

                } elseif ($this->request->getMethod() === 'POST') {
                    return $this->insert($entity);
                }
                return null;
            }

            if (count($args) == 1) {
                // this handles entity detail view and update
                if (is_numeric($args[0])) {
                    if ($this->request->getMethod() === 'GET') {
                        $param = $this->request->getGet();
                        $id = $args[0];
                        $result = $this->detail($entity, $id, $param);
                        if (!$result) {
                            return ApiResponse::error('No data available');
                        }
                        return ApiResponse::success('Success', $result);
                    } elseif ($this->request->getMethod() === 'POST') {
                        $values = $this->request->getPost();
                        $id = $args[0];
                        $this->update($entity, $id, $values);
                    }
                    return null;
                } else {
                    if (strtolower($args[0]) == 'bulk_upload') {
                        $message = '';
                        $this->processBulkUpload($entity, $message);
                        return null;
                    }
                }
                return null;
            }

            if (count($args) == 2 && is_numeric($args[1])) {
                if ($this->request->getMethod() === 'POST') {
                    if (strtolower($args[0]) == 'delete') {
                        $id = $args[1];
                        if ($this->delete($entity, $id)) {
                            return ApiResponse::success('Success');
                        }
                        return ApiResponse::error('Unable to delete item');
                    }

                    // handle the issue with the status
                    if (strtolower($args[0]) == 'disable' || strtolower($args[0]) == 'enable') {
                        $operation = $args[0];
                        $id = $args[1];
                        $status = false;
                        if ($operation == 'disable' || $operation == 'remove') {
                            $status = $this->disable($entity, $id);
                        } else {
                            $status = $this->enable($entity, $id);
                        }
                        return $status ? ApiResponse::success('Success') : ApiResponse::error('Unable to disable item');
                    }

                    if (strtolower($args[0]) == 'toggle_action') {
                        $operation = $args[0];
                        $id = $args[1];
                        $status = false;
                        $values = $this->request->getPost(null);
                        if ($operation === 'toggle_action') {
                            $status = $this->updateEntityAction($entity, $id, $values);
                        }
                        return $status ? ApiResponse::success('Success') : ApiResponse::error('Unable to update item');
                    }

                }
                return null;
            }

            if (count($args) == 3 && is_numeric($args[2])) {
                if ($this->request->getMethod() === 'POST') {
                    if (strtolower($args[0]) == 'delete') {
                        $type = $args[1];
                        $id = $args[2];
                        if ($this->delete($entity, $id, $type)) {
                            return ApiResponse::success('Success');
                        }
                        return ApiResponse::error('Unable to delete item');
                    }

                    // handle the issue with the status
                    if (strtolower($args[0]) == 'disable' || strtolower($args[0]) == 'enable') {
                        $operation = $args[0];
                        $id = $args[1];
                        $status = false;
                        if ($operation == 'disable') {
                            $status = $this->disable($entity, $id);
                        } else {
                            $status = $this->enable($entity, $id);
                        }
                        return $status ? ApiResponse::success('Success') : ApiResponse::error('Unable to disable item');
                    }
                }
                return null;
            }

            return ApiResponse::error('Resource not found', null, 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }

    }

    private function validateHeader(string $entity, array $header): bool
    {
        $entity = loadClass($entity);
        return $entity::$bulkUploadField == $header;
    }

    private function processBulkUpload(string $entity)
    {
        $entity = loadClass($entity);
        $message = 'success';
        $content = $this->getUploadedFileContent($message);
        if (!$content) {
            return ApiResponse::error('Not uploaded content found');
        }
        $content = trim($content);
        $array = stringToCsv($content);
        $header = array_shift($array);
        if (!$this->validateHeader($entity, $header)) {
            $message = 'column does not match, please check the column template and try again';
            return ApiResponse::error($message);
        }
        $result = $entity->bulkUpload($header, $array, $message);
        return $result ? ApiResponse::success($message) : ApiResponse::error($message);
    }

    private function getUploadedFileContent(string &$message): bool|string
    {
        return self::loadUploadedFileContent('upload_form', false, $message);
    }

    private function delete(string $entity, int $id): bool
    {
        $this->db->transBegin();
        $entity = loadClass($entity);
        if (!$entity->delete($id, $this->db)) {
            $this->db->transRollback();
            return false;
        }
        $this->db->transCommit();
        return true;
    }

    private function detail(string $entity, int $id, $param = null)
    {
        $entityDetails = new EntityDetails;
        $methodName = 'get' . ucfirst($entity) . 'Details';
        if (method_exists($entityDetails, $methodName)) {
            return $entityDetails->$methodName($id);
        }
        EntityLoader::loadClass($this, $entity);
        $this->$entity->id = $id;
        if ($this->$entity->load()) {
            return $this->$entity->toArray();
        }
        return null;
    }

    public function disable(string $model, int $id)
    {
        $this->db->transBegin();
        EntityLoader::loadClass($this, $model);
        //check that model is actually a subclass
        if (!(empty($id) === false && is_subclass_of($this->$model, $this->crudNameSpace))) {
            return null;
        }
        return $this->$model->disable($id, $this->db);
    }

    public function enable(string $model, int $id)
    {
        $this->db->transBegin();
        EntityLoader::loadClass($this, $model);
        //check that model is actually a subclass
        if (!(empty($id) === false && is_subclass_of($this->$model, $this->crudNameSpace))) {
            return false;
        }
        return $this->$model->enable($id, $this->db);
    }

    public function updateEntityAction(string $entity, int $id, array $param)
    {
        $entityCreator = new EntityCreator($this->request);
        EntityLoader::loadClass($this, $entity);
        if (method_exists($this->$entity, 'transformUpdateData')) {
            $param = $this->$entity->transformUpdateData($param);
        }
        return $entityCreator->updateAction($entity, $id, $param);
    }

    /**
     * This is to perform update on the entity
     * @param string $entity [description]
     * @param int $id [description]
     * @param array|null $param [description]
     * @return bool|string|null [type]             [description]
     */
    private function update(string $entity, int $id, array $param = null)
    {
        $entityCreator = new EntityCreator($this->request);
        $tempEntity = $entity;
        EntityLoader::loadClass($this, $entity);

        if (property_exists($this->$entity, 'allowedFields')) {
            $allowParam = $this->$entity::$allowedFields;
            if (!$this->validateAllowedParameter($param, $allowParam)) {
                return ApiResponse::error('Allowed parameters for update are:', ['parameters' => $allowParam]);
            }
        }
        return $entityCreator->update($tempEntity, $id, true, $param);
    }

    /**
     * This is to perform insertion on the entity
     * @param string $entity [description]
     * @return bool|null [type]               [description]
     */
    private function insert(string $entity)
    {
        $entityCreator = new EntityCreator($this->request);
        return $entityCreator->add($entity);
    }

    /**
     * [validateAllowedParameter description]
     * @param array $param [description]
     * @param array $allowParam [description]
     * @return bool [type]             [description]
     */
    private function validateAllowedParameter(array $param, array $allowParam): bool
    {
        foreach ($param as $key => $value) {
            if (!in_array($key, $allowParam)) {
                return false;
            }
        }
        return true;
    }

    /**
     * This is to get an entity list
     * @param string $entity [description]
     * @return array [type] [description]
     */
    private function listEntity(string $entity): array
    {
        return $this->list($entity);
    }


}