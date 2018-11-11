
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Kathmandu');

class Stats extends CI_Controller {

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
		$this->load->model('Login_Model','login_model');
		$this->_salt = "";
		if($this->login_model->logged_in() == FALSE)redirect(base_url());

	}

	function get_stats()
	{
		$this->load->view('users/stats');
	}
}