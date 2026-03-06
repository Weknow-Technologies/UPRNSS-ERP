<?php
include("scripts/settings.php");
function old_dbconnect($db){
	$connect = mysqli_connect("localhost","cloudice", "clou@123", $db);
	if(!$connect){
		die('1.System error contact administrator');
	}
	return $connect;	
}

//ehrms_update();
payroll_update();

function ehrms_update(){
	global $db;
	$sql = 'SELECT * FROM `book1`';
	$result = execute_query($sql);
	echo '<table border="1">
	<tr>
	<th>S.No</th>
	<th>Employee Name</th>
	<th>Employee Code</th>
	<th>EHRMS Code</th>
	<th>Account No</th>
	<th>employee.sno</th>
	<th>Update Status</th>
	</tr>';
	$i=1;
	while($row = mysqli_fetch_assoc($result)){
		$sql = 'SELECT * FROM `employee` where employee_code="'.$row['emp_code'].'"';
		$result_emp = execute_query($sql);
		echo '<tr>
		<td>'.$i++.'</td>
		<td>'.$row['emp_name'].'</td>
		<td>'.$row['emp_code'].'</td>
		<td>'.$row['ehrms'].'</td>
		<td>'.$row['ac_no'].'</td>
		';
		if(mysqli_num_rows($result_emp)!=0){
			$row_emp = mysqli_fetch_assoc($result_emp);
			echo '<td>'.$row_emp['sno'].'</td>';
			
			$sql = 'update employee set hrms_no = "'.$row['ehrms'].'", bank_name = "'.$row['bank_name'].'", branch = "'.$row['branch'].'", acount_number = "'.$row['ac_no'].'" where sno="'.$row_emp['sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){
				echo '<td>Error : '.mysqli_error($db).' >> '.$sql.'</td>';
			}
			else{
				echo '<td>Updated</td>';
			}
		}
		else{
			echo '<td>-</td>';
		}
		echo '<td></td></tr>';
	}
}

function payroll_update(){
    $sql = 'SELECT * FROM `payslip_details` where value_type="percent" and head_name="DA"';
    $result = execute_query($sql);
    while($row = mysqli_fetch_assoc($result)){
        $sql = 'select * from payslip_details where payslip_id="'.$row['payslip_id'].'" and head_id="1"';
        $row_basic = mysqli_fetch_assoc(execute_query($sql));
        
        $sql = 'select * from payslip_details where payslip_id="'.$row['payslip_id'].'" and head_id="3"';
        $grade_pay = mysqli_fetch_assoc(execute_query($sql));
        
        $sql = 'select * from payslip_details where payslip_id="'.$row['payslip_id'].'" and head_id="9"';
        $cpf = mysqli_fetch_assoc(execute_query($sql));
        
        $da_value = ($row_basic['base_amount']+$grade_pay['base_amount'])*(float)$row['base_value']/100;
        
        $cpf_value = ($row_basic['base_amount']+$da_value)*$cpf['base_value']/100;
        
        $sql = 'update payslip_details set base_amount = "'.$da_value.'" where sno="'.$row['sno'].'"';
        //echo $sql.'<br>';
        execute_query($sql);
        
        $sql = 'update payslip_details set base_amount = "'.$cpf_value.'" where sno="'.$cpf['sno'].'"';
        execute_query($sql);
        
    }
    echo 'Done';
}
?>