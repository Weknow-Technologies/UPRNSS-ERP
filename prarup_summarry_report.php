<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
	// if($_POST['avsheshdate_1']!=""){
		$sql='insert into invoice_prarup_new_5 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_new_5_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			
			$inv_id = mysqli_insert_id($db);
			
			for($i=1; $i<=$_POST['prarup_new_5_id']; $i++){
				if($_POST['avsheshdate_'.$i]!=""){
					$sql = 'insert into trans_prarup_new_5 (`invoice_id`,`month`, `avsheshdate`, `totelpayment`, `gsttdsbhuktan`, `gsttdsrashi`, `utrdate`, `gsttdssno`, `avshesh`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['month_'.$i].'", "'.$_POST['avsheshdate_'.$i].'", "'.$_POST['totelpayment_'.$i].'", "'.$_POST['gsttdsbhuktan_'.$i].'", "'.$_POST['gsttdsrashi_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['gsttdssno_'.$i].'", "'.$_POST['avshesh_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
				}
			}
			if($msg==""){
				$msg="<p class='alert alert-success'>Data stored all</p>";
			}
		}
	// }
	// else{
		// echo '<script>alert("Please Fill Form properly!");</script>';
	// }
}
else{
	$sql = 'update invoice_prarup_new_5 set 
		`total_trans`= "'.$_POST['prarup_5_id'].'",
		`edited_by` = "'.$_SESSION['username'].'", 
		`edition_time` = "'.date("Y-m-d H:i:s").'"
		where sno="'.$_POST['edit_sno'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$sql = 'delete from trans_prarup_new_5 where invoice_id="'.$_POST['edit_sno'].'"';
			// echo $sql;
			execute_query($sql);
			for($i=1; $i<=$_POST['prarup_new_5_id']; $i++){
				if($_POST['avsheshdate_'.$i]!=""){
					$sql = 'insert into trans_prarup_new_5 (`invoice_id`,`month`, `avsheshdate`, `totelpayment`, `gsttdsbhuktan`, `gsttdsrashi`, `utrdate`, `gsttdssno`, `avshesh`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['month_'.$i].'", "'.$_POST['avsheshdate_'.$i].'", "'.$_POST['totelpayment_'.$i].'", "'.$_POST['gsttdsbhuktan_'.$i].'", "'.$_POST['gsttdsrashi_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['gsttdssno_'.$i].'", "'.$_POST['avshesh_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
				}
			}
			if($msg==""){
				$msg="<p class='alert alert-success'>Data stored all</p>";
			}
				
			if($msg==''){
				$msg .= '<p class="alert alert-info text-dark">Data Update!</p>';
				unset($_POST);
				goto postblank;
			}	
		}
}
}
else{
postblank:
$_POST['prarup_5_id']="1";
$_POST['edit_sno']="";

$_POST['month_1']="";
$_POST['avsheshdate_1'] ="";
$_POST['totelpayment_1'] ="";
$_POST['gsttdsbhuktan_1'] ="";
$_POST['gsttdsrashi_1'] ="";
$_POST['utrdate_1'] ="";
$_POST['gsttdssno_1'] ="";
$_POST['avshesh_1'] ="";

}
if(isset($_GET['edit_sno'])){
$sql = 'select * from trans_prarup_new_5 where invoice_id="'.$_GET['edit_sno'].'"';
// echo $sql.'<br>';
$result_rcpt = execute_query($sql);
if(mysqli_num_rows($result_rcpt)!=0){
	$_POST['prarup_5_id'] = mysqli_num_rows($result_rcpt);
	$i=1;
	while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
		// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_new_4_id'].'<br/>';
		
		$_POST['month_'.$i] =$row_rcpt['month'];
		$_POST['avsheshdate_'.$i] =$row_rcpt['avsheshdate'];
		$_POST['totelpayment_'.$i] =$row_rcpt['totelpayment'];
		$_POST['gsttdsbhuktan_'.$i] =$row_rcpt['gsttdsbhuktan'];
		$_POST['gsttdsrashi_'.$i] =$row_rcpt['gsttdsrashi'];
		$_POST['utrdate_'.$i] =$row_rcpt['utrdate'];
		$_POST['gsttdssno_'.$i] =$row_rcpt['gsttdssno'];
		$_POST['avshesh_'.$i] =$row_rcpt['avshesh'];
		
		$i++;
	}
}
else{
	$_POST['month_1']="";
	$_POST['avsheshdate_1'] ="";
	$_POST['totelpayment_1'] ="";
	$_POST['gsttdsbhuktan_1'] ="";
	$_POST['gsttdsrashi_1'] ="";
	$_POST['utrdate_1'] ="";
	$_POST['gsttdssno_1'] ="";
	$_POST['avshesh_1'] ="";
}
$_POST['edit_sno']=$_GET['edit_sno'];
}

	if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_new_5 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_new_5 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}
	
?>
<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>
	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center"></h4></br>
					</div>
					<div class="card-body">
						<h4>Prarup-1</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_1`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-2</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_2`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-3</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_3`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-4</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_new_4`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-5</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_new_5`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						
						<h4>Prarup-6</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_new_6`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-7</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_7`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-8</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_8`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-9</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_9`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-10</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_new_10`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-11</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_new_11`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-12</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_new_12`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-13</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_13`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-14</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_14`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
						<h4>Prarup-15</h4>
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT  division_id, entry_date, created_by
										FROM `invoice_prarup_15`
										GROUP BY division_id';
							$result_prarup_new_5 = execute_query($sql);
							$i=1;
							while($row_prarup_new_5 = mysqli_fetch_assoc($result_prarup_new_5)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_5['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_5['entry_date'].'</td>
								</tr>';
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>	

<?php
page_footer_start();
?>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>