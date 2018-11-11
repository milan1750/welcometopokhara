<?php 
	/**
	 * 
	 */
	class Bill extends CI_Model
	{
		
		public  $lang,$prefix;
		function __construct(){
			parent:: __construct();
			$this->lang= $this->Lang->nepali(); 
			$this->load->model('Account_Model','account_model');
			$this->prefix = $this->session->userdata('table_prefix');
		}

		function generate_new_bill($id)
		{
			/*System Data*/
			$system_data = $this->account_model->get_systemdata(TRUE); 
			/*User Data*/
			$user_data = $this->account_model->get_userdata();
			$user_id = $user_data->id;
			/*Payer Data*/
			$table = $this->prefix.'payer_list';
			$payer_data = $this->db->query("select * from $table where id='$id' and status = TRUE ");

			if($payer_data->num_rows()>0){
				$payer_info= $payer_data->row();
				$payer_name = $payer_info->name;
				$payer_id = $payer_info->id;
			}else{
				echo "no data available";exit;
			}


			$fy = $system_data->fy;

			$started_fy = $system_data->started_fy;

			//Checking if transactio done
            if($this->account_model->check_status($payer_id)!=TRUE){echo "Access Forbidden !!";exit;}                      
                           

			$payable_tax=0;
			$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
			$today = $ndate['year'];

			$date =date('Y:m:d H:i:s');
    		$y = substr($date,0,4);
    		$m = substr($date,5,2);
    		$d = substr($date,8,2);
    		$ndate = $this->nc->eng_to_nep($y, $m, $d);
    
			
			$mun = $payer_info->payer_local_gov;
			$table = $this->prefix.'address_list';
			$mun_nep = $this->db->query("select local_gov_nep from $table where id = '$mun' and status = TRUE order by id desc")->row()->local_gov_nep;
			$table = $this->prefix.'land_list';
			$land = $this->db->query("select * from $table where payer_id = '$id' and status = TRUE order by id desc");
			$table = $this->prefix.'home_list';
			$home = $this->db->query("select * from $table where payer_id='$id' and status = TRUE order by id desc");

			$bill_vdc = '';$bill_p_ward='';$bill_reg_no='';$bill_land_area='';$bill_land_area_value='';$bill_land_type='';$bill_land_price='';$bill_home_type='';$bill_floor='';$bill_home_area='';$bill_home_price='';$bill_rem='';$total_land_price='';$total_home_price='';$bill_home_kind = '';$h_reg_no='';
			$tax=0;
			$fine=0;

			$tax_paid_date = $this->lang['date'].' : '.$this->numc->change_number_nep($ndate['year']).' '.$this->numc->getNepaliMonth($ndate['month']).' '.$this->numc->change_number_nep($ndate['date']).' '.$this->lang['dday'].' '.$this->numc->getNepaliDay($ndate['day']);
			if($fy != $started_fy){
				$table = $this->prefix.'transaction';
				$last_paid = $this->db->query("select * from $table where payer_id = '$id' and status = TRUE order by id desc");
				if($last_paid->num_rows()>0){
					$last_paid_details = $last_paid->row();
					$last_paid_date = $last_paid_details->inserted_date;
					$table = $this->prefix.'land_list';
					$unpaid_tax_land = $this->db->query("select * from $table where payer_id = '$id' and inserted_date > '$last_paid_date' and status = TRUE order by id desc");
					$table = $this->prefix.'home_list';
					$unpaid_tax_home = $this->db->query("select * from $table where payer_id='$id' and inserted_date > '$last_paid_date' and status = TRUE order by id desc");
					$last_paid_fy = $last_paid_details->fy;
					foreach ($unpaid_tax_land->result() as $item):
						$year_scape = substr($fy,0,4)-substr($last_paid_fy,0,4);
						$fy1 = $last_paid_fy;
						$price = $item->price;
						$reg_no = $item->reg_no;
						for($i=1;$i<=$year_scape;$i++){
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();
						
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
							$table = $this->prefix.'debt_account_details';
							$this->db->query("insert into $table (particular,payer_id,fy,reg_no,debt_amount,fine,inserted_by,inserted_date) values ('$particular','$payer_id','$fy1','reg_no',$c_tac_amount,$c_fine,'$user_id','$date') ");
							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;
					foreach ($unpaid_tax_home->result() as $item):
						$reg_no = $item->reg_no;
						$year_scape = substr($fy,0,4)-substr($last_paid_fy,0,4);
						$fy1 = $last_paid_fy;
						$price = $item->price;
						for($i=1;$i<=$year_scape;$i++){
							$constructed_date = substr($item->constructed_date,0,4);
							$year_passed = $today - $constructed_date;
							$table = $this->prefix.'depretiation';
							$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE ");

							if($depn->num_rows()>0){
								$price = $home_price*(100-$depn->row()->rate)/100;
							}else{
								$price = $home_price;
							}
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();
						
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
							$table = $this->prefix.'debt_account_details';
							$this->db->query("insert into $table (particular,payer_id,fy,reg_no,debt_amount,fine,inserted_by,inserted_date) values ('$particular','$payer_id','$fy1','reg_no',$c_tac_amount,$c_fine,'$user_id','$date') ");
							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;
				}else{
					foreach($land->result() as $item):
						$year_scape = substr($fy,0,4)-substr($started_fy,0,4);
						$fy1 = $started_fy;
						$price = $item->price;
						$reg_no = $item->reg_no;
						for($i=1;$i<=$year_scape;$i++){
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();
						
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
							$table = $this->prefix.'debt_account_details';
							$this->db->query("insert into $table (particular,payer_id,fy,reg_no,debt_amount,fine,inserted_by,inserted_date) values ('$particular','$payer_id','$fy1','reg_no',$c_tac_amount,$c_fine,'$user_id','$date') ");
							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;
					foreach ($home->result() as $item):
						$reg_no = $item->reg_no;
						$year_scape = substr($fy,0,4)-substr($started_fy,0,4);
						$constructed_date = substr($item->constructed_date,0,4);
						$year_passed = $today - $constructed_date;
						$home_price = $item->price;
						$fy1 = $started_fy;
						for($i=1;$i<=$year_scape;$i++){
							$table = $this->prefix.'depretiation';
							$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE ");
							if($depn->num_rows()>0){
								$price = $home_price*(100-$depn->row()->rate)/100;
							}else{
								$price = $home_price;
							}
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();

							//die("select * from tax_rate where frm <= ".$price." and too >= ".$price." and fy='".$fy1."' and status = TRUE ");
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
							$table = $this->prefix.'debt_account_details';
							$this->db->query("insert into $table (particular,payer_id,fy,reg_no,debt_amount,fine,inserted_by,inserted_date) values ('$particular','$payer_id','$fy1','$reg_no',$c_tac_amount,$c_fine,'$user_id','$date') ");
							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;		
				}
				$table = $this->prefix.'debt_account';
				$this->db->query("insert into  $table (payer_id,debt,fine,inserted_by,inserted_fy,inserted_date) values ('$payer_id','$tax', '$fine', '$user_id','$fy','$date') ");
			}


			foreach ($land->result() as $item):
				$bill_vdc .= $item->vdc.'/'.$this->numc->change_number_nep($item->vdc_ward).'<br>';
				$bill_p_ward .= $this->numc->change_number_nep($item->ward).'<br>';
				$bill_reg_no .= $this->numc->change_number_nep($item->reg_no).'<br>';
				$bill_land_area .= $this->numc->change_number_nep($item->r).'-'.$this->numc->change_number_nep($item->a).'-'.$this->numc->change_number_nep($item->p).'-'.$this->numc->change_number_nep($item->d).'<br>';
				$area = $item->area;
				$table = $this->prefix.'area';
				$bill_land_type .= $this->db->query("select * from $table where area_eng = '$area' and status = TRUE ")->row()->area_nep.'<br>';
				$bill_land_price .=$this->numc->change_number_nep($item->price).'<br>';

				$total_land_price += $item->price;
			endforeach;


			foreach ($home->result() as $item):
				$bill_home_type .= $item->home_type.'<br>';
				$bill_floor .= $this->numc->change_number_nep($item->floor).'<br>';
				$bill_home_area .= $this->numc->change_number_nep($item->area).'<br>';
				$bill_home_kind .= $this->numc->change_number_nep($item->home_kind).'<br>';
				$total_home=$item->price;
				$constructed_date = substr($item->constructed_date,0,4);
				$year_passed = $today - $constructed_date;
				$h_reg_no .= $this->numc->change_number_nep($item->reg_no).'<br>';
				$table = $this->prefix.'depretiation';
				$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE and fy = '$fy' ");
				if($depn->num_rows()>0){
					$bill_home_price .= $this->numc->change_number_nep($total_home*(100-$depn->row()->rate)/100).'<br>';
					$total_home_price +=$total_home*(100-$depn->row()->rate)/100;
				}else{
					$bill_home_price .= $this->numc->change_number_nep($total_home*(100-$depn->row()->rate)/100).'<br>';
					$total_home_price +=$total_home*(100-$depn->row()->rate)/100;
				}
			endforeach;	

			$g_total=$total_land_price+$total_home_price;	
			$table = $this->prefix.'tax_rate';
			$tax_rate = $this->db->query("select * from $table where frm <= $g_total and too >= $g_total and status = TRUE and fy='$fy' ");
			$tax_amount = 0;
			if($tax_rate->num_rows()>0){
				$tax_rate= $tax_rate->row();
				if($tax_rate->type==1){
					$tax_amount = $tax_rate->rate;
				}else{
					$tax_amount = round($g_total/100000)*$tax_rate->rate;
				}
			}
			

			$payable_tax =$tax_amount;

			$month = $ndate['month'];
			$table = $this->prefix.'discount_month';
			$discount = $this->db->query("select * from $table where frm<=$month and too>=$month and status = TRUE ");

			if($discount->num_rows()>0){
				$dis = round($discount->row()->rate*$tax_amount/100);
			}else{
				$dis= "0";
			}

			

			
			$debt=0;
			$fine=0;
			$table = $this->prefix.'debt_account';
			$debt_info = $this->db->query("select * from $table where payer_id = '$id' and is_paid = FALSE order by id desc");
			if($debt_info->num_rows()>0){
				foreach ($debt_info->result() as $value) {
					$debt +=$value->debt;
					$fine +=$value->fine;
					$debt_id = $value->id;
					$this->db->query("update $table set is_paid = TRUE where id = $debt_id ");
				}
				
			}

			$bill_header = $system_data->bill_header;

			$g_tax = $this->numc->change_number_nep($payable_tax + $debt + $fine - $dis);
			$debt = $this->numc->change_number_nep($debt);
			$fine = $this->numc->change_number_nep($fine);
			$g_total = $this->numc->change_number_nep($g_total);
			$tax_amount = $this->numc->change_number_nep($tax_amount);
			$payable_tax = $this->numc->change_number_nep($payable_tax);
			$discount = $this->numc->change_number_nep($dis);
			$table = $this->prefix.'transaction';
			$this->db->query("insert into $table (payer_name,inserted_by,payer_id,vdc,p_ward,reg_no,land_area,land_type,land_price,home_type,floor,home_area,home_price,remark,debt,fine,inserted_date,g_total,discount,paid_amount,tax_amount,fy,tax_paid_date,mun,bill_header,g_tax,h_reg_no,home_kind) values ('$payer_name','$user_id','$payer_id','$bill_vdc','$bill_p_ward','$bill_reg_no','$bill_land_area','$bill_land_type','$bill_land_price','$bill_home_type','$bill_floor','$bill_home_area','$bill_home_price','$bill_rem','$debt','$fine','$date','$g_total','$discount','$payable_tax','$tax_amount','$fy','$tax_paid_date','$mun','$bill_header','$g_tax','$h_reg_no','$bill_home_kind')");

			return $this->generate_bill($id,$fy);		
		}

		function generate_new_bill_preview($id)
		{
			// System Data
			$system_data = $this->account_model->get_systemdata(TRUE); 

			// User Data
			$user_data = $this->account_model->get_userdata();
			$user_id = $user_data->id;

			//Payer Data
			$table = $this->prefix.'payer_list';
			$payer_data = $this->db->query("select * from $table where id='$id' and status = TRUE ");

			//If user exists
			if($payer_data->num_rows()>0){
				$payer_info= $payer_data->row();
				$payer_name = $payer_info->name;
				$payer_id = $payer_info->id;
				$payer_eng_id = $payer_info->payer_eng_id;
				$payer_mun = $payer_info->payer_local_gov;
				$payer_ward = $payer_info->ward;
			}else{
				echo "no data available";exit;
			}


            //Checking if transactio done
            if($this->account_model->check_status($payer_id)!=TRUE){echo "Access Forbidden !!";exit;}                      
            
			$fy = $system_data->fy;
			$started_fy = $system_data->started_fy;
			$payable_tax=0;

			//Date Convertor
			$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
			$today = $ndate['year'];
			$date =date('Y:m:d H:i:s');
    		$y = substr($date,0,4);
    		$m = substr($date,5,2);
    		$d = substr($date,8,2);
    		$ndate = $this->nc->eng_to_nep($y, $m, $d);
    
			
			$mun = $payer_info->payer_local_gov;
			$table = $this->prefix.'address_list';
			$mun_nep = $this->db->query("select local_gov_nep from $table where id = '$mun' and status = TRUE order by id desc")->row()->local_gov_nep;
			$table = $this->prefix.'land_list';
			$land = $this->db->query("select * from $table where payer_id = '$id' and status = TRUE order by id desc");
			$table = $this->prefix.'home_list';
			$home = $this->db->query("select * from $table where payer_id='$id' and status = TRUE order by id desc");

			$bill_vdc = '';$bill_p_ward='';$bill_reg_no='';$bill_land_area='';$bill_land_area_value='';$bill_land_type='';$bill_land_price='';$bill_home_type='';$bill_floor='';$bill_home_area='';$bill_home_price='';$bill_rem='';$total_land_price='';$total_home_price='';$bill_home_kind = '';$h_reg_no='';
			$tax=0;
			$fine=0;

			$tax_paid_date = $this->lang['date'].' : '.$this->numc->change_number_nep($ndate['year']).' '.$this->numc->getNepaliMonth($ndate['month']).' '.$this->numc->change_number_nep($ndate['date']).' '.$this->lang['dday'].' '.$this->numc->getNepaliDay($ndate['day']);
			if($fy != $started_fy){
				$table = $this->prefix.'transaction';
				$last_paid = $this->db->query("select * from $table where payer_id = '$id' and status = TRUE order by id desc");
				if($last_paid->num_rows()>0){
					$last_paid_details = $last_paid->row();
					$last_paid_date = $last_paid_details->inserted_date;
					$table = $this->prefix.'land_list';
					$unpaid_tax_land = $this->db->query("select * from $table where payer_id = '$id' and inserted_date > '$last_paid_date' and status = TRUE order by id desc");
					$table = $this->prefix.'home_list';
					$unpaid_tax_home = $this->db->query("select * from $table where payer_id='$id' and inserted_date > '$last_paid_date' and status = TRUE order by id desc");
					$last_paid_fy = $last_paid_details->fy;
					foreach ($unpaid_tax_land->result() as $item):
						$year_scape = substr($fy,0,4)-substr($last_paid_fy,0,4);
						$fy1 = $last_paid_fy;
						$price = $item->price;
						$reg_no = $item->reg_no;
						for($i=1;$i<=$year_scape;$i++){
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();
						
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
								$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;
					foreach ($unpaid_tax_home->result() as $item):
						$reg_no = $item->reg_no;
						$year_scape = substr($fy,0,4)-substr($last_paid_fy,0,4);
						$fy1 = $last_paid_fy;
						$price = $item->price;
						for($i=1;$i<=$year_scape;$i++){
							$constructed_date = substr($item->constructed_date,0,4);
							$year_passed = $today - $constructed_date;
							$table = $this->prefix.'depretiation';
							$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE ");

							if($depn->num_rows()>0){
								$price = $home_price*(100-$depn->row()->rate)/100;
							}else{
								$price = $home_price;
							}
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();
						
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;
				}else{
					foreach($land->result() as $item):
						$year_scape = substr($fy,0,4)-substr($started_fy,0,4);
						$fy1 = $started_fy;
						$price = $item->price;
						$reg_no = $item->reg_no;
						for($i=1;$i<=$year_scape;$i++){
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();
						
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;
					foreach ($home->result() as $item):
						$reg_no = $item->reg_no;
						$year_scape = substr($fy,0,4)-substr($started_fy,0,4);
						$constructed_date = substr($item->constructed_date,0,4);
						$year_passed = $today - $constructed_date;
						$home_price = $item->price;
						$fy1 = $started_fy;
						for($i=1;$i<=$year_scape;$i++){
							$table = $this->prefix.'depretiation';
							$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE ");
							if($depn->num_rows()>0){
								$price = $home_price*(100-$depn->row()->rate)/100;
							}else{
								$price = $home_price;
							}
							$table = $this->prefix.'fine';
							$fine_rate = $this->db->query("select rate from $table where year = $year_scape and fy='$fy1' and status = TRUE ");
							$table = $this->prefix.'tax_rate';
							$tax_rate = $this->db->query("select * from $table where frm <= $price and too >= $price and fy='$fy1' and status = TRUE ")->row();

							//die("select * from tax_rate where frm <= ".$price." and too >= ".$price." and fy='".$fy1."' and status = TRUE ");
							if($tax_rate->type==1){
								$c_tac_amount =$tax_rate->rate;
								$tax += $c_tac_amount;
							}else{
								$c_tac_amount = ceil($price/100000)*$tax_rate->rate;
								$tax += $c_tac_amount;
							}
							if($fine_rate->num_rows()>0){
								$f_rate = $fine_rate->row()->rate;
								$c_fine = $c_tac_amount*$f_rate/100;
								$fine += $c_fine;
							}

							$particular = $this->lang['fy'].' '.$this->numc->change_number_nep($fy1).' '.$this->lang['reg_no'].' '.$reg_no.' '.$this->lang['tax_particular'];
							$fy_first = substr($fy1,0,4);
							$fy_last = substr($fy1,5,2);

							$fy1 = ($fy_first+1).'/'.($fy_last+1);
						}
					endforeach;		
				}				
			}


			foreach ($land->result() as $item):
				$bill_vdc .= $item->vdc.'/'.$this->numc->change_number_nep($item->vdc_ward).'<br>';
				$bill_p_ward .= $this->numc->change_number_nep($item->ward).'<br>';
				$bill_reg_no .= $this->numc->change_number_nep($item->reg_no).'<br>';
				$bill_land_area .= $this->numc->change_number_nep($item->r).'-'.$this->numc->change_number_nep($item->a).'-'.$this->numc->change_number_nep($item->p).'-'.$this->numc->change_number_nep($item->d).'<br>';
				$area = $item->area;
				$table = $this->prefix.'area';
				$bill_land_type .= $this->db->query("select * from $table where area_eng = '$area' and status = TRUE ")->row()->area_nep.'<br>';
				$bill_land_price .=$this->numc->change_number_nep($item->price).'<br>';

				$total_land_price += $item->price;
			endforeach;


			foreach ($home->result() as $item):
				$bill_home_type .= $item->home_type.'<br>';
				$bill_floor .= $this->numc->change_number_nep($item->floor).'<br>';
				$bill_home_area .= $this->numc->change_number_nep($item->area).'<br>';
				$bill_home_kind .= $this->numc->change_number_nep($item->home_kind).'<br>';
				$total_home=$item->price;
				$constructed_date = substr($item->constructed_date,0,4);
				$year_passed = $today - $constructed_date;
				$h_reg_no .= $this->numc->change_number_nep($item->reg_no).'</br>';
				$table = $this->prefix.'depretiation';
				$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE and fy = '$fy' ");
				if($depn->num_rows()>0){
					$bill_home_price .= $this->numc->change_number_nep($total_home*(100-$depn->row()->rate)/100).'<br>';
					$total_home_price +=$total_home*(100-$depn->row()->rate)/100;
				}else{
					$bill_home_price .= $this->numc->change_number_nep($total_home*(100-$depn->row()->rate)/100).'<br>';
					$total_home_price +=$total_home*(100-$depn->row()->rate)/100;
				}
			endforeach;	

			$g_total=$total_land_price+$total_home_price;	
			$table = $this->prefix.'tax_rate';
			$tax_rate = $this->db->query("select * from $table where frm <= $g_total and too >= $g_total and status = TRUE and fy='$fy' ");
			$tax_amount = 0;
			if($tax_rate->num_rows()>0){
				$tax_rate= $tax_rate->row();
				if($tax_rate->type==1){
					$tax_amount = $tax_rate->rate;
				}else{
					$tax_amount = round($g_total/100000)*$tax_rate->rate;
				}
			}
			

			$payable_tax =$tax_amount;
			$month = $ndate['month'];
			$table = $this->prefix.'discount_month';
			$discount = $this->db->query("select * from $table where frm<=$month and too>=$month and status = TRUE ");

			if($discount->num_rows()>0){
				$dis = round($discount->row()->rate*$tax_amount/100);
			}else{
				$dis= "0";
			}
			
			$debt=0;
			$fine=0;
			$table = $this->prefix.'debt_account';
			$debt_info = $this->db->query("select * from $table where payer_id = '$id' and is_paid = FALSE order by id desc");
			if($debt_info->num_rows()>0){
				foreach ($debt_info->result() as $value) {
					$debt +=$value->debt;
					$fine +=$value->fine;
					$debt_id = $value->id;
				}
				
			}

			$bill_header = $system_data->bill_header;
			$g_tax = $this->numc->change_number_nep($payable_tax + $debt + $fine - $dis);
			$debt = $this->numc->change_number_nep($debt);
			$fine = $this->numc->change_number_nep($fine);
			$g_total = $this->numc->change_number_nep($g_total);
			$tax_amount = $this->numc->change_number_nep($tax_amount);
			$payable_tax = $this->numc->change_number_nep($payable_tax);
			$discount = $this->numc->change_number_nep($dis);

			$table = $this->prefix."address_list";
			$payer_mun = $this->db->query("select local_gov_nep from $table where id='$payer_mun' and status = TRUE")->row()->local_gov_nep;
			
			$output = 
					'<p class="text-center">'.$bill_header.'</p>
					<div class="col-md-9 col-sm-8 col-xs-7 no-margin no-padding">
					'.$this->lang['bill_no'].' : '.$this->lang['preview'].'<br>
					'.$this->lang['payer_id'].' : '.$this->numc->change_number_nep($payer_eng_id).'<br>
					'.$this->lang['payer_name'].' : '.$payer_name.'

					</div>
					<div class="col-md-3 col-sm-4 col-xs-5 no-margin no-padding" style="padding-left:80px;">
						'.$tax_paid_date.'<br>
						'.$this->lang['tax_paid_year'].' : '.$this->numc->change_number_nep($fy).'<br>
						'.$this->lang['location'].' : '.$payer_mun.' - '.$this->numc->change_number_nep($payer_ward).'
						
					</div>
					<br style="clear:both;">
					<br>
					<table class= "table table-responsive" id="print-table"> 
						<tr>
							<td colspan="5">'.$this->lang['land_detail'].'</td>
							<td rowspan="2">'.$this->lang['land_price'].'</td>
							<td colspan="4">'.$this->lang['home_detail'].'</td>
							<td rowspan="2">'.$this->lang['home_price'].'</td>
							<td rowspan="2">'.$this->lang['remark'].'</td>
						</tr>
						<tr>
							<td>'.$this->lang['vdcandward'].'</td>
							<td>'.$this->lang['p_ward'].'</td>
							<td>'.$this->lang['reg_no'].'</td>
							<td>'.$this->lang['area'].'</td>
							<td>'.$this->lang['type'].'</td>
							<td>'.$this->lang['reg_no'].'</td>
							<td>'.$this->lang['b_kind'].'</td>
							<td>'.$this->lang['home_bill_kind'].'</td>
							<td>'.$this->lang['home_bill_area'].'</td>
						</tr>
						<tr>
						<td><div id="data-row">
						';
					$output.= $bill_vdc;

					$output.= '</div></td><td>';

					$output.= $bill_p_ward.'<br>';

					$output.= '</td><td>';

					$output.= $bill_reg_no.'<br>';

					$output.= '</td><td>';

					$output.= $bill_land_area;

					$output.= '</td><td>';

					$output.= $bill_land_type;

					$output.= '</td><td>';
					$output.= $bill_land_price;

					$output.= '</td><td>';

					$output.= $h_reg_no.'<br>';

					$output.= '</td><td>';

					$output.= $bill_home_type.'<br>';

					$output.= '</td><td>';
					$output.= $bill_home_kind.'<br>';

					$output.= '</td><td>';

					$output.= $bill_home_area.'<br>';

					$output.= '</td><td>';
					$output.= $bill_home_price;


					
				
				$output.= '</td><td ></td></tr>';

				$table = $this->prefix.'home_kind';
				$kind = $this->db->query("select * from $table where status = TRUE ")->result();
				$i=1;
				$h_kind='';
				foreach ($kind as $value) {
					$index = $this->numc->change_number_nep($i);
					$h_kind .= $index.'.  '.$value->kind_nep.'<br>';
					$i++;
				}


				$output.='<tr class="break"><tr><tr><td style="padding-bottom:60px;padding-top:60px;" rowspan="7" colspan="8">'.$this->lang['kind_info'].'<br>'.$h_kind.'</td></tr><tr><td>'.$this->lang['agg_tax'].'</td><td>';

				$output.=$g_total;

				$output.= '</td><td>'.$this->lang['agg_tax_pay'].'</td><td>';

				
				$output.=$tax_amount;

				

				
				$output.= '</td></tr><td colspan="2" rowspan="5" style="padding-top:100px;">'.$this->lang['letter'].' : '.$this->numc->get_letter($this->numc->change_number_eng($g_tax)).'</td><td>'.$this->lang['fine'].'</td><td>';

				
				$output.=$fine;

				

				$output.= '</td></tr><tr><td>'.$this->lang['add_tax'].'</td><td>०</td></tr><tr><td>'.$this->lang['debt'].'</td><td>'.$debt.'</td></tr><td>'.$this->lang['discount'].'</td><td>';



				$output.=$discount;
				
				$output.= '</td></tr><tr><td>'.$this->lang['g_total'].'</td><td>'.$g_tax.'</td></tr>';


				
				
				
				

				$output.='</table><p class="text-right" style="margin-right:200px;">'.$this->lang['authority'].'</p><p class="text-right" style="margin-right:190px;">'.$this->lang['sign'].'</p><p class="text-center">'.$this->lang['tax_info'].'</p>';



				return $output;
		}

		function generate_bill($id,$fy)
		{
			$table = $this->prefix.'transaction';
			$bill = $this->db->query("select * from $table where payer_id='$id' and fy='$fy' and status = TRUE order by id desc");
			if($bill->num_rows()>0){
				$b = $bill->row();
				$bill_id = $b->id;
				$print_counter = $b->print_counter;
				if($print_counter==0){
					$bill_no = $bill_id;
				}else{
					$bill_no = $bill_id.' '.$this->lang['copy'].' ('.$print_counter.')';
				}
				$print_counter++;
				$this->db->query("update $table set print_counter = '$print_counter' where id= $bill_id ");
				
				$payer_id = $b->payer_id;
				$payer_name = $b->payer_name;
				$table = $this->prefix.'payer_list';
				$payer_info = $this->db->query("select * from $table where id='$id' and status = TRUE ")->row();
				$payer_nepali_id = $payer_info->payer_nep_id;
				$payer_mun = $b->mun;
				$table = $this->prefix.'address_list';
				$payer_mun = $this->db->query("select local_gov_nep from $table where id='$payer_mun' and status = TRUE")->row()->local_gov_nep;
				$payer_ward = $payer_info->ward;
				$date = $b->tax_paid_date;
				$fy = $b->fy;

				$vdc = $b->vdc;
				$p_ward = $b->p_ward;
				$reg_no = $b->reg_no;
				$land_area = $b->land_area;
				$land_type = $b->land_type;
				$land_price = $b->land_price; 
				$home_type = $b->home_type;
				$h_reg_no = $b->h_reg_no;
				$home_area = $b->home_area;
				$home_price = $b->home_price;
				$home_kind = $b->home_kind;

				$g_total = $b->g_total;
				$payable_tax = $b->paid_amount;
				$tax_amount = $b->tax_amount;
				$discount = $b->discount;
				$print_counter = $b->print_counter;

				$debt  = $b->debt;
				$fine = $b->fine;

				$bill_header = $b->bill_header;
				$other_charge = $b->other_charge;

				$g_tax = $b->g_tax;
				$tax_paid_date = $b->tax_paid_date;

				$table = $this->prefix.'home_kind';
				$kind = $this->db->query("select * from $table where status = TRUE ")->result();
				$i=1;
				$h_kind='';
				foreach ($kind as $value) {
					$index = $this->numc->change_number_nep($i);
					$h_kind .= $index.'.  '.$value->kind_nep.'<br>';
					$i++;
				}



				/*$prev_bill = $this->db->query("select * from transaction where payer_id = '$id' and id <$bill_id and status = TRUE order by id desc limit 1");
				$total = $b->paid_amount;
				$ins_date = $b->inserted_date;
				$payable_tax=0;
				$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
				$today = $ndate['year'];

				$mun = $payer_info->payer_local_gov;
				$mun_nep = $this->db->query("select local_gov_nep from address_list where local_gov_eng = '$mun' and status = TRUE ")->row()->local_gov_nep;
				$land = $this->db->query("select * from land_list where payer_id = '$id' and inserted_date < '$ins_date' and status = TRUE ");
				$home = $this->db->query("select * from home_details where payer_id='$id' and inserted_date < '$ins_date' and status = TRUE ");
				$system_data = $this->account_model->get_systemdata(); 
				$user_data = $this->account_model->get_userdata();*/

				$output = 
					'<p class="text-center">'.$bill_header.'</p>
					<div class="col-md-9 col-sm-10 col-xs-8 no-margin no-padding">
					'.$this->lang['bill_no'].' : '.$this->numc->change_number_nep($bill_no).'<br>
					'.$this->lang['payer_id'].' : '.$this->numc->change_number_nep($payer_nepali_id).'<br>
					'.$this->lang['payer_name'].' : '.$payer_name.'

					</div>
					<div class="col-md-3 col-sm-2 col-xs-4 no-margin no-padding" style="padding-left:80px;">
						'.$tax_paid_date.'<br>
						'.$this->lang['tax_paid_year'].' : '.$this->numc->change_number_nep($b->fy).'<br>
						'.$this->lang['location'].' : '.$payer_mun.' - '.$this->numc->change_number_nep($payer_ward).'
						
					</div>
					<br style="clear:both;">
					<br>
					<table class= "table table-responsive" id="print-table" BORDERCOLOR="RED"> 
						<tr>
							<td colspan="5">'.$this->lang['land_detail'].'</td>
							<td rowspan="2">'.$this->lang['land_price'].'</td>
							<td colspan="4">'.$this->lang['home_detail'].'</td>
							<td rowspan="2">'.$this->lang['home_price'].'</td>
							<td rowspan="2">'.$this->lang['remark'].'</td>
						</tr>
						<tr>
							<td>'.$this->lang['vdcandward'].'</td>
							<td>'.$this->lang['p_ward'].'</td>
							<td>'.$this->lang['reg_no'].'</td>
							<td>'.$this->lang['area'].'</td>
							<td>'.$this->lang['type'].'</td>
							<td>'.$this->lang['reg_no'].'</td>
							<td>'.$this->lang['b_kind'].'</td>
							<td>'.$this->lang['home_bill_kind'].'</td>

							<td>'.$this->lang['home_bill_area'].'</td>
						</tr>
						<tr>
						<td><div id="data-row">
						';
					$output.= $vdc;

					$output.= '</div></td><td>';

					$output.= $p_ward.'<br>';

					$output.= '</td><td>';

					$output.= $reg_no.'<br>';

					$output.= '</td><td>';

					$output.= $land_area;

					$output.= '</td><td>';

					$output.= $land_type;

					$output.= '</td><td>';
					$output.= $land_price;

					$output.= '</td><td>';

					$output.= $h_reg_no.'<br>';

					$output.= '</td><td>';

					$output.= $home_type.'<br>';

					$output.= '</td><td>';
					$output.= $home_kind.'<br>';

					$output.= '</td><td>';

					$output.= $home_area.'<br>';

					$output.= '</td><td>';
					$output.= $home_price;


					
				
				$output.= '</td><td ></td></tr>';

				$output.='<tr><td style="padding-bottom:60px;padding-top:60px;" rowspan="6" colspan="5">'.$this->lang['kind_info'].'<br>'.$h_kind.'</td><td colspan="2">'.$this->lang['agg_tax'].'</td><td>';

				$output.=$g_total;

				$output.= '</td><td colspan="2">'.$this->lang['agg_tax_pay'].'</td><td colspan="2">';

				
				$output.=$tax_amount;

				

				
				$output.= '</td></tr><td colspan="3" rowspan="5" style="padding-top:100px;">'.$this->lang['letter'].' : '.$this->numc->get_letter($this->numc->change_number_eng($g_tax)).'</td><td colspan="2">'.$this->lang['fine'].'</td><td colspan="2">';

				
				$output.=$fine;

				

				$output.= '</td></tr><tr><td colspan="2">'.$this->lang['add_tax'].'</td><td colspan="2">'.$this->numc->change_number_nep($other_charge).'</td></tr><tr><td colspan="2">'.$this->lang['debt'].'</td><td colspan="2">'.$debt.'</td></tr><td colspan="2">'.$this->lang['discount'].'</td><td colspan="2">';



				$output.=$discount;
				
				$output.= '</td></tr><tr><td colspan="2">'.$this->lang['g_total'].'</td><td colspan="2">'.$g_tax.'</td></tr>';


				
				
				
				

				$output.='</table><p class="text-right" style="margin-right:200px;">'.$this->lang['authority'].'</p><p class="text-right" style="margin-right:190px;">'.$this->lang['sign'].'</p><p class="text-center" style="margin-top:100px;">'.$this->lang['tax_info'].'</p>';



				return $output;
			}
		}
	}
	?>
