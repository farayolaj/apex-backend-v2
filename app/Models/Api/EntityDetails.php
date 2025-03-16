<?php 

/**
 * This will get different entity details that can be use inside the APIs
 */
namespace App\Models\Api;

class EntityDetails
{
	public function __construct()
    {
        helper('string');
    }

    public function getVotersDetails(int $id)
    {
        # Get something
        $entity = loadClass('voters');
        $entity->id = $id;
        if(!$entity->load()){
            return sendApiResponse(false, 'Voter not found');
        }
        $entity->voters_path = base_url($entity->voters_path);
        return $entity->toArray();
    }

    public function getOffices_candidateDetails(int $id)
    {
        # Get something
        $entity = loadClass('offices_candidate');
        $voters = loadClass('voters');
        $entity->id = $id;
        if(!$entity->load()){
            return sendApiResponse(false, 'Candidate not found');
        }
        $voters->id = $entity->voters_id;
        if(!$voters->load()){
            return sendApiResponse(false, 'Candidate info not found');
        }
        $result = $entity->toArray();
        $result['voters_path'] = base_url($voters->voters_path);
        return $result;
    }


}

