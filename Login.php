<?php
	/**
	 * 
	 */
	class Login extends CI_Controller
	{
		
		function __construct()
		{
			parent :: __construct();
			$this->load->model('Login_Model','login_model');

		}

		function get_login()
		{
			echo $this->login_model->check_user();	
				
		}
	}

?>