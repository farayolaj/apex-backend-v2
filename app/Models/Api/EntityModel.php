<?php
/**
 * The class that managers entity related data generally
 */

namespace App\Models\Api;

use App\Models\Api\EntityCreator;
use App\Traits\EntityListTrait;
use CodeIgniter\Model;
use App\Models\Api\EntityDetails;
use App\Models\FormConfig;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EntityModel
{
    use EntityListTrait;

    protected $db;

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
                        return sendApiResponse(true, 'Success', $result);
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
                        $param = $this->request->getGet(null);
                        $id = $args[0];
                        $result = $this->detail($entity, $id, $param);
                        if (!$result) {
                            return sendApiResponse(false, 'No data available');
                        }
                        return sendApiResponse(true, 'Success', $result);
                    } elseif ($this->request->getMethod() === 'POST') {
                        $values = $this->request->getPost(null);
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
                            return sendApiResponse(true, 'Success');
                        }
                        return sendApiResponse(false, 'Unable to delete item');
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
                        return sendApiResponse($status, $status ? 'Success' : 'Unable to delete item');
                    }

                    if (strtolower($args[0]) == 'toggle_action') {
                        $operation = $args[0];
                        $id = $args[1];
                        $status = false;
                        $values = $this->request->getPost(null);
                        if ($operation === 'toggle_action') {
                            $status = $this->updateEntityAction($entity, $id, $values);
                        }
                        return sendApiResponse($status, $status ? 'Success' : 'Unable to disable item');
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
                            return sendApiResponse(true, 'Success');
                        }
                        return sendApiResponse(false, 'Unable to delete item');
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
                        return sendApiResponse($status, $status ? 'Success' : 'Unable to disable item');
                    }
                }
                return null;
            }

            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Resource not found']);
        } catch (\Exception $e) {
            return sendApiResponse(false, $e->getMessage());
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
        $content = $this->loadUploadedFileContent($message);
        if (!$content) {
            displayJson(false, 'not uploaded content found');
            return false;
        }
        $content = trim($content);
        $array = stringToCsv($content);
        $header = array_shift($array);
        if (!$this->validateHeader($entity, $header)) {
            $message = 'column does not match, please check the column template and try again';
            displayJson(false, $message);
            return false;
        }
        $result = $entity->bulkUpload($header, $array, $message);
        displayJson($result, $message);
    }

    private function loadUploadedFileContent(string &$message)
    {
        $filename = 'upload_form';
        $status = $this->checkFile($filename, $message);
        if (!$status) {
            return false;
        }
        if (!endsWith($_FILES[$filename]['name'], '.csv')) {
            $message = "invalid file format";
            return false;
        }
        $path = $_FILES[$filename]['tmp_name'];
        $content = file_get_contents($path);
        return $content;
    }

    private function checkFile(string $name, string &$message = null)
    {
        $error = !$_FILES[$name]['name'] || $_FILES[$name]['error'];
        if ($error) {
            if ((int)$error === 2) {
                $message = 'file larger than expected';
                return false;
            }
            return false;
        }

        if (!is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $this->db->transRollback();
            $message = 'uploaded file not found';
            return false;
        }
        return true;
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
        $entity = loadClass($entity);
        $entity->id = $id;
        if ($entity->load()) {
            return $entity->toArray();
        }
        return false;
    }

    public function disable(string $model, int $id)
    {
        $this->db->transBegin();
        $model = loadClass($model);
        //check that model is actually a subclass
        if (!(empty($id) === false && is_subclass_of($model, $this->crudNameSpace))) {
            return false;
        }
        return $model->disable($id, $this->db);
    }

    public function enable(string $model, int $id)
    {
        $this->db->transBegin();
        $model = loadClass($model);
        //check that model is actually a subclass
        if (!(empty($id) === false && is_subclass_of($model, $this->crudNameSpace))) {
            return false;
        }
        return $model->enable($id, $this->db);
    }

    public function updateEntityAction(string $entity, int $id, array $param)
    {
        $entityCreator = new EntityCreator($this->request);
        $entity = loadClass($entity);
        if (method_exists($entity, 'transformUpdateData')) {
            $param = $entity->transformUpdateData($param);
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
        $entity = loadClass($entity);

        if (property_exists($entity, 'allowedFields')) {
            $allowParam = $entity::$allowedFields;
            if (!$this->validateAllowedParameter($param, $allowParam)) {
                return sendApiResponse(false, 'allowed parameters for update are:', ['parameters' => $allowParam]);
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
     * This is to get entity list
     * @param string $entity [description]
     * @return array [type]         [description]
     */
    private function listEntity(string $entity): array
    {
        return $this->list($entity);
    }


}