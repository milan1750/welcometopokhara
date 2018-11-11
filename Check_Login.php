<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Check_Login extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Account_Model','account_model');
		$this->load->model('Nepali_Calendar','nc');
		$this->load->model('Number_Convertor','numc');
		$this->load->model('Curd','curd');
		$this->load->model('Address','address');
		$this->load->model('Developer','developer');
		$this->load->model('Bill','bill');
		$this->load->model('System','system');
	}
	function index()
	{
		if($this->account_model->logged_in() == TRUE)
		{
			$this->dashboard(TRUE);
		}
		else
		{
			$this->load->view('login');
		}
	}
	function dashboard($condition = FALSE)
	{
		if($condition === TRUE OR $this->account_model->logged_in() === TRUE)
		{
			$this->load->view('users/dashboard');
		}
		else
		{
			$this->load->view('login');
		}
	}
	function login()
	{
		$this->form_validation->set_rules('username', 'Username','xss_clean|required');
		$this->form_validation->set_rules('password', 'Password','xss_clean|required|min_length[4]|max_length[12]|sha1|callback_password_check');
		$this->_username = $this->input->post('username');
		$this->_password =sha1($this->_salt . $this->input->post('password'));
		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('login');
		}
		else
		{
			$this->account_model->login();
			$data['message'] ="You are logged in! Now go take a look at the ".anchor('account/dashboard', 'Dashboard');
			$this->load->view('account/success', $data);
		}
	}

	
}
