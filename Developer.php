<?php
/**
 * 
 */
class Developer extends CI_Model
{
	
	function get_data()
	{
		return $this->db->query("select * from developer where status = TRUE")->row();
	}
}