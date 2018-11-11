<?php
/**
 * 
 */

/* Set internal character encoding to UTF-8 */
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding("UTF-8");
class Number_Convertor extends CI_Model
{
	
	function change_number_nep($num){
		$new = '';
		if(preg_match('/[^\x20-\x7f]/', $num)){
			$item = (string)$num;
			for($i=0;$i<strlen($item);$i++){
				$new .= $this->convertNosNep(mb_substr($item,$i,1));
			}
		}else{
			for($i=0;$i<mb_strlen($num);$i++){
				$new .= $this->convertNosNep(substr($num,$i,1));
			}	
		}
		
		return $new;
	}

	function change_number_eng($num){
		$new = '';
		if(preg_match('/[^\x20-\x7f]/', $num)){
			for($i=0;$i<mb_strlen($num);$i++){
				$new .= $this->convertNosEng(mb_substr($num,$i,1));
			}
		}else{
			$item = (string)$num;
			for($i=0;$i<strlen($item);$i++){
				$new .= $this->convertNosEng(mb_substr($item,$i,1));
			}
		}
		
		return $new;
	}

	// An array of Nepali number representations
	function convertNosEng($nos)
	{
	  $n = '';
	  switch($nos){
	    case "०": $n = 0; break;
	    case "१": $n = 1; break;
	    case "२": $n= 2; break;
	    case "३": $n = 3; break;
	    case "४": $n = 4; break;
	    case "५": $n = 5; break;
	    case "६": $n = 6; break;
	    case "७": $n = 7; break;
	    case "८": $n = 8; break;
	    case "९": $n = 9; break;
	    default: $n=$nos;
	   }
	   return $n;
	}

	// An array of Nepali number representations
	function convertNosNep($nos)
	{
	  $n = '';
	  switch($nos){
	    case "0": $n = "०"; break;
	    case "1": $n = "१"; break;
	    case "2": $n = "२"; break;
	    case "3": $n = "३"; break;
	    case "4": $n = "४"; break;
	    case "5": $n = "५"; break;
	    case "6": $n = "६"; break;
	    case "7": $n = "७"; break;
	    case "8": $n = "८"; break;
	    case "9": $n = "९"; break;
	    default: $n=$nos;
	   }
	   return $n;
	}

	function getNumber($char)
	{
		$n='';
		switch($char){
			case 'अ': $n="०१"; break;
			case 'आ': $n="०२"; break;
			case 'इ': $n="०३"; break;
			case 'ई': $n="०३"; break;
			case 'उ': $n="०४"; break;
			case 'ऋ': $n="०५"; break;
			case 'रि': $n="०५"; break;
			case 'ए': $n="०६"; break;
			case 'य': $n="०६"; break;
			case 'एे': $n="०७"; break;
			case 'आे': $n="०८"; break;
			case 'ओ': $n="०८"; break;
			case 'व': $n="०८"; break;
			case 'अौ': $n="०९"; break;
			case 'अं': $n="१०"; break;
			case 'क': $n="११"; break;
			case 'ख': $n="१२"; break;
			case 'ग': $n="१३"; break;
			case 'घ': $n="१४"; break;
			case 'ङ': $n="१५"; break;
			case 'च': $n="१६"; break;
			case 'छ': $n="१७"; break;
			case 'ज': $n="१८"; break;
			case 'झ': $n="१९"; break;
			case 'ञ': $n="२०"; break;
			case 'ट': $n="२१"; break;
			case 'ठ': $n="२२"; break;
			case 'ड': $n="२३"; break;
			case 'ढ': $n="२४"; break;
			case 'ण': $n="२५"; break;
			case 'त': $n="२६"; break;
			case 'थ': $n="२७"; break;
			case 'द': $n="२८"; break;
			case 'ध': $n="२९"; break;
			case 'न': $n="३०"; break;
			case 'प': $n="३१"; break;
			case 'फ': $n="३२"; break;
			case 'ब': $n="३३"; break;
			case 'भ': $n="३४"; break;
			case 'म': $n="३५"; break;
			case 'र': $n="३६"; break;
			case 'ल': $n="३७"; break;
			case 'श': $n="३८"; break;
			case 'स': $n="३८"; break;
			case 'ष': $n="३८"; break;
			case 'श्री': $n="३८"; break;
			case 'ह': $n="३९"; break;
			case 'क्ष': $n="४०"; break;
			case 'त्र': $n="४१"; break;
			case 'ज्ञ': $n="४२"; break;
			default : $n="००";

		}

		return $n;
	}

	function getNepaliMonth($month)
	{
		$nepMonth='';
		switch ($month) {
			case 'Baishak':$nepMonth='बैशाख'; break;
			case '1':$nepMonth='बैशाख' ;break;
			case 'Jestha':$nepMonth='जेष्ठ' ;break;
			case '2':$nepMonth='जेष्ठ' ;break;
			case 'Ashad':$nepMonth='अषाढ' ;break;
			case '3':$nepMonth='असार' ;break;
			case 'Shrawn':$nepMonth='श्रावण' ;break;
			case '4':$nepMonth='श्रावण' ;break;
			case 'Bhadra':$nepMonth='भाद्र' ;break;
			case '5':$nepMonth='भाद्र'; break;
			case 'Ashwin':$nepMonth='अाश्विन' ;break;
			case '6':$nepMonth='अाश्विन' ;break;
			case 'kartik':$nepMonth='कार्तिक' ;break;
			case '7':$nepMonth='कार्तिक' ;break;
			case 'Mangshir':$nepMonth='मंसीर'; break;
			case '8':$nepMonth='मंसीर'; break;
			case 'Poush':$nepMonth='पाैष' ;break;
			case '9':$nepMonth='पाैष' ;break;
			case 'Magh':$nepMonth='माघ' ;break;
			case '10':$nepMonth='माघ'; break;
			case 'Falgun':$nepMonth='फाल्गुन' ;break;
			case '11':$nepMonth='फाल्गुन' ;break;
			case 'Chaitra':$nepMonth='चैत्र' ;break;
			case '12':$nepMonth='चैत्र'; break;
		}
		return $nepMonth;
	}
	function getNepaliDay($day)
	{
		$nepDay='';
		switch ($day) {
			case 'Sunday':$nepDay='आर्इतबार'; break;
			case '1':$nepDay='आर्इतबार' ;break;
			case 'Monday':$nepDay='साेमबार'; break;
			case '2':$nepDay='साेमबार' ;break;
			case 'Tuesday':$nepDay='मंगलबार'; break;
			case '3':$nepDay='मंगलबार' ;break;
			case 'Wednesday':$nepDay='बुधबार'; break;
			case '4':$nepDay='बुधबार' ;break;
			case 'Thursday':$nepDay='बिहीबार' ;break;
			case '5':$nepDay='विहीबार' ;break;
			case 'Friday':$nepDay='शुक्रबार'; break;
			case '6':$nepDay='शुक्रबार'; break;
			case 'Saturday':$nepDay='शनिबार'; break;
			case '7':$nepDay='शनिबार'; break;
			
		}
		return $nepDay;
	}

	function get_letter($number){
		$letter='';
		if($number<10){
			$letter = $this->changeToLetter($number);
		}else if($number>=10 and $number<100){
			$letter = $this->changeToLetter($number);
		}else if($number>=100 and $number <1000){
			$hundred = floor($number/100);
			$tens = ($number-$hundred*100);
			$letter = $this->changeToLetter($hundred).' सय '.$this->changeToLetter($tens);
		}else if($number >=1000 and $number <100000){
			$thousand = floor($number/1000);
			$hundred = floor(($number-($thousand*1000))/100);
			$tens = $number - $thousand*1000 - $hundred*100;

			if($hundred == 0){
				$letter = $this->changeToLetter($thousand).' हजार '.$this->changeToLetter($tens).' मात्र';
			}else{
				$letter = $this->changeToLetter($thousand).' हजार '.$this->changeToLetter($hundred).' सय '.$this->changeToLetter($tens).' मात्र';

			}

		}

		return $letter;
	}


	function changeToLetter($number){
		$no='';
		switch ($number) {
			case 1:
				$no='एक';
				break;
			case 2:
				$no='दुर्इ';
				break;
			case 3:
				$no='तिन';
				break;
			case 4:
				$no='चार';
				break;
			case 5:
				$no='पाँच';
				break;
			case 6:
				$no='छ';
				break;
			case 7:
				$no='सात';
				break;
			case 8:
				$no='आठ';
				break;
			case 9:
				$no='नौ';
				break;
			case 10:
				$no='दश';
				break;
			case 11:
				$no='एघार';
				break;
			case 12:
				$no='बाह्र';
				break;
			case 13:
				$no='तेह्र';
				break;
			case 14:
				$no='चाौध';
				break;
			case 15:
				$no='पन्ध्र';
				break;
			case 16:
				$no='साेह्र';
				break;
			case 17:
				$no='सत्र';
				break;
			case 18:
				$no='अठार';
				break;
			case 19:
				$no='उन्नर्इस';
				break;
			case 20:
				$no='बीस';
				break;
			case 21:
				$no='एक्कार्इस';
				break;
			case 22:
				$no='बार्इस';
				break;
			case 23:
				$no='तेर्इस';
				break;
			case 24:
				$no='चाौबीस';
				break;
			case 25:
				$no='पच्चीस';
				break;
			case 26:
				$no='छब्बीस';
				break;
			case 27:
				$no='सत्तार्इस';
				break;
			case 28:
				$no='अठ्ठार्इस';
				break;
			case 29:
				$no='उनान्तिस';
				break;
			case 30:
				$no='तीस';
				break;
			case 31:
				$no='एकतिस';
				break;
			case 32:
				$no='बत्तिस';
				break;
			case 33:
				$no='तेत्तिस';
				break;
			case 34:
				$no='चौतिस';
				break;
			case 35:
				$no='पैतिस';
				break;
			case 36:
				$no='छत्तिस';
				break;
			case 37:
				$no='सरतिस';
				break;
			case 38:
				$no='अठ्तिस';
				break;
			case 39:
				$no='उनान्चालिस';
				break;
			case 40:
				$no='चालिस';
				break;
			case 41:
				$no='एकचालिस';
				break;
			case 42:
				$no='बयालिस';
				break;
			case 43:
				$no='त्रिचालिस';
				break;
			case 44:
				$no='चौवालिस';
				break;
			case 45:
				$no='पैचालिस';
				break;
			case 46:
				$no='छयालिस';
				break;
			case 47:
				$no='सत्चालिस';
				break;
			case 48:
				$no='अठ्चालिस';
				break;
			case 49:
				$no='उनान्पचास';
				break;
			case 50:
				$no='पचास';
				break;
			case 51:
				$no='एकाउन्न';
				break;
			case 52:
				$no='बाउन्न';
				break;
			case 53:
				$no='त्रिपन्न';
				break;
			case 54:
				$no='चौवन्न';
				break;
			case 55:
				$no='पचपन्न';
				break;
			case 56:
				$no='छपन्न';
				break;
			case 57:
				$no='सन्ताउन्न';
				break;
			case 58:
				$no='अन्ठाउन्न';
				break;
			case 59:
				$no='उनान्साठी';
				break;
			case 60:
				$no='साठी';
				break;
			case 61:
				$no='एकसठ्ठी';
				break;
			case 62:
				$no='बैसठ्ठी';
				break;
			case 63:
				$no='त्रिसठ्ठी';
				break;
			case 64:
				$no='चौसठ्ठी';
				break;
			case 65:
				$no='पैँसठ्ठी';
				break;
			case 66:
				$no='छैसठ्ठी';
				break;
			case 67:
				$no='सत्सठ्ठी';
				break;
			case 68:
				$no='अठसठ्ठी';
				break;
			case 69:
				$no='उनान्सत्तरी';
				break;
			case 70:
				$no='सत्तरी';
				break;
			case 71:
				$no='एकहत्तर';
				break;
			case 72:
				$no='बहत्तर';
				break;
			case 73:
				$no='तिरहत्तर';
				break;
			case 74:
				$no='चाौरअत्तर';
				break;
			case 75:
				$no='पचहत्तर';
				break;
			case 76:
				$no='छयाहत्तर';
				break;
			case 77:
				$no='सतहत्तर';
				break;
			case 78:
				$no='अठहत्तर';
				break;
			case 79:
				$no='उनान्असी';
				break;
			case 80:
				$no='असी';
				break;
			case 81:
				$no='एकासी';
				break;
			case 82:
				$no='बयासी';
				break;
			case 83:
				$no='तिरासी';
				break;
			case 84:
				$no='चाौरासी';
				break;
			case 85:
				$no='पचासी';
				break;
			case 86:
				$no='छयासी';
				break;
			case 87:
				$no='सतासी';
				break;
			case 88:
				$no='अठासी';
				break;
			case 89:
				$no='उनान्नव्बे';
				break;
			case 90:
				$no='नव्बे';
				break;
			case 91:
				$no='एकानव्बे';
				break;
			case 92:
				$no='बयानव्बे';
				break;
			case 93:
				$no='तिरानव्बे';
				break;
			case 94:
				$no='चाौरानव्बे';
				break;
			case 95:
				$no='पञ्चानव्बे';
				break;
			case 96:
				$no='छयानव्बे';
				break;
			case 97:
				$no='सन्तानव्बे';
				break;
			case 98:
				$no='अन्ठानब्बे';
				break;
			case 99:
				$no='उनान्सय';
				break;
			default:
				$no='';
				break;
		}
		return $no;
	}

}





