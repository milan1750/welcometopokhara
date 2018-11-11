
<?php
defined('BASEPATH') OR exit('No direct script access allowed');


date_default_timezone_set('Asia/Kathmandu');

class Account extends CI_Controller {

	

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
		

	}

	

	function dashboard()
	{
		$this->load->view('users/dashboard');
	}


	function add_payer()
	{
		$this->load->view('users/add_payer');
	}

	function payer_detail()
	{
		$this->load->view('users/payer_detail');
	}

	function tax_payer_detail()
	{
		$this->load->view('users/description');
	}

	function get_local_gov()
	{
		echo json_encode($this->curd->get_local_gov());
	}

	function get_local_gov_ward()
	{
		echo json_encode($this->curd->get_local_gov_ward());
	}

	function add_new_payer()
	{
		echo json_encode($this->curd->add_payer());
	}

	function edit_payer_info(){
		$id = $this->uri->segment(3); 
		$payer_data = $this->account_model->get_payer_data($id);
		
		$this->load->view('users/update_payer_info',$payer_data);
	}

	function land_data(){
		$this->load->view('users/pages/landdata');
	}

	function update_payer_info(){
		echo json_encode($this->curd->update_payer_info());
	}

	function get_data()
	{
		echo  $this->curd->get_data();
	}

	function delete_data()
	{
		echo $this->curd->delete_data();
	}




	function payer(){
		$this->load->view('users/payer.php');
	}

	function get_road_type()
	{
		echo json_encode($this->curd->get_road_type());
	}

	function get_tax_rate(){
		echo json_encode($this->curd->get_tax_rate());
	}

	function save_landdata()
	{
		echo $this->curd->save_landdata();

	}

	function save_homedata(){
		echo $this->curd->save_homedata();
	}

	function get_bill(){
		echo $this->bill->get_bill();
	}

	function generate_new_bill(){
		$this->load->view('users/generate_new_bill');
	}

	function generate_new_bill_preview(){
		$this->load->view('users/generate_new_bill_preview');
	}

	function generate_copy_bill(){
		$this->load->view('users/generate_copy_bill');
	}

	function home_data(){
		$this->load->view('users/pages/home_data.php');
	}

	
	function save_debt(){
		echo $this->account_model->save_debt();
	}

	function add_mun(){
		$this->load->model('Upload','upload');
		$this->upload->add_mun();
	}

	function edit_land(){
		$id = $this->uri->segment(3);
		$data = $this->account_model->get_landdata($id);
		$this->load->view('users/edit_land',$data);
	}

	function edit_home(){
		$id = $this->uri->segment(3);
		$data = $this->account_model->get_homedata($id);
		$this->load->view('users/edit_home',$data);
	}




	function update_land_data()
	{
		return $this->curd->update_land_data();
	}

	function delete_land()
	{
		return $this->curd->delete_land_data();
	}

	function delete_home()
	{
		return $this->curd->delete_home_data();
	}

	function remove_transaction()
	{
		return $this->curd->remove_transaction();
	
	}

	function filter_data(){
		$this->account_model->filter_data();
	}

	function logout()
	{
		session_destroy();
	}

	function tax_data()
	{
		$this->load->view("users/pages/taxdata");
	}

}