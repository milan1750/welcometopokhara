<?php
	
	// Logout Controller
	class Logout extends CI_Controller
	{
		
		function index(){
			session_destroy();
			redirect(base_url());
		}
	}	