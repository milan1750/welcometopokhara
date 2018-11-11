<?php
/**
 * 
 */
class Curd extends CI_Model
{
	public  $lang,$prefix;
	function __construct(){
		parent:: __construct();
		$this->lang= $this->Lang->nepali(); 
		$this->load->model('Account_Model','account_model');
		$this->prefix = $this->session->userdata('table_prefix');
	}


	function get_local_gov()
	{
		$table = $this->prefix.'address_list';
		$district = $this->input->post('district');
		$local_gov = $this->db->query("select * from $table where district_eng = '$district' and status = TRUE ")->result();
		$local_gov_list = array();
		foreach ($local_gov as $item) {
			$local_gov_list[$item->id] = $item->local_gov_nep;
		}
		return $local_gov_list;
	}

	function get_local_gov_ward()
	{
		$table = $this->prefix.'address_list';
		$local_gov = $this->input->post('local_gov');
		$local_gov = $this->db->query("select total_wards from $table where id = '$local_gov' and status = TRUE ")->row();
		$local_ward_list = array();
		for($i=1;$i<=$local_gov->total_wards;$i++)
		{
			$local_ward_list[$i] = $this->numc->change_number_nep($i);
		}
		return $local_ward_list;
	}

	function get_road_type()
	{
		$table = $this->prefix.'road_type';
		$ward = $this->input->post('ward');
		$area = $this->input->post('area');
		$area_data = $this->db->query("select * from $table where ward_eng = '$ward' and area_eng = '$area' and status = TRUE ")->result();
		$type_list = array();
		foreach ($area_data as $item) {
			$type_list[$item->id] = $item->type_nep;
		}

		return $type_list;
	}

	function get_tax_rate()
	{
		$table = $this->prefix.'road_type';
		$r = $this->numc->change_number_eng($this->input->post('r'));
		$a = $this->numc->change_number_eng($this->input->post('a'));
		$p = $this->numc->change_number_eng($this->input->post('p'));
		$d = $this->numc->change_number_eng($this->input->post('d'));
	
		$ward = $this->input->post('ward');
		$area = $this->input->post('area');
		$road_type = $this->input->post('road_type');
		$tax =  $this->db->query("select * from $table where ward_eng = '$ward' and area_eng = '$area' and id= '$road_type' and status = TRUE");
		if($tax->num_rows()>0){
			$tax = $tax->row();
			$land_rate = $tax->eng_rate;

			$total_land = $this->numc->change_number_nep(($r+$a/16+$p/64+$d/256)* $land_rate);

			return array(
				'land_rate' => $this->numc->change_number_nep($land_rate),
				'total_land' => $total_land
			);
		}else{
			return array(
				'land_rate' => '-',
				'total_land' => '-'
			);
		}

		
	}
	function add_payer()
	{
		$table = $this->prefix.'payer_list';
		header('Content-Type: text/plain; charset=UTF-8');

		//fetchinh data from post
		$payer_name = $this->input->post('payer_name');
		$payer_ward = $this->input->post('payer_ward');
		$payer_address = $this->input->post('payer_address');
		$payer_contact = $this->input->post('payer_contact');
		$payer_citizenship = $this->input->post('payer_citizenship');
		$payer_district = $this->input->post('payer_district');
		$payer_local_gov = $this->input->post('payer_local_gov');
		$payer_father = $this->input->post('payer_father');
		$payer_gf = $this->input->post('payer_gf');

		//fetchong userid
		$user_data=$this->account_model->get_userdata();
		$user_id = $user_data->id;

		//date
		$date_eng = date('Y:m:d H:i:s');

		//retrieving first character
		$f_char = mb_substr($payer_name,0,1);

		$f_char_number = $this->numc->change_number_eng($this->numc->getNumber($f_char));

		//generating ID  
		$generated_id = "0001";
		$last_user_id = $this->db->query("select max(CAST(SUBSTRING(payer_eng_id,7,4) AS UNSIGNED)) as p_id from jaljala_payer_list where first_letter = '$f_char_number' ")->row()->p_id;
		if($last_user_id>0){
			$generated_id=$last_user_id+1;
			if($generated_id>0 && $generated_id<10){
				$generated_id= '000'.$generated_id;
			}else if($generated_id>=10 && $generated_id<100){
				$generated_id= '00'.$generated_id;
			}else if($generated_id>=100 && $generated_id<1000){
				$generated_id= '0'.$generated_id;
			}
		}

		$system = $this->account_model->get_systemdata($user_data->local_gov);
		$system_mun =$system->name_eng;
		$p_ward = $payer_ward;

		$table = $this->prefix.'address_list';
		$payer_mun = $this->db->query("select local_gov_eng from $table where id='$payer_local_gov' and status = TRUE ")->row()->local_gov_eng;
		if($system_mun != $payer_mun){
			$p_ward = '00';
		}

		//managing ids
		$payer_eng_id = $p_ward.'-'.$this->numc->change_number_eng($this->numc->getNumber($f_char)).'-'.$generated_id;
		$payer_nep_id = $this->numc->change_number_nep($p_ward).'-'.$this->numc->getNumber($f_char).'-'.$this->numc->change_number_nep($generated_id);


		//date convertor
		//echo $this->numc->change_number_nep($ndate['date']);
		$table = $this->prefix.'payer_list';
		$this->db->query("insert into $table (name, address, contact, citizenship,payer_eng_id,payer_nep_id,inserted_date,updated_date,inserted_by,updated_by,payer_district,payer_local_gov,ward,payer_father,payer_gf,first_letter) values ('$payer_name','$payer_address','$payer_contact','$payer_citizenship','$payer_eng_id','$payer_nep_id','$date_eng','','$user_id','','$payer_district','$payer_local_gov','$payer_ward','$payer_father','$payer_gf','$f_char_number') ");

		$table = $this->prefix.'address_list';
		if($this->db->affected_rows()>0)
		{
			$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
			return array(
				'0' => TRUE,
				'1' => $this->db->insert_id(),
				'2' => 'करदाताकाे विवरण रुजु गर्नुहाेस',
				'3' => array(
					'insd' =>$this->lang['inserted_date'],
					'year'  => $this->numc->change_number_nep($ndate['year']),
					'month' => $this->numc->change_number_nep($ndate['month']),
					'date'  => $this->numc->change_number_nep($ndate['date']),
					'ndate' => $this->numc->getNepaliMonth($ndate['nmonth']),
					'day'	=> $this->numc->getNepaliDay($ndate['day'])),
				$this->lang['generated_id']  => $payer_nep_id,
				$this->lang['payer_name']	=> $payer_name,
				$this->lang['father']	=> $payer_father,
				$this->lang['gf']	=> $payer_gf,
				$this->lang['payer_district'] =>$this->db->query("select district_nep from $table where district_eng = '$payer_district'")->row()->district_nep,
				$this->lang ['payer_local_gov'] => $this->db->query("select local_gov_nep from $table where id = '$payer_local_gov'")->row()->local_gov_nep,
				$this->lang['payer_ward']	=> $this->numc->change_number_nep($payer_ward),
				$this->lang['payer_address'] => $payer_address,
				$this->lang['payer_contact'] => $payer_contact,
				$this->lang['payer_citizenship'] => $payer_citizenship);
		}
		else
		{
			return array(
				'process' => FALSE
			);
		}
	}

	function update_payer_info()
	{
		header('Content-Type: text/plain; charset=UTF-8');

		//fetchinh data from post
		$payer_id=$this->input->post('id');

		$payer_name = $this->input->post('payer_name');
		$payer_name  = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',$payer_name);

		
		$payer_ward = $this->input->post('payer_ward');
		$payer_address = $this->input->post('payer_address');
		$payer_contact = $this->input->post('payer_contact');
		$payer_citizenship = $this->input->post('payer_citizenship');
		$payer_district = $this->input->post('payer_district');
		$payer_local_gov = $this->input->post('payer_local_gov');
		$payer_father = $this->input->post('payer_father');
		$payer_gf = $this->input->post('payer_gf');

		//fetchong userid
		$user_data=$this->account_model->get_userdata();
		$user_id = $user_data->id;

		//getting eng_id
		$table = $this->prefix.'payer_list';
		$payer_eng_id = $this->db->query("select payer_eng_id from $table where id='$payer_id' ")->row()->payer_eng_id;

		$serial  = substr($payer_eng_id, 6);
		

		//retrieving first character
		$f_char = mb_substr($payer_name,0,1);

		$f_char_number = $this->numc->change_number_eng($this->numc->getNumber($f_char));

		//generating ID  
		$generated_id = "0001";

		$last_user_id = $this->db->query("select max(CAST(SUBSTRING(payer_eng_id,7,4) AS UNSIGNED)) as p_id from jaljala_payer_list where first_letter = '$f_char_number' ")->row()->p_id;

		




		if($last_user_id>0){
			if(substr($payer_eng_id,3,2) != $f_char_number){
				$generated_id=$last_user_id+1;
				if($generated_id>0 && $generated_id<10){
					$generated_id= '000'.$generated_id;
				}else if($generated_id>=10 && $generated_id<100){
					$generated_id= '00'.$generated_id;
				}else if($generated_id>=100 && $generated_id<1000){
					$generated_id= '0'.$generated_id;
				}
			}else{
				$generated_id=substr($payer_eng_id,6)+0;
				if($generated_id>0 && $generated_id<10){
					$generated_id= '000'.$generated_id;
				}else if($generated_id>=10 && $generated_id<100){
					$generated_id= '00'.$generated_id;
				}else if($generated_id>=100 && $generated_id<1000){
					$generated_id= '0'.$generated_id;
				}
			}
			
		}

		$system = $this->account_model->get_systemdata($user_data->local_gov);
		$system_mun =$system->name_eng;
		$p_ward = $payer_ward;

		$table = $this->prefix.'address_list';
		$payer_mun = $this->db->query("select local_gov_eng from $table where id='$payer_local_gov' and status = TRUE ")->row()->local_gov_eng;
		if($system_mun != $payer_mun){
			$p_ward = '00';
		}

		//managing ids
		$payer_eng_id = $p_ward.'-'.$this->numc->change_number_eng($this->numc->getNumber($f_char)).'-'.$generated_id;
		$payer_nep_id = $this->numc->change_number_nep($p_ward).'-'.$this->numc->getNumber($f_char).'-'.$this->numc->change_number_nep($generated_id);

		//fetchong userid
		$user_id = $this->account_model->get_userdata()->id;;

		//date
		$date_eng = date('Y:m:d H:i:s A D');



		$first_letter = $this->numc->change_number_eng($this->numc->getNumber($f_char));
		$table = $this->prefix.'payer_list';
		$this->db->query("update  $table set name = '$payer_name', ward = '$payer_ward', address = '$payer_address', contact = '$payer_contact',  citizenship = '$payer_citizenship', updated_date = '$date_eng', updated_by = '$user_id', payer_eng_id='$payer_eng_id',payer_nep_id='$payer_nep_id', payer_district='$payer_district',payer_local_gov = '$payer_local_gov', payer_father = '$payer_father',payer_gf = '$payer_gf',first_letter = '$f_char_number' where id = '$payer_id' ");
		if($this->db->affected_rows()>0)
		{
			$table = $this->prefix.'address_list';
			$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
			return array(
				'0' => TRUE,
				'1' => $payer_id,
				'2' => 'करदाताकाे विवरण रुजु गर्नुहाेस',
				'3' => array(
					'insd' =>$this->lang['inserted_date'],
					'year'  => $this->numc->change_number_nep($ndate['year']),
					'month' => $this->numc->change_number_nep($ndate['month']),
					'date'  => $this->numc->change_number_nep($ndate['date']),
					'ndate' => $this->numc->getNepaliMonth($ndate['nmonth']),
					'day'	=> $this->numc->getNepaliDay($ndate['day'])),
				$this->lang['generated_id']  => $payer_nep_id,
				$this->lang['payer_name']	=> $payer_name,
				$this->lang['father']	=> $payer_father,
				$this->lang['gf']	=> $payer_gf,
				$this->lang['payer_district'] =>$this->db->query("select district_nep from $table where district_eng = '$payer_district'")->row()->district_nep,
				$this->lang ['payer_local_gov'] => $this->db->query("select local_gov_nep from $table where id = '$payer_local_gov'")->row()->local_gov_nep,
				$this->lang['payer_ward']	=> $this->numc->change_number_nep($payer_ward),
				$this->lang['payer_address'] => $payer_address,
				$this->lang['payer_contact'] => $payer_contact,
				$this->lang['payer_citizenship'] => $payer_citizenship);

		}
		else
		{
			return array(
				'process' => die("update  payer_list set name = '$payer_name', ward = '$payer_ward', address = '$payer_address', contact = '$payer_contact',  citizenship = '$payer_citizenship', updated_date = '$date_eng', updated_by = '$user_id' where id = '$payer_id' ")
			);
		}
		
	}


	function get_data()
	{
		$table = $this->prefix.'address_list';
		$payer_data = $this->db->query("select * from payer_list where status = TRUE ")->result();
		$output = '';
		foreach ($payer_data as $value) {
	        $output .= '<tr>'
    		. '<td>'.$value->payer_nep_id.'</td>'
    		. '<td>'.$value->name.'</td>'
    		. '<td>'.$this->db->query("select district_nep from $table where district_eng = '".$value->payer_district."'")->row()->district_nep.'</td>'
    		. '<td>'.$this->db->query("select local_gov_nep from $table where local_gov_eng = '".$value->payer_local_gov."'")->row()->local_gov_nep.'</td>'
    		. '<td>'.$this->numc->change_number_nep($value->ward).'</td>'
    		. '<td>'.$value->address.'</td>'
    		. '<td>'.$value->contact.'</td>'
    		. '<td>
    				<button class="btn btn-primary btn-sm"> <i class="fa fa-eye"></i></button>
    				<button class="btn btn-primary btn-sm"> <i class="fa fa-edit"></i></button>
    				<button class="btn btn-danger btn-sm"> <i class="fa fa-remove"></i></button>
    		</td>'
    		. '</tr>';
    	}

    	return $output;
	}

	function delete_data()
	{
		$table = $this->prefix.'payer_list';
		$id = $this->input->post('id');
		$this->db->query("delete from $table where id = '$id' ");
		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FAlSE;
		}
	}


	function save_landdata()
	{
		$r= $this->numc->change_number_eng($this->input->post('r'));
		$a = $this->numc->change_number_eng($this->input->post('a'));
		$p = $this->numc->change_number_eng($this->input->post('p'));
		$d = $this->numc->change_number_eng($this->input->post('d'));
	
		$vdc = $this->input->post('vdc');
		$payer_id = $this->input->post('payer_id');
		$vdc_ward = $this->input->post('vdc_ward');
		$ward = $this->input->post('ward');
		$area = $this->input->post('area');
		$road_type = $this->input->post('road_type');
		$reg_no = $this->input->post('reg_no');
		$location = $this->input->post('location');

		//date
		$inserted_date = date('Y-m-d H:i:s');

		//getting eng_id
		$user_id = $this->account_model->get_userdata()->id;

		$fy=$this->system->get_data()->fy;

		$started_fy =  $this->system->get_data()->started_fy;
	
		$table = $this->prefix.'road_type';
		$rate =  $this->db->query("select * from $table where id= '$road_type' and status = TRUE")->row();

		$land_rate = $rate->eng_rate;

		$price = ($r+$a/16+$p/64+$d/256)* $land_rate;


		
		
		$total_land = $this->numc->change_number_nep($price);

		$table = $this->prefix.'land_list';
		$this->db->query("insert into $table (payer_id,vdc,vdc_ward,ward,area,road_type,reg_no,r,a,p,d,inserted_date,inserted_by,location,inserted_fy,price) values ('$payer_id','$vdc','$vdc_ward','$ward','$area','$road_type','$reg_no','$r','$a','$p','$d','$inserted_date','$user_id','$location','$fy','$price')");

		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FALSE;
		}
	}

	function save_homedata()
	{
		$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
		$today = $ndate['year'];
		$f= $this->numc->change_number_eng($this->input->post('f'));
		$l = $this->numc->change_number_eng($this->input->post('l'));
		$b =$this->numc->change_number_eng ($this->input->post('b'));
		$ar = $this->numc->change_number_eng($this->input->post('ar'));
	
		$payer_id = $this->input->post('payer_id');
		$home_type = $this->input->post('home_type');
		$home_kind = $this->input->post('home_kind');
		$home_use = $this->input->post('home_use');
		$c_date = $this->numc->change_number_eng($this->input->post('c_date'));
		$reg_no = $this->numc->change_number_eng($this->input->post('reg_no'));

		//date
		$inserted_date = date('Y-m-d H:i:s');

		//getting eng_id
		$user_id = $this->account_model->get_userdata()->id;

		$fy=$this->system->get_data()->fy;

		$started_fy =  $this->system->get_data()->started_fy;

		$fine = 0;

		$table = $this->prefix.'debt_account';
		$prev_record = $this->db->query("select * from $table where payer_id = $payer_id and status = TRUE order by id desc limit 1");

		$table = $this->prefix.'home_kind';
		$rate = $this->db->query("select * from $table where kind_eng = '$home_kind' and status = TRUE ")->row()->rate;
		$home_price = $f*$l*$b*2.25*$rate;

		


		
		$home_detail = $this->db->query("select kind_nep from $table where kind_eng = '$home_kind' and status = TRUE ")->row()->kind_nep;
		$table = $this->prefix.'home_list';

		if($ar !=''){
			$this->db->query("insert into $table (payer_id,home_type,home_kind,home_use,price,constructed_date,reg_no,area,home_detail,inserted_date,inserted_by, inserted_fy) values ('$payer_id','$home_type','$home_kind','$home_use','$home_price','$c_date','$reg_no','$ar','$home_detail','$inserted_date','$user_id','$fy')");

		}else{
			$ar=0;
			$ar = $f*$l*$b*2.25;
			$this->db->query("insert into $table (payer_id,home_type,home_kind,home_use,price,constructed_date,reg_no,length,breadth,floor,area,home_detail,inserted_date,inserted_by,inserted_fy) values ('$payer_id','$home_type','$home_kind','$home_use','$home_price','$c_date','$reg_no','$l','$b','$f','$ar','$home_detail','$inserted_date','$user_id','$fy')");

	
		}
		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FALSE;
		}
	}

	function update_land_data()
	{
		$id = $this->input->post('id');
		$r= $this->numc->change_number_eng($this->input->post('r'));
		$a = $this->numc->change_number_eng($this->input->post('a'));
		$p = $this->numc->change_number_eng($this->input->post('p'));
		$d = $this->numc->change_number_eng($this->input->post('d'));
	
		$vdc = $this->input->post('vdc');
		$payer_id = $this->input->post('payer_id');
		$vdc_ward = $this->input->post('vdc_ward');
		$ward = $this->input->post('ward');
		$area = $this->input->post('area');
		$road_type = $this->input->post('road_type');
		$reg_no = $this->input->post('reg_no');
		$location = $this->input->post('location');

		//date
		$inserted_date = date('Y-m-d H:i:s');

		//getting eng_id
		$user_id = $this->account_model->get_userdata()->id;

		$fy=$this->system->get_data()->fy;

		$started_fy =  $this->system->get_data()->started_fy;
	
		$table = $this->prefix.'road_type';
		$rate =  $this->db->query("select * from $table where id= '$road_type' and status = TRUE")->row();

		$land_rate = $rate->eng_rate;

		$price = ($r+$a/16+$p/64+$d/256)* $land_rate;


		
		
		$total_land = $this->numc->change_number_nep($price);

		$table = $this->prefix.'land_list';
		$this->db->query("update $table set vdc = '$vdc',vdc_ward='$vdc_ward',ward='$ward',area='$area',road_type='$road_type',reg_no='$reg_no',r=$r,a=$a,p=$p,d=$d,inserted_date='$inserted_date',inserted_by=$user_id,location='$location',inserted_fy='$fy',price='$price' where id = $id ");

		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FALSE;
		}
	}

	function delete_land_data()
	{
		$table = $this->session->userdata('table_prefix').'land_list';
		$id = $this->input->post('id');
		$this->db->query("update $table set status = FALSE where id = $id ");
		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FALSE;
		}
	}

	function delete_home_data()
	{
		$table = $this->session->userdata('table_prefix').'home_list';
		$id = $this->input->post('id');
		$this->db->query("update $table set status = FALSE where id = $id ");
		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FALSE;
		}
	}

	function remove_transaction()
	{
		$table = $this->session->userdata('table_prefix').'transaction';

		$id = $this->input->post('id');

		$this->db->query("update $table set status = FALSE where id = $id ");

		if($this->db->affected_rows()>0){
			echo TRUE;
		}else{
			echo FALSE;
		}

	}
}