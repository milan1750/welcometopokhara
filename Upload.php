<?php
/**
 * 
 */
class Upload extends CI_Model
{
	
	function add_mun(){
		$file=$_FILES['file']['tmp_name'];
		$file = fopen($file,'r');
		$value='';
		$row_number=1;
		while($row = fgets($file)){
			if($row_number==0){
				//do nothing
			}else if($row_number==1){
				$value.= "('".implode("','",$row)."')";
			}else{
				$value.= ",('".implode("','",$row)."')";
			}

			$row_number++;
			
		}
		$this->db->query("insert into address_list (province_number,district_nep,local_level_type,local_gov_nep) values ".$value); 
		if($this->db->affected_rows()>0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
}