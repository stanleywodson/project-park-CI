<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{

    public function index()
    {
        $data = array(

            'title' => 'Usuários Cadastrados',
            'subtitle' => 'Listando todos os usuários',
            'users' => $this->ion_auth->users()->result(),

            'styles' => array(
                'plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css',

            ),

            'scripts' => array(
                'plugins/datatables.net/js/jquery.dataTables.min.js',
                'plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js',
                'plugins/datatables.net/js/park.js'
            )
        );

        $this->load->view('layout/header', $data);
        $this->load->view('users/index');
        $this->load->view('layout/footer');
    }



    public function core($user_id = '')
    {
        if (!$user_id) {
            // cadastrar usuário caso nao informe o id
            exit('cadastrar usuário');
        } else {
            // editar - usuário
            if ($this->ion_auth->user($user_id)->row()) {

                $perfil_atual = $this->ion_auth->get_users_groups($user_id)->row();

                $this->form_validation->set_rules('first_name', 'Nome', 'trim|required|min_length[4]|max_length[20]');
                $this->form_validation->set_rules('last_name', 'Sobre Nome', 'trim|required|min_length[4]|max_length[20]');
                $this->form_validation->set_rules('username', 'Usuário', 'trim|required|min_length[4]|max_length[20]|callback_username_check');
                $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|callback_email_check');
                $this->form_validation->set_rules('password', 'Senha', 'trim|min_length[8]');
                $this->form_validation->set_rules('confirm_password', 'Confirmar Senha', 'trim|min_length[8]|matches[password]');

                if ($this->form_validation->run()) {

                    $data = elements(
                        array(
                            'first_name',
                            'last_name',
                            'username',
                            'email',
                            'password',
                            'active'
                        ),
                        $this->input->post()
                    );


                    $password = $this->input->post('password');
                   

                    if (!$password) {
                        unset($data['password']);
                    }

                    $data = html_escape($data);

                    if ($this->ion_auth->update($user_id, $data)) {
                        $perfil_post = $this->input->post('perfil');
                        //se perfil for diferente, atualiza o grupo
                        if($perfil_atual->id != $perfil_post){
                            $this->ion_auth->remove_group($perfil_atual->id, $user_id);
                            $this->ion_auth->add_to_group($perfil_post, $user_id);
                        }
                        $this->session->set_flashdata('sucesso', 'Dados atualizados com sucesso!');
                    } else {
                        $this->session->set_flashdata('error', 'Algo deu errado');
                    }
                    redirect($this->router->fetch_class());
                } else {
                    // erro de validação
                    echo 'erro na validação..';
                    $data = array(
                        'title' => 'Editar Usuário',
                        'subtitle' => '',
                        'user' => $this->ion_auth->user($user_id)->row(),
                        'perfil' => $this->ion_auth->get_users_groups($user_id)->row(),
                    );

                    $this->load->view('layout/header', $data);
                    $this->load->view('users/core');
                    $this->load->view('layout/footer');
                }


                // $data = [
                //     'title' => 'Editar Usuário',
                //     'subtitle' => '',
                //     'user' => $this->ion_auth->user($user_id)->row(),
                //     'perfil' => $this->ion_auth->get_users_groups($user_id)->row(), 
                // ];

                // $this->load->view('layout/header', $data);
                // $this->load->view('users/core');
                // $this->load->view('layout/footer');

            } else {
                exit('usuário não existe');
            }
        }
    }
    //essa função e referente a validação do metodo core
    public function username_check($username)
    {
        $user_id = $this->input->post('user_id');

        if ($this->core_model->getById('users', array('username' => $username, 'id !=' => $user_id))) {

            $this->form_validation->set_message('username_check', 'Esse usuário já existe');

            return false;
        } else {
            return true;
        }
    }
    //essa função e referente a validação do metodo core
    public function email_check($email)
    {
        $user_id = $this->input->post('user_id');

        if ($this->core_model->getById('users', array('email' => $email, 'id !=' => $user_id))) {

            $this->form_validation->set_message('username_check', 'Esse email já existe');

            return false;
        } else {
            return true;
        }
    }
}
