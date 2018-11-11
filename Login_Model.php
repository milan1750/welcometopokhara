<?php
/**
 * Loging Credintials
 */
class Login_Model extends CI_Model
{
	function __construct()
	{
		parent:: __construct();
	}

	// Avoid Sql Injection
	function check_injk($value)
	{
		return $this->db->escape($value);
	}

	// Check login
	function logged_in()
	{
		return $this->session->has_userdata('username');
	} 

	// Check User 
	function check_user()
	{
		if($this->logged_in()==TRUE)
		{
			return TRUE;
		}
		else
		{
			// Retrieving username and password
			$username = $this->check_injk($this->input->post('username'));
			$password = $this->check_injk(sha1($this->input->post('password')));

			// Check user exists
			if($this->db->query("select * from users_data where username = $username and status = TRUE ")->num_rows()>0)
			{
				$user_data =$this->db->query("select * from users_data where username = $username and password = $password and status = TRUE ");

				// Check password match
				if($user_data->num_rows()>0)
				{

					// Check System status
					if($this->is_system_active($user_data->row()->local_gov)==TRUE)
					{
	
						$local_gov = $user_data->row()->local_gov;
						$table_prefix =  $this->db->query("select table_prefix from system_table_gapa where id = $local_gov")->row()->table_prefix;

						$data = array(
							'username' => $username,
							'table_prefix' => $table_prefix,
							'logged_in' => TRUE
						);
						$this->session->set_userdata($data);
						return TRUE;
					}
					else
					{
						return "System is currently deactive";
					}
				}
				else
				{	
					return "Password is incorrect";
				}
			}else
			{
				return "Username doesn't exists";
			}
		}

	}

	// Check System Active
	function is_system_active($gov_id){
		if($this->db->query("select * from system_table_gapa where id = $gov_id and status = TRUE ")->num_rows()>0)
		{
			return TRUE;
		}
		else
		{
			return False;
		}
	}
}