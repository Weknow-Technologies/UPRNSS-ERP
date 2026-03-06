<?php
include("settings.php");
date_default_timezone_set('Asia/Calcutta');
$time = mktime(true);
session_cache_limiter('nocache');
//session_start();
//$q = htmlspecialchars(urldecode(strtoupper($_GET["term"])), ENT_QUOTES);
//if (!$q) return;
//$q = htmlspecialchars(urldecode(strtoupper($_GET["term"])), ENT_QUOTES);
//if (!$q) return;

if(isset($_REQUEST['id'])){
    $id = $_REQUEST['id'];
}
else {
    $id='';
}
if(!isset($_GET['term'])){
    return;
}
else{
    $q = $_GET['term'];
}
$result = array();


if($id=='projects'){
    $q=$_GET['term'];
	$sql = 'select * from projects where client_id="'.$q.'"';
	$result_projects = execute_query($sql);
	while($row = mysqli_fetch_array($result_projects)){
		array_push($result, array("id"=>$row['sno'], "label"=>$row['project_name'], "rate"=>$row['rate'], "start_date" => $row['start_date'], "rate_type" => $row['rate_type'], "over_time_rate" => $row['over_time_rate'], "full_over_time_days_rate" => $row['full_over_time_days_rate'], "full_night_rate" => $row['full_night_rate']));
	}
	
}

if($id =='projects_calc'){
	$sql = 'select * from projects where sno="'.$_GET['proj'].'"';
    $result_projects = execute_query($sql);
	while($row = mysqli_fetch_array($result_projects)){
		array_push($result, array("id"=>$row['sno'], "label"=>$row['project_name'], "rate"=>$row['rate'], "start_date" => $row['start_date'], "rate_type" => $row['rate_type'], "over_time_rate" => $row['over_time_rate'], "full_over_time_days_rate" => $row['full_over_time_days_rate'], "full_night_rate" => $row['full_night_rate']));
	}
	
	//array_push($result, array("id"=>"sample_calculation"));
	
}
if($id=='salary'){
	$sql = 'select head_type.sno as sno, head_name, head_value from salary_structure join head_type on head_type.sno=salary_structure.head_id where emp_id="'.$q.'"';
	$result_projects = execute_query($sql);
	while($row = mysqli_fetch_array($result_projects)){
		array_push($result, array("id"=>$row['sno'], "label"=>$row['head_name'], "head_value"=>$row['head_value']));
	}
	
}
if($id=='customer_payment'){
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
            $amount = 0;
        array_push($final, array("id"=>$row['sno'], "label"=>$row['employee_name'], "employee_name"=>$row['employee_name'],"mobile" => $row['contact'], "employee_designation" => $row['employee_designation'], "address" => $row['address'] , "amount" => $amount));
    }
    
}
if(empty($result)!=true){
    echo array_to_json($result);
}
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

if(isset($_POST['search'])){
             $search= $_POST['search'];
             //echo "$search";
             $query = "SELECT * FROM `client_details` WHERE  client_name like '%".$search."%'";
            // echo $query;
             $result = execute_query($query);
             $response = array();
             while($row = mysqli_fetch_array($result) ){
               $response[] = array("id"=>$row['sno'], "cust_name"=>$row['client_name'], "address"=>$row['address'], "gst"=>$row['gst'],"mobile"=>$row['mobile'], "label"=>$row['client_name']);
             }

             echo json_encode($response);
        }

        if(isset($_POST['search1'])){
             $search= $_POST['search1'];
             //echo "$search";
             $query = "SELECT * FROM `client_details` WHERE   client_name like '%".$search."%'";
             //echo $query;
             $result = execute_query($query);
             $response = array();
             while($row = mysqli_fetch_array($result) ){
               $response[] = array( "cust_name"=>$row['client_name'], "label"=>$row['client_name']);
             }

             echo json_encode($response);
        }

?>