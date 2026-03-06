<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		if($_POST['avsheshdate_1']!=""){
			$sql='insert into invoice_prarup_new_6 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_new_6_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
			execute_query($sql);
			
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_new_6_id']; $i++){
					if($_POST['avsheshdate_'.$i]!=""){
						$sql = 'insert into trans_prarup_new_6 (`invoice_id`, `month`,`avsheshdate`, `totelpayment`, `tenderfee`, `tenderfeemonth`, `utrnumber`,  `utrdate`, `maintenderfee`, `avsheshtenderfee`, `otherremark`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['month_'.$i].'","'.$_POST['avsheshdate_'.$i].'", "'.$_POST['totelpayment_'.$i].'", "'.$_POST['tenderfee_'.$i].'", "'.$_POST['tenderfeemonth_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['utrnumber_'.$i].'", "'.$_POST['maintenderfee_'.$i].'", "'.$_POST['avsheshtenderfee_'.$i].'", "'.$_POST['otherremark_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
		}
	else{
		echo '<script>alert("Please Fill Form properly!");</script>';
	}
}
	else{
		$sql = 'update invoice_prarup_2 set 
			`total_trans`= "'.$_POST['prarup_2_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("Y-m-d H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_2 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1; $i<=$_POST['prarup_2_id']; $i++){
					if($_POST['month_'.$i]!=""){
						$sql = 'insert into trans_prarup_2 (`invoice_id`,`month`, `bankname`, `acctype`, `rate`, `startingavshesh`, `monthbyaj`, `kramikarjit`, `mukhyalayamahpreshit`, `utrdate`, `mukhyalayakramik`, `avsheshvyaj`, `resonavshesh`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['month_'.$i].'","'.$_POST['bankname_'.$i].'", "'.$_POST['acctype_'.$i].'", "'.$_POST['rate_'.$i].'", "'.$_POST['startingavshesh_'.$i].'", "'.$_POST['monthbyaj_'.$i].'", "'.$_POST['kramikarjit_'.$i].'", "'.$_POST['mukhyalayamahpreshit_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['mukhyalayakramik_'.$i].'", "'.$_POST['avsheshvyaj_'.$i].'", "'.$_POST['resonavshesh_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
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
	$_POST['prarup_2_id']="1";
	$_POST['edit_sno']="";

	$_POST['month_1']="";
	$_POST['bankname_1'] ="";
	$_POST['acctype_1'] ="";
	$_POST['rate_1'] ="";
	$_POST['startingavshesh_1'] ="";
	$_POST['monthbyaj_1'] ="";
	$_POST['kramikarjit_1'] ="";
	$_POST['mukhyalayamahpreshit_1'] ="";
	$_POST['utrdate_1'] ="";
	$_POST['mukhyalayakramik_1'] ="";
	$_POST['avsheshvyaj_1'] ="";
	$_POST['resonavshesh_1'] ="";


	}
	if(isset($_GET['edit_sno'])){
		$sql = 'select * from trans_prarup_2 where invoice_id="'.$_GET['edit_sno'].'"';
		// echo $sql.'<br>';
		$result_rcpt = execute_query($sql);
		if(mysqli_num_rows($result_rcpt)!=0){
			$_POST['prarup_2_id'] = mysqli_num_rows($result_rcpt);
			$i=1;
			while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
				// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_new_4_id'].'<br/>';
				
				$_POST['month_'.$i]=$row_rcpt['month'];
				$_POST['bankname_'.$i] =$row_rcpt['bankname'];
				$_POST['acctype_'.$i] =$row_rcpt['acctype'];
				$_POST['rate_'.$i] =$row_rcpt['rate'];
				$_POST['startingavshesh_'.$i] =$row_rcpt['startingavshesh'];
				$_POST['monthbyaj_'.$i] =$row_rcpt['monthbyaj'];
				$_POST['kramikarjit_'.$i] =$row_rcpt['kramikarjit'];
				$_POST['mukhyalayamahpreshit_'.$i] =$row_rcpt['mukhyalayamahpreshit'];
				$_POST['utrdate_'.$i] =$row_rcpt['utrdate'];
				$_POST['mukhyalayakramik_'.$i] =$row_rcpt['mukhyalayakramik'];
				$_POST['avsheshvyaj_'.$i] =$row_rcpt['avsheshvyaj'];
				$_POST['resonavshesh_'.$i] =$row_rcpt['resonavshesh'];
				
				$i++;
			}
		}
		else{
			$_POST['month_1']="";
			$_POST['bankname_1'] ="";
			$_POST['acctype_1'] ="";
			$_POST['rate_1'] ="";
			$_POST['startingavshesh_1'] ="";
			$_POST['monthbyaj_1'] ="";
			$_POST['kramikarjit_1'] ="";
			$_POST['mukhyalayamahpreshit_1'] ="";
			$_POST['utrdate_1'] ="";
			$_POST['mukhyalayakramik_1'] ="";
			$_POST['avsheshvyaj_1'] ="";
			$_POST['resonavshesh_1'] ="";
		}
		$_POST['edit_sno']=$_GET['edit_sno'];
}

if(isset($_GET['delid'])){
	
	$sql = 'delete from invoice_prarup_new_6 where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		
	}
	else{
		$sql = 'delete from trans_prarup_new_6 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
	}
	
	
}
?>
	

<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>

	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">मुख्यालय को टेंडर फीस का विवरण </h4></br>
					</div>
						<?php echo $msg; ?>
					<div class="card-body">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label >प्रखण्ड का नाम</label>
									<input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION["unit_name"]; ?>" readonly \>
									<input type="hidden" name="division_id" id="division_id" class="form-control" placeholder=" " value="<?php echo $_SESSION["divisions"][0];?>" readonly \>
								</div>
							</div>
						</div>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-3">
								<label >माह</label>
								<select name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control"  value="<?php echo isset($_GET['editid']) ? $editrow['month_'.$i] : ''; ?>" tabindex="<?php echo $tab++; ?>">
									<option value="Select">Select--	</option>
									<option value="January" >January</option>
									<option value="February" >February</option>
									<option value="March" >March</option>
									<option value="April" >April</option>
									<option value="May" >May</option>
									<option value="June" >June</option>
									<option value="July" >July</option>
									<option value="August" >August</option>
									<option value="September" >September</option>
									<option value="October" >October</option>
									<option value="November" >November</option>
									<option value="December" >December</option>
								</select>
							</div>
							<div class="col-md-3">
								<label>देय टेण्डर फीस का प्रारम्भिक अवशेष 01.04.2023</label>
								<input type="text" class="form-control" name="avsheshdate_<?php echo $i; ?>" id="avsheshdate_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['avsheshdate_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>टेण्डर के सापेक्ष माह में प्राप्त टेण्डर फीस</label>
								<input type="text" class="form-control" name="totelpayment_<?php echo $i; ?>" id="totelpayment_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['totelpayment_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>टेण्डर के सापेक्ष क्रमिक प्राप्त टेण्डर फीस</label>
								<input type="text" class="form-control" name="tenderfee_<?php echo $i; ?>" id="tenderfee_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['tenderfee_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में मुख्यालय को प्रेषित टेण्डर फीस</label>
								<input type="text" class="form-control" name="tenderfeemonth_<?php echo $i; ?>" id="tenderfeemonth_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['tenderfeemonth_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>यू०टी०आर०नम्बर</label>
								<input type="text" class="form-control" name="utrdate_<?php echo $i; ?>" id="utrdate_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['utrdate_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>यू०टी०आर०  दिनांक</label>
								<input type="date" class="form-control" name="utrnumber_<?php echo $i; ?>" id="utrnumber_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['utrnumber_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>मुख्यालय को क्रमिक प्रेषित टेण्डर फीस</label>
								<input type="text" class="form-control" name="maintenderfee_<?php echo $i; ?>" id="maintenderfee_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['maintenderfee_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष टेण्डर फीस</label>
								<input type="text" class="form-control" name="avsheshtenderfee_<?php echo $i; ?>" id="avsheshtenderfee_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['avsheshtenderfee_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>अन्य विवरण</label>
								<input type="text" class="form-control" name="otherremark_<?php echo $i; ?>" id="otherremark_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['otherremark_'.$i] : ''; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_new_6_add_button" class="btn btn-info pull-right" onClick="prarup_new_6_add_rows()">Add</button>
								
							</div>
							
						</div>
						<input type="hidden" name="prarup_new_6_id" id="prarup_new_6_id" value="1">
						<div id="prarup_new_6_insert"></div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success">Submit</button>
								<input type="hidden" id="id" name="id" value="1">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<script>
	function prarup_new_6_add_rows(){
       
		var id = parseFloat($("#prarup_new_6_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_new_6_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control"  value="" tabindex=""><option value="Select">Select--</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div><div class="col-md-3"><label>देय टेण्डर फीस का प्रारम्भिक अवशेष 01.04.2023</label><input type="text" class="form-control" name="avsheshdate_'+id+'" id="avsheshdate_'+id+'" value="" ></div><div class="col-md-3"><label>टेण्डर के सापेक्ष माह में प्राप्त टेण्डर फीस</label><input type="text" class="form-control" name="totelpayment_'+id+'" id="totelpayment_'+id+'" value="" ></div><div class="col-md-3"><label>टेण्डर के सापेक्ष क्रमिक प्राप्त टेण्डर फीस</label><input type="text" class="form-control" name="tenderfee_'+id+'" id="tenderfee_'+id+'" value="" ></div><div class="col-md-3"><label>माह में मुख्यालय को प्रेषित टेण्डर फीस</label><input type="text" class="form-control" name="tenderfeemonth_'+id+'" id="tenderfeemonth_'+id+'" value="" ></div><div class="col-md-3"><label>यू०टी०आर०नम्बर</label><input type="text" class="form-control" name="utrdate_'+id+'" id="utrdate_'+id+'" value="" ></div><div class="col-md-3"><label>यू०टी०आर०  दिनांक</label><input type="date" class="form-control" name="utrnumber_'+id+'" id="utrnumber_'+id+'" value=""></div><div class="col-md-3"><label>मुख्यालय को क्रमिक प्रेषित टेण्डर फीस</label><input type="text" class="form-control" name="maintenderfee_'+id+'" id="maintenderfee_'+id+'" value="" ></div><div class="col-md-3"><label>अवशेष टेण्डर फीस</label><input type="text" class="form-control" name="avsheshtenderfee_'+id+'" id="avsheshtenderfee_'+id+'" value="" ></div><div class="col-md-3"><label>अन्य विवरण</label><input type="text" class="form-control" name="otherremark_'+id+'" id="otherremark_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_new_6_add_button" class="btn btn-info pull-right" onClick="prarup_new_6_add_rows()">Add</button></div></div>';
            $("#prarup_new_6_insert").append(txt);
            $("#prarup_new_6_id").val(id);
	}
	</script>

	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">मुख्यालय को टेंडर फीस का विवरण </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_6` where invoice_prarup_new_6.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_new_6 = execute_query($sql);
							$i=1;
							while($row_prarup_new_6 = mysqli_fetch_assoc($result_prarup_new_6)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_6['division_id'].'"';
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
								<td>'.date("d-m-Y",strtotime($row_prarup_new_6['entry_date'])).'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_6['sno'].'" ><i class="far fa-trash-alt"></i></a></td>
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