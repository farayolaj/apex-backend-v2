<?php

namespace App\Controllers;

use App\Models\AdminApiModel;
use App\Models\Api\EntityModel;
use App\Models\Api\WebApiModel;
use App\Models\Api\ApiModel;
use CodeIgniter\HTTP\ResponseInterface;

class Api extends BaseController {

    /**
     * @param string $entity [description]
     * @return ResponseInterface|false|string|null [type]         [description]
     */
	public function webApi(string $entity) {
		$dictionary = getEntityTranslation();
		$args = array_slice(func_get_args(), 1);

		$method = array_key_exists($entity, $dictionary) ? $dictionary[$entity] : $entity;
		$entities = listEntities($this->db); // caching is used here for performance

		// this check if the method is equivalent to any entity model to get it equiv result
		if (in_array($method, $entities)) {
			$entityModel = new EntityModel($this->request, $this->response);
			return $entityModel->process($method, $args);
		}

		// define the set of methods in another model called WebApiModel|ApiModel
		$webApiModel = new WebApiModel($this->request, $this->response);
		if (method_exists($webApiModel, $method)) {
            $args = (!empty($args)) ? $args : null;
			return $webApiModel->$method($args);
		} else {
			return $this->response->setStatusCode(405)
				->setJSON(['status' => false, 'message' => 'Operation denied']);
		}
	}

    public function frontApi(string $entity) {
        $dictionary = getAPIEntityTranslation();
        $args = array_slice(func_get_args(), 1);

        $method = array_key_exists($entity, $dictionary) ? $dictionary[$entity] : $entity;
        $entities = listEntities($this->db); // caching is used here for performance

        // this check if the method is equivalent to any entity model to get it equiv result
        if (in_array($method, $entities)) {
            $entityModel = new EntityModel($this->request, $this->response);
            return $entityModel->process($method, $args);
        }

        // define the set of methods in another model called WebApiModel|ApiModel
        $apiModel = new ApiModel($this->request, $this->response);
        if (method_exists($apiModel, $method)) {
            $args = (!empty($args)) ? $args : null;
            return $apiModel->$method($args);
        } else {
            return $this->response->setStatusCode(405)
                ->setJSON(['status' => false, 'message' => 'Operation denied']);
        }
    }


}
