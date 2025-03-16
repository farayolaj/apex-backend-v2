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

class ApiModel extends Model
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

    public function candidate_details(){
        $candidate = loadClass('offices_candidate');
        $result = $candidate->getCandidateDetails();
        return sendApiResponse(true, 'Candidate details', $result);
    }

    public function vote_casting(){

        if(!$this->request->is('post')){
            return sendApiResponse(false, 'Invalid request');
        }

        $validation = Services::validation();
        $validationData = $this->request->getPost();
        $validation->setRule('voting.*.label', 'voting label', 'required|string');
        $validation->setRule('voting.*.data', 'voting data', 'required|numeric');

        if (!$validation->run($validationData)) {
            $errors = $validation->getErrors();
            foreach ($errors as $error) {
                return sendApiResponse(false, $error);
            }
        }
        $validData = $validation->getValidated();
        $voting = $validData['voting'];
        $currentSession = currentSession();
        $currentUser = currentAPIUser();
        $this->db->transBegin();

        if($this->db->table('votes')->where(['voter_id'=>$currentUser->id, 'session_id'=>$currentSession->id])->countAllResults() > 0){
            return sendApiResponse(false, 'You have already cast your vote');
        }

        $candidate = loadClass('offices_candidate');
        $office = loadClass('offices_post');
        $totalExpectedVote = $candidate->getCandidateTotal();
        if(count($voting) != $totalExpectedVote){
            return sendApiResponse(false, 'Invalid vote cast, expecting '. $totalExpectedVote . ' votes');
        }

        foreach($voting as $vote){
            if(!$office->getWhere(['name'=>$vote['label']], $count, 0, 1, false)){
                return sendApiResponse(false, "Invalid candidate office[{$vote['label']}]");
            }

            if(!$candidateData = $candidate->getWhere(['id'=>$vote['data'],'sessions_id'=>$currentSession->id], $count, 0, 1, false)){
                return sendApiResponse(false, "Invalid candidate[{$vote['label']}] for this session");
            }

            $candidateData = $candidateData[0];
            $voteData = [
                'voter_id' => $currentUser->id,
                'office_id' => $candidateData->offices_id,
                'session_id' => $currentSession->id,
            ];

            if($this->db->table('votes_submission')->where($voteData)->countAllResults() > 0){
                $this->db->transRollback();
                return sendApiResponse(false, "You have already cast your vote for this office[{$vote['label']}]");
            }
            $voteData['candidate_id'] = $candidateData->id;
            $voteData['status'] = 1;

            if(!$this->db->table('votes_submission')->insert($voteData)){
                $this->db->transRollback();
                return sendApiResponse(false, 'An error occurred during vote casting');
            }

            $message = "You have successfully cast your vote for {$vote['label']}";
            $voteMeta = [
                'voter_id' => $currentUser->id,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => toUserAgent($this->request->getUserAgent()),
                'raw_data' => json_encode($validationData),
                'response_data' => $message,
            ];
            $this->db->table('votes_submission_meta')->insert($voteMeta);
        }
        $this->db->table('votes')->insert([
            'voter_id' => $currentUser->id,
            'session_id' => $currentSession->id,
        ]);
        $this->db->table('user_tokens')->update(['status'=>0], ['user_id'=>$currentUser->user_id]);
        $this->db->transCommit();
        return sendApiResponse(true, 'You have successfully cast your vote');
    }


}
