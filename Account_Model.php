<?php

class Account_Model extends CI_Model
{
	public $lang;
	public $prefix;
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->lang= $this->Lang->nepali(); 
		$this->prefix = $this->session->userdata('table_prefix');
	}

	function create($data)
	{
		if($this->db->insert('users', $data))
		return TRUE;
	}



	function get_userdata()
	{
		if($this->session->has_userdata('username'))
		{
			$username = $this->session->userdata('username');
			return $this->db->query("select * from users_data where status  = TRUE and username = $username ")->row();
		}
		else
		{
			return FALSE;
		}
	}
	function get_userdata_by_id($id)
	{
		return $this->db->query("select * from users_data where status  = TRUE and id = '$id' ");
	}

	function get_users_data()
	{
		return $this->db->query("select * from users_data ")->result();
	}

	function get_systemdata($id)
	{
		$userdata = $this->get_userdata();
		$local_gov = $userdata->local_gov;
		return $this->db->query("select * from system_table_gapa where id = $local_gov and status = TRUE" )->row();
	}

	function get_payer_data($id){
		$table = $this->prefix.'payer_list';
		return $this->db->query("select * from $table where id= '$id' and status = TRUE")->row();
	}

	function get_payers_data(){
		$table = $this->prefix.'payer_list';
		return $this->db->query("select * from $table where status = TRUE order by id asc")->result();
	}

	function get_payerdata_by_id($id)
	{
		$table = $this->prefix.'land_list';
		return $this->db->query("select * from $table where payer_id = '$id' and status = TRUE order by id asc")->result();

	}

	function get_payer_home_data_by_id($payer_id){
		$table = $this->prefix.'home_list';
		return $this->db->query("select * from $table where payer_id = '$payer_id' and status = TRUE order by id asc")->result();
	}

	function get_taxdata($payer_id){
		$table = $this->prefix.'transaction';
		return $this->db->query("select * from $table where payer_id = '$payer_id' and status = TRUE order by id desc")->result();
	}

	function check_status($payer_id){
		$table = $this->prefix.'transaction';
		$tax_data = $this->db->query("select * from $table where payer_id = '$payer_id' and status = TRUE order by id desc limit 1");
		$fy = $this->get_systemdata(true)->fy;
		if($tax_data->num_rows()>0){
			if($fy==$tax_data->row()->fy){
				return FALSE;
			}else{
				return $fy;
			}
		}else{
			return $fy;
		}
	}

	function get_vdc_data(){
		$table = $this->prefix.'vdc';
		return $this->db->query("select * from $table ")->result();

	}

	function get_area_data(){
		$table = $this->prefix.'area';
		return $this->db->query("select * from $table ")->result();

	}

	function get_road_type_data(){
		$table = $this->prefix.'road_type';
		return $this->db->query("select * from $table ")->result();

	}

	function get_home_type_data(){
		$table = $this->prefix.'home_type';
		return $this->db->query("select * from $table ")->result();
	}

	function get_home_kind_data(){
		$table = $this->prefix.'home_kind';
		return $this->db->query("select * from $table ")->result();
	}
	function get_home_use_data(){
		$table = $this->prefix.'home_use';
		return $this->db->query("select * from $table ")->result();
	}

	function save_debt(){
		$user_data = $this->account_model->get_userdata();
		$system_data = $this->account_model->get_systemdata(TRUE  );
		$payer_id=$this->input->post('id');
		$amount = $this->input->post('debt_amount');
		$particular = $this->lang['old_debt'];

		$fy = $system_data->fy;
		$user_id = $user_data->id;
		$date =date('Y:m:d H:i:s');

		$table = $this->prefix.'debt_account_details';

		$this->db->query("insert into $table (particular,payer_id,fy,debt_amount,inserted_by,inserted_date) values ('$particular','$payer_id','$fy','$amount','$user_id','$date') ");
		$table = $this->prefix.'debt_account';
		$this->db->query("insert into  $table (payer_id,debt,inserted_by,inserted_fy,inserted_date) values ('$payer_id','$amount', '$user_id','$fy','$date') ");


		if($this->db->affected_rows()>0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function get_local_gov_data(){
		$table = $this->prefix.'address_list';
		return $this->db->query("select * from $table")->result();

	}

	function get_landdata($id)
	{
		$table = $this->prefix.'land_list';
		return $this->db->query("select * from $table where id=$id")->row();
	}
	function get_homedata($id)
	{
		$table = $this->prefix.'home_list';
		return $this->db->query("select * from $table where id=$id")->row();
	}

	function get_area_type($type)
	{
		$table = $this->prefix.'road_type';
		return $this->db->query("select type_nep from $table where id = $type ")->row()->type_nep;
	}

	function get_area($area)
	{
		$table = $this->prefix.'area';
		return $this->db->query("select area_nep from $table where area_eng = '$area' ")->row()->area_nep;
	}

	function filter_data()
	{
		/* $group = $this->input->post('group');
		$data = $this->numc->change_number_eng($this->input->post('data'));


		$group_data = array('null','payer_eng_id','name','payer_father','payer_gf','contact');

		$group = $group_data[$this->input->post('group')];
		*/

		$user_data = $this->get_userdata();

		$table = $this->prefix.'payer_list';

		$sql = "select * from $table where  status = TRUE ";
		$reuslt_data = $this->db->query($sql);

		$output = array();
		$button='';
		$i=0;
		if($reuslt_data->num_rows()>0)
		{
			foreach ($reuslt_data->result() as $value) 
			{
				if($user_data->user_type==1){
					$output[$i]= array(
						'p_id' => $value->id,
						'id' => $value->payer_nep_id,
						'name' => $value->name,
						'father_name' => $value->payer_father,
						'gf' => $value->payer_gf,
						'contact' => $this->numc->change_number_nep($value->contact),
						'view' => '<button class="btn btn-xs btn-primary" id="view-payer" data-id="'.$value->id.'"> <i class="fa fa-eye"></i></button>		
							<button class="btn btn-danger btn-xs" id="edit-payer" data-id="'.$value->id.'"> <i class="fa fa-edit" ></i></button>
							<button class="btn btn-primary btn-xs" id="delete-payer" data-id="'.$value->id.'" data-name="'.$value->name.'" data-pid="'.$value->payer_nep_id.'"data-target="#confirm-delete"> <i class="fa fa-remove"></i></button>'
					);
    			}else{
    				$output[$i]= array(
						'p_id' => $value->id,
						'id' => $value->payer_nep_id,
						'name' => $value->name,
						'father_name' => $value->payer_father,
						'gf' => $value->payer_gf,
						'contact' => $this->numc->change_number_nep($value->contact),
						'view' => '<button class="btn btn-xs btn-primary" id="view-payer" data-id="'.$value->id.'" > <i class="fa fa-eye"></i></button>'
					);
    			}
				
				$i++;
			}

			echo json_encode($output);

		}else{
			echo json_encode($output);
		}
	}


}
