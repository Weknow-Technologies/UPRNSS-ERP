<?php
include("scripts/settings.php");



// salary_month_update();


function salary_month_update(){
	$sql = 'SELECT * FROM `payslip_report`';
	//die($sql);
    $result = execute_query($sql);
	
    while($row = mysqli_fetch_assoc($result)){
		
		$year = explode("-", $row['financial_year']);

		// $year[0] = 2023
		// $year[1] = 2024

		$year_to_store = '';
		$month=$row['for_month'];
		
		if($month>=1 and $month<=3){
			$year_to_store = $year[1];
		}
		else{
			$year_to_store = $year[0];
		}

		$date_to_store = $year_to_store.'-'.$month.'-01';
		
		$sql = 'update payslip_report set
		salary_month = "'.$date_to_store.'"
	
		where sno="'.$row['sno'].'"';
        execute_query($sql);
		
	}echo 'Done';
	
}

attendance_update();


function attendance_update(){
	set_time_limit(0);
    $sql = 'SELECT * FROM `attendance_details`';
	//die($sql);
    $result = execute_query($sql);
	
    while($row = mysqli_fetch_assoc($result)){
		
		
		$sql = 'update payslip_report set
		
		company_id = "'.$row['company_id'].'" 
	
		where emp_id="'.$row['emp_id'].'" and financial_year="'.$row['financial_year'].'" and for_month="'.$row['salary_generation_month'].'"';
        execute_query($sql);
        
    }
    echo 'Done';
}

// working_days = "'.$row['working_days'].'",
		// granted_leave = "'.$row['granted_leave'].'" ,
		// presented_days = "'.$row['presented_days'].'" 

//payroll_update();

function payroll_update(){
	set_time_limit(0);
    echo $sql = 'SELECT payslip_id, emp_id FROM payslip_details 
	left join payslip_report on payslip_details.payslip_id=payslip_report.sno
	where value_type="percent" and head_name="DA" order by payslip_details.sno ';
	//die($sql);
    $result = execute_query($sql);
	
    while($row = mysqli_fetch_assoc($result)){
		
		$sql = 'select * from salary_structure where emp_id="'.$row['emp_id'].'" and head_id="1"';
        $row_basic = mysqli_fetch_assoc(execute_query($sql));
		
		$sql = 'select * from salary_structure where emp_id="'.$row['emp_id'].'" and head_id="3"';
        $grade_pay = mysqli_fetch_assoc(execute_query($sql));
		
		$sql = 'select * from salary_structure where emp_id="'.$row['emp_id'].'" and head_id="4"';
        $row_DA = mysqli_fetch_assoc(execute_query($sql));
		
		$sql = 'select * from salary_structure where emp_id="'.$row['emp_id'].'" and head_id="9"';
        $cpf = mysqli_fetch_assoc(execute_query($sql));
        
        $sql = 'select * from payslip_details where payslip_id="'.$row['payslip_id'].'" and head_id="9"';
        $cpfsno = mysqli_fetch_assoc(execute_query($sql));
        
        // $da_value = ($row_basic['base_amount']+$grade_pay['base_amount'])*(float)$row['base_value']/100;
        
        // $cpf_value = ($row_basic['base_amount']+$da_value)*$cpf['base_value']/100;
		
		$da_value = (floatval($row_basic['head_value']) + floatval($grade_pay['head_value'])) * (floatval($row_DA['head_value']) / 100);
		$cpf_value = (floatval($row_basic['head_value']) + floatval($da_value)) * (floatval($cpf['head_value']) / 100);
		
      echo $row['emp_id'].'<br>';
      echo $da_value.'<br>';
      echo $cpf_value.'<br>';
		
       // $sql = 'update payslip_details set base_amount = "'.$da_value.'" where payslip_id="'.$row['payslip_id'].'"';
        // //echo $sql.'<br>';
        // execute_query($sql);
        
       $sql = 'update payslip_details set base_amount = "'.$cpf_value.'" where sno="'.$cpfsno['sno'].'"';
        execute_query($sql);
        
    }
    echo 'Done';
}




// insert_ehrmsid ();
// function insert_ehrmsid() {
    // $empsql = "SELECT * FROM update_ehrms_final_step";
    // $result = execute_query($empsql);
    
    // while ($row = mysqli_fetch_array($result)) { 
        // $emp_code = $row['emp_code']; 
        // $ehrms_code = $row['ehrms_id']; 
        
        
        // $emp_sql = "SELECT * FROM employee WHERE employee_code='$emp_code'";
        // $emp_result = execute_query($emp_sql);
       // echo $emp_sql . '</br>';
       // $update = "UPDATE employee SET  ehrms_id = '$ehrms_code' WHERE employee_code = '$emp_code'";
		// $res = execute_query($update);
		// if($res){
			// $msg1 = '<div class="alert alert-success">Update dp</div>';
		// } else {
			
			// $msg1 .= '<div class="alert alert-danger">Error # AE003 : ' . mysqli_error($db) . ' >> ' . $update . '</div>';
		// }

		 // echo $update . '</br>';
		 // echo $msg1 . '</br>';
    // }
// }





// function insert_salary_stucture (){
	
// set_time_limit(300); // Set to 5 minutes (adjust as needed)

// $empsql = "SELECT employee.sno AS employee_sno, head_type.sno AS head_type_sno FROM employee, head_type";
// $empres = mysqli_query($db, $empsql);
// echo $emprowcount = mysqli_num_rows($empres);
// $i = 1;
// $count = 0;
// $good = 0;
// $bad = 0;
// $str = "<table><tr><td>Emp ID</td><td>Head ID</td></tr>";

// while ($rowemp = mysqli_fetch_assoc($empres)) {
   // $check = "SELECT head_id,emp_id FROM `salary_structure` WHERE emp_id='" . $rowemp['employee_sno'] . "' and head_id='" . $rowemp['head_type_sno'] . "'";
    // $checkres = mysqli_query($db, $check);

    // if (mysqli_num_rows($checkres) == 0) {
        // $insert = "INSERT INTO `salary_structure`(`emp_id`, `head_id`) VALUES ('" . $rowemp['employee_sno'] . "','" . $rowemp['head_type_sno'] . "')";
        // $res = mysqli_query($db, $insert);
        // if ($res) {
			// echo "hello<br>";
            // $count++;
            // $str .= "<tr><td>" . $rowemp['employee_sno'] . "</td><td>" . $rowemp['head_type_sno'] . "</td></tr>";
        // }
    // } elseif (mysqli_num_rows($checkres) == 1) {
		// echo "good<br>";
        // $good++;
    // } else {
		// echo "bad <br>";
        // $bad++;
    // }
    // $i++;
// }

// $str .= "</table>";  // Corrected assignment

// echo $count . " Times data inserted";
// echo "<br>";
// echo $good . " data right";
// echo "<br>";
// echo $bad . " Data Wrong";
// echo "<br>";
// echo $i . " Times loop run";
// echo "<br>";
// echo $str;

// }




?>
