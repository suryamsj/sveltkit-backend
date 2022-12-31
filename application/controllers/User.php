<?php


defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';


class User extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate a uniqe string
     *
     */
    private function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0C2f) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0x2Aff),
            mt_rand(0, 0xffD3),
            mt_rand(0, 0xff4B)
        );
    }


    /**
     * Get All Data from this method.
     *
     * @return Response
     */
    public function index_get($uuid = 0)
    {
        if (!empty($uuid)) {
            $data = $this->db->select('id, uuid, name, phone_number, email')->get_where("users", ['uuid' => $uuid])->row_array();
        } else {
            $data = $this->db->select('id, uuid, name, phone_number, email')->get('users')->result();
        }
        $this->response($data, REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('phone_number', 'Phone Number', 'trim|required|numeric');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');

        if ($this->form_validation->run() == FALSE)    #3
        {
            $this->response([
                "status" => REST_Controller::HTTP_BAD_GATEWAY,
                "error" => strip_tags($this->form_validation->error_string())
            ]);
        }

        $id = $this->generate_uuid();
        $Hashpassword = password_hash($this->input->post('password', true), PASSWORD_DEFAULT);
        $data = [
            "uuid" => $id,
            "name" => $this->input->post('name', true),
            "phone_number" => $this->input->post('phone_number', true),
            "email" => $this->input->post('email', true),
            "password" => $Hashpassword,
        ];
        $insert = $this->db->insert('users', $data);
        if ($insert) {
            $this->response([
                "status" => REST_Controller::HTTP_CREATED,
                "message" => "User created successfully"
            ]);
        } else {
            $this->response([
                "status" => REST_Controller::HTTP_BAD_GATEWAY,
                "message" => "Failed to create user"
            ]);
        }
    }

    /**
     * Get All Data from this method.
     *
     * @return Response
     */
    public function index_put($uuid)
    {
        $input = $this->put();
        $update = $this->db->update('users', $input, array('uuid' => $uuid));
        if ($update) {
            $this->response([
                $input,
                "status" => REST_Controller::HTTP_OK,
                "message" => "User updated successfully"
            ]);
        } else {
            $this->response([
                "status" => REST_Controller::HTTP_BAD_GATEWAY,
                "message" => "Failed to update user"
            ]);
        }
    }

    /**
     * Get All Data from this method.
     *
     * @return Response
     */
    public function index_delete($uuid)
    {
        $delete = $this->db->delete('users', array('uuid' => $uuid));
        if ($delete) {
            $this->response([
                "status" => REST_Controller::HTTP_OK,
                "message" => "User deleted successfully."
            ]);
        } else {
            $this->response([
                "status" => REST_Controller::HTTP_BAD_GATEWAY,
                "message" => "Failed to delete user"
            ]);
        }
    }
}

/* End of file User.php and path \application\controllers\User.php */
