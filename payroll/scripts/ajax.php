<?php
session_cache_limiter('nocache');
include("settings.php");
if(!isset($_REQUEST['term'])){
    return;
}
else{
    $q = $_REQUEST['term'];
}
$final = array();
$q = htmlspecialchars(urldecode(strtoupper($_REQUEST["term"])), ENT_QUOTES);
if (!$q) return;

if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}
else {
	$id='';
}
$data = array();

if($id=='emp_detail'){
	$sql = 'select * from dp_personal_info where sno="'.$_POST['val'].'"';
	// echo $sql;
	$emp = mysqli_fetch_assoc(mysqli_query($db_erp, $sql));
	array_push($final,$emp); 
	/*foreach($emp as $k=>$v){
		$data[$k] = $v;
		//print_r($v);
		//array_push($final, array(
	}*/
	
}
if($id=='employee_detail'){
    $final=array();
    $date = date('Y-m-d');
    $sql = 'select * from employee where employee_name like "%'.$q.'%" or employee_designation like "%'.$q.'%" or contact like "%'.$q.'%" '; 
    //echo $sql;
    $res = execute_query($sql);
    while($row = mysqli_fetch_array($res)){
        array_push($final, array("id"=>$row['sno'], "label"=>$row['employee_name'], "employee_name"=>$row['employee_name'],"mobile" => $row['contact'], "employee_designation" => $row['employee_designation'], "address" => $row['address']));
    }    
}
elseif($id=='hra'){
    $final=array();
    $date = date('Y-m-d');
	
	$sql = 'select * from uprnss_division where s_no="'.$_POST['div'].'"';
	$result = mysqli_query($db_erp, $sql);
	$row_div = mysqli_fetch_assoc($result);
	
	// echo $row_div['city_grade'];
	
	if($row_div['city_grade']=='A'){
		
		$sql = 'SELECT *  FROM `payroll_hra_master` WHERE pay_level_id ="'.$_POST['val'].'"';
		//echo $sql;
		$res = execute_query($sql);
		while($row = mysqli_fetch_array($res)){
			array_push($final, array("id"=>$row['sno'], "hra" => $row['a_hra']));
		}
	}else{
		
		$sql = 'SELECT *  FROM `payroll_hra_master` WHERE pay_level_id ="'.$_POST['val'].'"';
		//echo $sql;
		$res = execute_query($sql);
		while($row = mysqli_fetch_array($res)){
			array_push($final, array("id"=>$row['sno'], "hra" => $row['b_hra']));
		}
	}
	
		    
}


elseif($id=='customer_payment'){
    $amount = 0;
    $final=array();
    $date = date('Y-m-d');
    $sql = 'select * from employee where employee_name like "%'.$q.'%" or employee_designation like "%'.$q.'%" or contact like "%'.$q.'%" '; 
    //echo $sql;
    $res = execute_query($sql);
    while($row = mysqli_fetch_array($res)){
        
        //echo $row1['addva'];
            /**$amount = get_cust_balance("1970-01-01" , $date , $row['sno']);
            if($amount <0){
                $amount=substr($amount,1);
            }else{
                $amount = 0;
            }**/
            $amount -= get_cust_balance("1970-01-01", date("Y-m-d"), $row['sno']);
            //$amount = 0;
        array_push($final, array("id"=>$row['sno'], "label"=>$row['employee_name'], "employee_name"=>$row['employee_name'],"mobile" => $row['contact'], "employee_designation" => $row['employee_designation'], "address" => $row['address'] , "amount" => $amount));
    }    
}

echo array_to_json($final);

function array_to_json( $array ){

    if( !is_array( $array ) ){
        return false;
    }

    $associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
    if( $associative ){

        $construct = array();
        foreach( $array as $key => $value ){

            // We first copy each key/value pair into a staging array,
            // formatting each key and value properly as we go.

            // Format the key:
            if( is_numeric($key) ){
                $key = "key_$key";
            }
            $key = json_encode($key);

            // Format the value:
            if( is_array( $value )){
                $value = array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = json_encode($value);
            }

            // Add to staging array:
            $construct[] = "$key: $value";
        }

        // Then we collapse the staging array into the JSON form:
        $result = "{ " . implode( ", ", $construct ) . " }";

    } else { // If the array is a vector (not associative):

        $construct = array();
        foreach( $array as $value ){

            // Format the value:
            if( is_array( $value )){
                $value = array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = json_encode($value);
            }

            // Add to staging array:
            $construct[] = $value;
        }

        // Then we collapse the staging array into the JSON form:
        $result = "[ " . implode( ", ", $construct ) . " ]";
    }

    return $result;
}

?>