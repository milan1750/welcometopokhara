<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Excel_import extends CI_Controller
{
    public $prefix,$lang;

 public function __construct()
 {
  parent::__construct();
  $this->load->model('excel_import_model');
  $this->load->library('excel');
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
  $this->load->model('Lang','language');
  $this->_salt = "";

  $this->lang = $this->language->nepali();
    if($this->login_model->logged_in() == FALSE)redirect(base_url());

    $this->prefix = $this->session->userdata('table_prefix');

 }

 
 
 function fetch()
 {
  $data = $this->excel_import_model->select();
  $output = '
  <h3 align="center">Total Data - '.$data->num_rows().'</h3>
  <table class="table table-striped table-bordered">
   <tr>
    <th>SN</th>
    <th>District</th>
    <th>Local Gov</th>
    <th>Province No</th>
   </tr>
  ';
  $i=0;
  foreach($data->result() as $row)
  {
    $i++;
   $output .= '
   <tr>
    <td>'.$i.'</td>
    <td>'.$row->district_nep.'</td>
    <td>'.$row->local_gov_nep.'</td>
    <td>'.$row->province_number.'</td>
   </tr>
   ';
  }
  $output .= '</table>';
  echo $output;
 }

 function import()
 {
  if(isset($_FILES["file"]["name"]))
  {
   $path = $_FILES["file"]["tmp_name"];
   $object = PHPExcel_IOFactory::load($path);
   foreach($object->getWorksheetIterator() as $worksheet)
   {
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    for($row=2; $row<=$highestRow; $row++)
    {
     $province = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
     $district = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
     $local_gov = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
     $district_eng = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
     
     $data[] = array(
      'province_number'  => $province,
      'district_nep'   => $district,
      'local_gov_nep'    => $local_gov,
      'district_eng' =>$district_eng
     );
    }
   }
   $this->excel_import_model->insert($data);
   echo 'Data Imported successfully';
  } 
 }


 function export_to_excel(){
  // Execute the database query

  // Instantiate a new PHPExcel object
  $objPHPExcel = new PHPExcel(); 
  // Set the active Excel worksheet to sheet 0
  $objPHPExcel->setActiveSheetIndex(0); 
  // Initialise the Excel row number
  $rowCount = 1; 
  // Iterate through each result from the SQL query in turn
  // We fetch each database result row into $row in turn
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

    $payer_list = $this->db->query("select * from jaljala_payer_list where status = TRUE ")->result();
    $ind = 0;
    $tax = 0;
    $tax_i = 0;
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

      // Set cell An to the "name" column from the database (assuming you have a column called name)
        //    where n is the Excel row number (ie cell A1 in the first row)
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $payer_data->payer_nep_id); 
        // Set cell Bn to the "age" column from the database (assuming you have a column called age)
        //    where n is the Excel row number (ie cell A1 in the first row)
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $payer_data->name); 
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,$this->numc->change_number_nep($total_land_price)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,$this->numc->change_number_nep($total_home_price)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $this->numc->change_number_nep($g_total)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $this->numc->change_number_nep($tax_i)); 
        // Increment the Excel row counter
        $rowCount++; 



    endforeach; 

/// Redirect output to a client’s web browser (Excel5) 
header('Content-Type: application/vnd.ms-excel'); 
header('Content-Disposition: attachment;filename="Limesurvey_Results.xls"'); 
header('Cache-Control: max-age=0'); 
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
$objWriter->save('php://output');
 }






     function payer_data(){
  // Execute the database query

  // Instantiate a new PHPExcel object
  $objPHPExcel = new PHPExcel(); 
  // Set the active Excel worksheet to sheet 0
  $objPHPExcel->setActiveSheetIndex(0); 
  // Initialise the Excel row number
  $rowCount = 1; 
  // Iterate through each result from the SQL query in turn
  // We fetch each database result row into $row in turn
      /*System Data*/

      $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $this->lang['p_id']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$this->lang['p_district']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('c'.$rowCount,$this->lang['p_mun']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $this->lang['p_ward']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $this->lang['p_name']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $this->lang['p_f_name']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $this->lang['p_g_name']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $this->lang['p_c_number']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $this->lang['p_l_price']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $this->lang['p_h_price']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $this->lang['p_t_price']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $this->lang['p_t_tax']); 
      $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $this->lang['p_remark']); 
      // Increment the Excel row counter
      $rowCount++; 

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

    $payer_list = $this->db->query("select * from jaljala_payer_list where status = TRUE ")->result();
    $ind = 0;
    $tax = 0;
    $tax_i = 0;
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

      // Set cell An to the "name" column from the database (assuming you have a column called name)
        //    where n is the Excel row number (ie cell A1 in the first row)
        $table = $this->prefix.'address_list';
        $district = $payer_data->payer_district;
        $mun = $payer_data->payer_local_gov;
        $district_nep = $this->db->query("select district_nep from $table where district_eng = '$district' ")->row()->district_nep;
        $mun_nep = $this->db->query("select local_gov_nep from $table where id = '$mun' ")->row()->local_gov_nep;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount,$payer_data->payer_nep_id); 
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$district_nep); 
        $objPHPExcel->getActiveSheet()->SetCellValue('c'.$rowCount,$mun_nep); 
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,$this->numc->change_number_nep($payer_data->ward)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,$payer_data->name); 
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,$payer_data->payer_father); 
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount,$payer_data->payer_gf); 
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount,$payer_data->contact); 
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount,$this->numc->change_number_nep($total_land_price)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount,$this->numc->change_number_nep($total_home_price)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount,$this->numc->change_number_nep($g_total)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $this->numc->change_number_nep($tax_i)); 
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount,'-'); 
        // Increment the Excel row counter
        $rowCount++; 
        



    endforeach; 

/// Redirect output to a client’s web browser (Excel5) 
header('Content-Type: application/vnd.ms-excel'); 
header('Content-Disposition: attachment;filename="Limesurvey_Results.xls"'); 
header('Cache-Control: max-age=0'); 
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
$objWriter->save('php://output');
 }
}

?>