
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Kathmandu');

class Setting extends CI_Controller {

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

	function get_setting()
	{
		$this->load->view('users/setting.php');
	}


	function update_system(){
		echo $this->system->update_system();
	}

	function change_status(){
		echo $this->system->change_status();
	}

	function user_data(){
		$this->load->view('users/pages/user_data');
	}

	function delete_user(){
		echo $this->system->delete_user();
	}

	function add_user_data(){
		echo $this->system->add_user_data();
	}

	function add_new_road_type(){
		echo $this->system->add_new_road_type();
	}

	function road_type_data(){
		$this->load->view('users/pages/road_type_data');
	}

	function user(){
		$this->load->view('users/users');
	}

}