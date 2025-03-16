<?php

/**
 * This is the Model that manages Api specific request
 */

namespace App\Models\Api;

use App\Models\Mailer1;
use App\Models\WebSessionManager;
use App\Traits\AccountTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Config\Services;
use Exception;

class WebApiModel extends Model
{
    use AccountTrait;

    protected ?RequestInterface $request;

    protected ?ResponseInterface $response;

    private Mailer1 $mailer;

    private WebSessionManager $webSessionManager;

    protected $db;

    public function __construct(RequestInterface $request = null, ResponseInterface $response = null)
    {

        parent::__construct();
        $this->db = db_connect();
        $this->request = $request;
        $this->response = $response;
        $this->webSessionManager = new WebSessionManager;
        $this->mailer = new Mailer1;
    }

    public function update_password()
    {
        $curr_password = $this->request->getPost('current_password');
        $new = $this->request->getPost('password');
        $confirm = $this->request->getPost('confirm_password');

        if (!isNotEmpty($curr_password, $new, $confirm)) {
            return sendApiResponse(false, 'Empty field detected.please fill all required field and try again');
        }

        if ($new !== $confirm) {
            return sendApiResponse(false, 'New password does not match with the confirmation password');
        }

        $customer = currentAPIUser();
        if (!$customer) {
            return sendApiResponse(false, 'Invalid users');
        }

        $id = $customer->user_id;
        $user = loadClass('users');

        if ($user->findUserProp($id)) {
            $check = decode_password(trim($curr_password), $user->data()[0]['password']);
            if (!$check) {
                return sendApiResponse(false, 'Please type-in your password correctly');
            }
        }

        $new = encode_password($new);
        $query = "update users set password = '$new' where id=?";
        if ($this->db->query($query, array($id))) {
            return sendApiResponse(true, 'You have successfully changed your password');
        } else {
            return sendApiResponse(false, 'Error occurred during operation');
        }
    }

    /**
     * [profile description]
     * @return false|string [type]
     */
    public function profile()
    {
        return $this->accountProfile();
    }

    public function candidates_office($args)
    {
        if(isset($args) && is_numeric($args[0])){
            $this->updateCandidate();
        }else{
            $this->createCandidate();
        }
    }

    public function updateCandidate(){
        $voters = loadClass('voters');
        $candidate = loadClass('offices_candidate');
        $uri = service('uri');
        $validation = Services::validation();
        $validationData = $this->request->getPost();
        $validation->setRule('office_id', 'office', 'required');
        $validation->setRule('voter_id', 'voter', 'required');
        // $validation->setRule('voter_path', 'voter image', 'uploaded[voter_path]|is_image[voter_path]|max_size[voter_path,1024]|ext_in[voter_path,png,jpg,jpeg]');

        if (!$validation->run($validationData)) {
            $errors = $validation->getErrors();
            foreach ($errors as $error) {
                return sendApiResponse(false, $error);
            }
        }
        $id = $uri->getSegment(3);
        $candidate->id = $id;
        if(!$candidate->load()){
            return sendApiResponse(false, 'Invalid candidate id');
        }

        $voters->id = $candidate->voters_id;
        if (!$voters->load()) {
            return sendApiResponse(false, 'Invalid voter id');
        }
        $voterOldOrigPath = WRITEPATH . $voters->voters_path;
        $validData = $validation->getValidated();
        $voterImage = $this->request->getFile('voter_path');

        if ($voterImage->isValid() && !$voterImage->hasMoved()) {
            $this->db->transBegin();
            $newName = $voterImage->getRandomName();
            $voterPath = 'uploads/voters';
            $voterOrigPath = WRITEPATH . $voterPath . DIRECTORY_SEPARATOR .$newName;
            $voterImage->move(WRITEPATH . $voterPath, $newName);
            $insertParam = [
                'offices_id' => $validData['office_id'],
            ];
            $this->db->table('offices_candidate')->update($insertParam, ['id' => $id]);

            $voters->voters_path = $voterPath . DIRECTORY_SEPARATOR . $newName;
            if (!$voters->update()) {
                $this->db->transRollback();
                deleteFile($voterOrigPath);
                return sendApiResponse(false, 'Error occurred during operation');
            }
            deleteFile($voterOldOrigPath);
            $this->db->transCommit();
            return sendApiResponse(true, 'Candidate successfully updated');
        }
    }

    private function createCandidate(){
        $voters = loadClass('voters');
        $validation = Services::validation();
        $validationData = $this->request->getPost();
        $validation->setRule('office_id', 'office', 'required');
        $validation->setRule('voter_id', 'voter', 'required');
        $validation->setRule('voter_path', 'voter image', 'uploaded[voter_path]|is_image[voter_path]|max_size[voter_path,1024]|ext_in[voter_path,png,jpg,jpeg]');

        if (!$validation->run($validationData)) {
            $errors = $validation->getErrors();
            foreach ($errors as $error) {
                return sendApiResponse(false, $error);
            }
        }
        $validData = $validation->getValidated();
        $voterImage = $this->request->getFile('voter_path');
        if ($voterImage->isValid() && !$voterImage->hasMoved()) {
            $this->db->transBegin();
            $newName = $voterImage->getRandomName();
            $voterPath = 'uploads/voters';
            $voterOrigPath = WRITEPATH . $voterPath . DIRECTORY_SEPARATOR .$newName;
            $voterImage->move(WRITEPATH . $voterPath, $newName);
            $insertParam = [
                'voters_id' => $validData['voter_id'],
                'sessions_id' => currentSession()->id,
            ];

            if(getSingleRecord('offices_candidate', $insertParam)){
                $this->db->transRollback();
                deleteFile($voterOrigPath);
                return sendApiResponse(false, 'Candidate already exist');
            }
            $insertParam['offices_id'] = $validData['office_id'];
            $this->db->table('offices_candidate')->insert($insertParam);
            $voters->id = $validData['voter_id'];
            if (!$voters->load()) {
                $this->db->transRollback();
                deleteFile($voterOrigPath);
                return sendApiResponse(false, 'Invalid voter id');
            }
            $voters->voters_path = $voterPath . DIRECTORY_SEPARATOR . $newName;
            if (!$voters->update()) {
                $this->db->transRollback();
                deleteFile($voterOrigPath);
                return sendApiResponse(false, 'Error occurred during operation');
            }
            $this->db->transCommit();
            return sendApiResponse(true, 'Candidate successfully added');
        }
    }

    public function dasboard_top_stats(){
        $voters = loadClass('voters');
        $votes = loadClass('votes');
        $votesSubmission = loadClass('votes_submission');
        $currentSession = currentSession();

        $payload = [
            'total_voters' => $voters::totalCount(" where status = '1' "),
            'total_votes' => $votesSubmission::totalCount(" where session_id = '$currentSession->id' "),
            'active_voters' => $votes::totalCount(" where session_id = '$currentSession->id' "),
        ];
        return sendApiResponse(true, 'success', $payload);
    }

    public function dashboard_candidate_stats(){
        $office = $this->request->getGet('office');
        $submission = loadClass('votes_submission');
        return sendApiResponse(true, 'success', $submission->getCandidateStats($office));
    }


}
