
<?php
defined('BASEPATH') OR exit('No direct script access allowed');


date_default_timezone_set('Asia/Kathmandu');

class Tax extends CI_Controller {

	public $prefix,$lang;

	function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation', 'session'));
		$this->load->model('Account_Model','account_model');
		$this->load->model('Nepali_Calendar','nc');
		$this->load->model('Number_Convertor','numc');
		$this->load->model('Curd','curd');
		$this->load->model('Address','address');
		$this->load->model('Developer','developer');
		$this->load->model('Bill','bill');
		$this->load->model('System','system');
		$this->load->model('Login_Model','login_model');
		
		$this->_salt = "";

		if($this->login_model->logged_in() == FALSE)redirect(base_url());

		$this->prefix = $this->session->userdata('table_prefix');

	}

	function get_total_tax_amount(){
		/*System Data*/
		$system_data = $this->account_model->get_systemdata(TRUE); 
		/*User Data*/
		$user_data = $this->account_model->get_userdata();
		$user_id = $user_data->id;
		/*Payer Data*/
		$table = $this->prefix.'payer_list';


		$fy = $system_data->fy;

		$started_fy = $system_data->started_fy;
                       

		$ndate = $this->nc->eng_to_nep(date('Y'), date('m'), date('d'));
		$today = $ndate['year'];

		$date =date('Y:m:d H:i:s');
		$y = substr($date,0,4);
		$m = substr($date,5,2);
		$d = substr($date,8,2);
		$ndate = $this->nc->eng_to_nep($y, $m, $d);

		$table = $this->prefix.'payer_list';

		$payer_list = $this->db->query("select * from jaljala_payer_list where status = TRUE  ")->result();
		echo "Total Tax Amount Till Now: <br>";
		$ind = 0;
		$tax = 0;
		$tax_i = 0;
		echo '<table border = 1>';
		foreach ($payer_list as $payer_data):
			$ind++;
			$payer_id = $payer_data->id;
			$total_land_price = 0;
			$total_home_price = 0;
			$land = $this->db->query("select * from jaljala_land_list where payer_id = $payer_id ");
			$home = $this->db->query("select * from jaljala_home_list where payer_id = $payer_id ");
			foreach ($land->result() as $item):
				$total_land_price += $item->price;
			endforeach;


			foreach ($home->result() as $item):
				$total_home=$item->price;
				$constructed_date = substr($item->constructed_date,0,4);
				$year_passed = $today - $constructed_date;
				$table = $this->prefix.'depretiation';
				$depn = $this->db->query("select rate from $table where frm <= $year_passed and too >= $year_passed and status = TRUE and fy = '$fy' ");
				if($depn->num_rows()>0){
					$total_home_price +=$total_home*(100-$depn->row()->rate)/100;
				}else{
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

			$tax_i = $payable_tax-$dis;
			$tax += $tax_i;

			echo '<tr><td>'.$payer_data->payer_nep_id.'</td><td>'.$payer_data->name.'</td><td> '.$tax_i.'</td><td>'.$total_land_price.'</td><td>'.$total_home_price.'</td><td>'.$g_total.'</td></tr>';

			


		endforeach;
		echo '</table>';
		echo $tax.' from .'.$ind.' payers';


	}
}
