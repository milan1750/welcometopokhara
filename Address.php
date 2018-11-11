<?php
/**
 * 
 */
class Address extends CI_Model
{
	private $lang,$prefix;
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->lang= $this->Lang->nepali(); 
		$this->load->model('Account_Model','account_model');
		$this->prefix = $this->session->userdata('table_prefix');
	}

	

	function get_district()
	{
		$table = $this->prefix.'address_list';
		return $this->db->query("select * from $table where status = TRUE group by district_eng order by priority asc")->result();
	}

	function get_local_gov()
	{
		$table = $this->prefix.'address_list';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}
	function get_local_gov_by_district($district)
	{
		$table = $this->prefix.'address_list';
		return $this->db->query("select * from $table where  district_eng='$district' and status = TRUE")->result();
	}
	function get_vdc(){
		$table = $this->prefix.'vdc';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}

	function get_area(){
		$table = $this->prefix.'area';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}

	function get_land_kind(){
		$table = $this->prefix.'kind';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}

	function get_type(){
		$table = $this->prefix.'home_type';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}

	function get_kind(){
		$table = $this->prefix.'home_kind';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}

	function get_use(){
		$table = $this->prefix.'home_use';
		return $this->db->query("select * from $table where status = TRUE")->result();
	}
}