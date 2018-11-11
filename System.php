<?php
/**
 * 
 */

date_default_timezone_set('Asia/Kathmandu');
class System extends CI_Model
{

	function main_system(){
		return $this->db->query("select * from main_system where status = TRUE ")->row();
	}
	
	function get_data()
	{
		return $this->db->query("select * from system_table_gapa where status = TRUE")->row();
	}

	function update_system()
	{
		$name = $this->input->post('name');
		$address = $this->input->post('address');
		$description = $this->input->post('description');
		$email = $this->input->post('email');
		$started_fy = $this->numc->change_number_eng($this->input->post('started_fy'));
		$fy = $this->numc->change_number_eng($this->input->post('fy'));
		$loginfo = $this->input->post('loginfo');
		$wards = $this->numc->change_number_eng($this->input->post('wards'));

		$user_data = $this->account_model->get_userdata();

		$user_id = $user_data->id;

		$date = date('Y:m:d H:i:s');

		$this->db->query("update system_table_gapa set name = '$name', description = '$description', address = '$address', wards = '$wards' , loginfo = '$loginfo',fy='$fy', started_fy = '$started_fy',updated_date ='$date', updated_by = '$user_id' ");

		if($this->db->affected_rows()>0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}

	function change_status(){
		$id = $this->input->post('id');

		$status = $this->db->query("select status from users_data where id = $id ")->row()->status;

		if($status == TRUE){
			$this->db->query("update users_data set status = 0 where id = $id");
		}else{
			$this->db->query("update users_data set status = 1 where id = $id");

		}
		if($this->db->affected_rows()>0){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	function delete_user(){
		$id = $this->input->post('id');
		$this->db->query("delete from users_data where id = $id ");
		if($this->db->affected_rows()>0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function add_user_data(){
		$username = $this->input->post('username');
		$email = $this->input->post('email');
		$password = sha1($this->input->post('password'));

		$this->db->query("insert into users_data (username,password,email,user_type,status) values ('$username','$password','$email','2','1') ");

		if($this->db->affected_rows()>0){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	function add_new_road_type(){
		$vdc = $this->input->post('vdc');
		$vdc_ward = $this->input->post('vdc_ward');
		$ward = $this->input->post('ward');
		$area = $this->input->post('area');
		$road_type = $this->input->post('road_type');
		$rate = $this->numc->change_number_eng($this->input->post('rate'));
		$road_type_eng = $this->numc->change_number_eng($this->input->post('road_type_eng'));
		
		$prefix = $this->session->userdata('table_prefix');

		//fetchong userid
		$user_id = $this->account_model->get_userdata()->id;

		//date
		$date_eng = date('Y:m:d H:i:s');

		$table = $prefix.'area';
		$area_nep = $this->db->query("select area_nep from $table where area_eng = '$area' ")->row()->area_nep;
		$ward_nep = $this->numc->change_number_nep($ward);
		$rate_nep = $this->numc->change_number_nep($rate);

		$table = $prefix.'road_type';
		$this->db->query("insert into $table (type_eng,type_nep,ward_name_nep,ward_eng,ward_nep,nep_rate,area_nep,area_eng,eng_rate,inserted_by,inserted_date) values ('$road_type_eng','$road_type','$vdc','$ward','$ward_nep','$rate_nep','$area_nep','$area','$rate','$user_id','$date_eng')");
		if($this->db->affected_rows()>0){
			return TRUE;
		}else{
			return FALSE;
		}

	}
}