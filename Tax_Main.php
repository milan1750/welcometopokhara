<?php
/**
 * 
 */
class Tax_Main extends CI_Controller
{
	function __Construct(){
		parent::__Construct();
		$this->load->model('Login_Model','login_model');
		$this->load->model('System','system');
		$this->load->model('Account_Model','account_model');
		$this->load->model('Number_Convertor','numc');
		$this->load->model('Number_Convertor','numc');
		$this->load->model('Nepali_Calendar','nc');

	}
	
	function index(){
		if($this->login_model->logged_in()==TRUE)
		{
			$this->load->view('users/dashboard');
		}
		else
		{
			$this->load->view("system/home_page");
		}
	}
}