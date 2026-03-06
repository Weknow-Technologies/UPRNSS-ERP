<?php
include("scripts/settings.php");
function old_dbconnect($db){
	$connect = mysqli_connect("localhost","cloudice", "clou@123", $db);
	if(!$connect){
		die('1.System error contact administrator');
	}
	return $connect;	
}

//check_data();
//disable_projects();
//update_division();
//temp_report();
status_update();

function status_update(){
    $link = old_dbconnect('cloudice_uprnss_2023_bk');
    $sql = 'select * from uprnss_project_temp';
    $result = mysqli_query($link, $sql);
    $data = array();
    while($row = mysqli_fetch_assoc($result)){
        $data[$row['sno']]['project_name'] = $row['project_name'];
        $data[$row['sno']]['old_status'] = $row['project_status_1'];
    }
    
    $link = old_dbconnect('cloudice_uprnss');
    $sql = 'select * from uprnss_project_temp';
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_assoc($result)){
        $data[$row['sno']]['new_status'] = $row['project_status_1'];
    }
    echo '<table border="1">
    <tr>
    <th>S.No.</th>
    <th>Project Name</th>
    <th>Old Status</th>
    <th>New Status</th></tr>';
    $i=1;
    //print_r($data);
    
    $link = old_dbconnect('cloudice_uprnss');
    foreach($data as $k=>$v){
        $sql = '';
        if($v['new_status']==''){
            $sql = 'update uprnss_project_temp set project_status_1="'.$v['old_status'].'" where sno="'.$k.'"';
            //mysqli_query($link, $sql);
            
        }
        echo '<tr><td>'.$i++.'</td>
        <td>'.$v['project_name'].'</td>
        <td>'.$v['old_status'].'</td>
        <td>'.$v['new_status'].'</td>
        <td>'.$sql.'</td>
        </tr>';
    }
    
}

function temp_report(){
    $tot=0;
	$sql = 'SELECT division_name, district_name_hindi, count(*) c  FROM `uprnss_project_temp` left join uprnss_district on uprnss_district.sno = district_id left join uprnss_division on uprnss_division.s_no = division_id where status!=5 group by division_id, district_id order by division_name';
	$result = execute_query($sql);
	echo '<table border="1" cellspacing="0" cellpadding="0">
	<tr>
	<th>S.No.</th>
	<th>Division Name</th>
	<th>District Name</th>
	<th>Total Project Count</th>
	</tr>';
	$i=1;
	while($row = mysqli_fetch_assoc($result)){
		echo '<tr>
		<td>'.$i++.'</td>
		<td>'.$row['division_name'].'</td>
		<td>'.$row['district_name_hindi'].'</td>
		<td>'.$row['c'].'</td>
		</tr>';
		$tot+=$row['c'];
	}
	echo '</table>';
	echo $tot;
}

function check_data(){
	$sql = 'SELECT * FROM `transaction_civil_activities` where creation_time<"2023-05-12"  group by invoice_id';
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$sql = 'delete from invoice_civil where sno="'.$row['invoice_id'].'"';
		execute_query($sql);
		$sql = 'delete from transaction_civil_activities where invoice_id="'.$row['invoice_id'].'"';
		execute_query($sql);
		$sql = 'delete from transaction_civil_attachment where invoice_id="'.$row['invoice_id'].'"';
		execute_query($sql);
			
		$sql = 'delete from transaction_civil_receipts where invoice_id="'.$row['invoice_id'].'"';
		execute_query($sql);
			
	}
	echo 'Deleted Done<br/>';
	
	$sql = 'select * from invoice_civil';
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$sql = 'select * from transaction_civil_activities where invoice_id="'.$row['sno'].'"';
		$result_act = execute_query($sql);
		if(mysqli_num_rows($result_act)==0){
			echo 'Invalid : '.$row['sno'];
			$sql = 'delete from invoice_civil where sno="'.$row['sno'].'"';
			execute_query($sql);
			$sql = 'delete from transaction_civil_activities where invoice_id="'.$row['sno'].'"';
			execute_query($sql);
			$sql = 'delete from transaction_civil_attachment where invoice_id="'.$row['sno'].'"';
			execute_query($sql);

			$sql = 'delete from transaction_civil_receipts where invoice_id="'.$row['sno'].'"';
			execute_query($sql);
			echo '. Deleted<br>';
		}
	}
	
}

function disable_projects(){
	global $db;
	$id = $_GET['id'];
	$sql = 'select * from uprnss_division where s_no='.$_GET['id'];
	$division = mysqli_fetch_assoc(execute_query($sql));
	echo $division['division_name'];
	
	$master_filled=0;
	$master_not_filled=0;
	$master_not_filled_array=array();
	$active = 0;
	$not_active = 0;
	$sql = 'select * from uprnss_project_temp where division_id='.$_GET['id'];
	$result_project = execute_query($sql);
	echo '<table border="1">';
	$i=1;
	while($row_project = mysqli_fetch_assoc($result_project)){
		if($row_project['status']=='1'){
			$status = 'Disabled';
			$not_active++;
		}
		else{
			$status = 'Active';
			$active++;
		}
		if($row_project['admin_go_no']==''){
			$master_not_filled++;	
			$master_not_filled_array[] = $row_project['sno'];
		}
		else{
			$master_filled++;
		}
		echo '<tr>
		<td>'.$i++.'</td>
		<td>'.$row_project['project_name_hindi'].'</td>
		<td>'.$row_project['sno'].'</td>
		<td>'.$status.'</td>
		<td>'.$row_project['admin_go_no'].'</td>
		</tr>';
	}
	echo '</table>';
	echo '<h4>Master Filled : '.$master_filled.'. Not Filled : '.$master_not_filled.' ('.implode(", ", $master_not_filled_array).') Active : '.$active.'. Not Active : '.$not_active.'.</h4>';
	echo '<h4><a href="test.php?id='.$_GET['id'].'&dis=1">Click Here to Disable Not Filled</a></h4>';
	if(isset($_GET['dis'])){
		$sql = 'UPDATE uprnss_project_temp SET `status` = "1" WHERE sno IN ('.implode(", ", $master_not_filled_array).')';
		execute_query($sql);
		if(mysqli_error($db)){
			echo '<h4>Failed : '.mysqli_error($db).' >> '.$sql.'</h4>';
		}
		else{
			echo '<h4>Success : >> '.$sql.'</h4>';
		}
	}
}

function update_division(){
	global $db;
	$sql = 'select * from uprnss_project_temp';
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$sql = 'update invoice_civil set division_id="'.$row['division_id'].'" where project_name="'.$row['sno'].'"';
		execute_query($sql);
		if(mysqli_error($db)){
			die(mysqli_error($db).' >> '.$sql.' >> ');
		}
	}
}

?>