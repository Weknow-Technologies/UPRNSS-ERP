<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if(isset($_POST['edit_sno']) && $_POST['edit_sno']==''){ //insert
		if(isset($_POST['monthlytarget_1']) && $_POST['monthlytarget_1']!=""){
			$sql='insert into invoice_prarup_13 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("d-m-Y").'", "'.$_POST['prarup_13_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_13_id']; $i++){
					$sql = 'insert into trans_prarup_13 (`invoice_id`,month,`monthlytarget`, `monthprogress`, `totalprogress`, `swekritdhanrashi`, `avmuktdhanrashi`, `vyadhanrashi`, `avsheshdhanrashi`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['month_'.$i].'","'.$_POST['monthlytarget_'.$i].'", "'.$_POST['monthprogress_'.$i].'", "'.$_POST['totalprogress_'.$i].'", "'.$_POST['swekritdhanrashi_'.$i].'", "'.$_POST['avmuktdhanrashi_'.$i].'", "'.$_POST['vyadhanrashi_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
				}
				if($msg==""){
					$msg="<p class='alert alert-success'>Data save successfully!</p>";
					unset($_POST);
					goto postblank;
				}
			}
		}
		else{
			echo '<script>alert("Please Fill Form properly!");</script>';
		}
	}
	else{
		
		$sql = 'update invoice_prarup_13 set 
			`total_trans`= "'.$_POST['prarup_13_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("d-m-Y H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_13 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1; $i<=$_POST['prarup_13_id']; $i++){
					if($_POST['month_'.$i]!=""){//???
						$sql = 'insert into trans_prarup_13 (`invoice_id`,`month`,`monthlytarget`, `monthprogress`, `totalprogress`, `swekritdhanrashi`, `avmuktdhanrashi`, `vyadhanrashi`, `avsheshdhanrashi`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['month_'.$i].'", "'.$_POST['monthlytarget_'.$i].'", "'.$_POST['monthprogress_'.$i].'", "'.$_POST['totalprogress_'.$i].'", "'.$_POST['swekritdhanrashi_'.$i].'", "'.$_POST['avmuktdhanrashi_'.$i].'", "'.$_POST['vyadhanrashi_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
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

	$_POST['month_1']="";
	$_POST['monthlytarget_1']="";
	$_POST['monthprogress_1']="";
	$_POST['totalprogress_1']="";
	$_POST['swekritdhanrashi_1']="";
	$_POST['avmuktdhanrashi_1']="";
	$_POST['vyadhanrashi_1']="";
	$_POST['avsheshdhanrashi_1']="";
	//diff
	$_POST['edit_sno']="";//initial values i guess
	$_POST['prarup_13_id']="1";
	
}


	if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_13 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_13 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning text-dark">Data Delete</div>';
		}
		
	}	
	if(isset($_GET['edit_sno'])){
		
			$sql = 'select * from trans_prarup_13 where invoice_id="'.$_GET['edit_sno'].'"';
			// echo $sql.'<br>';
			$result_rcpt = execute_query($sql);
			if(mysqli_num_rows($result_rcpt)!=0){
				$_POST['prarup_13_id'] = mysqli_num_rows($result_rcpt);
				$i=1;
				while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
					
					$_POST['month_'.$i] = $row_rcpt['month'];
					$_POST['monthlytarget_'.$i]= $row_rcpt['monthlytarget'];
					$_POST['monthprogress_'.$i]= $row_rcpt['monthprogress'];
					$_POST['totalprogress_'.$i]= $row_rcpt['totalprogress'];
					$_POST['swekritdhanrashi_'.$i]= $row_rcpt['swekritdhanrashi'];
					$_POST['avmuktdhanrashi_'.$i]= $row_rcpt['avmuktdhanrashi'];
					$_POST['vyadhanrashi_'.$i]= $row_rcpt['vyadhanrashi'];
					$_POST['avsheshdhanrashi_'.$i]= $row_rcpt['avsheshdhanrashi'];
					
					
					$i++;
				}
			}
			else{
				$_POST['month_1']="";
				$_POST['monthlytarget_1']="";
				$_POST['monthprogress_1']="";
				$_POST['totalprogress_1']="";
				$_POST['swekritdhanrashi_1']="";
				$_POST['avmuktdhanrashi_1']="";
				$_POST['vyadhanrashi_1']="";
				$_POST['avsheshdhanrashi_1']="";
			}
		$_POST['edit_sno']=$_GET['edit_sno'];
	}
	
?>
<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>

<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
    <div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title text-center">प्रखण्ड की प्रगति</h4></br>
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
					<?php
						for($i=1;$i<= $_POST['prarup_13_id'];$i++){
					?>
					<div class="row border rounded m-2 p-2 border-secondary">
						<div class="col-md-3">
							<label >माह</label>
							<select name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control"  value="<?php echo $editrow['month_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
							<option value="">--Select--</option>
									<option value="January"<?php echo ($_POST['month_'.$i]=="January" ? 'selected' : ''); ?> >January</option>
									<option value="February"<?php echo ($_POST['month_'.$i]=="February"?'selected':''); ?> >February</option>
									<option value="March"<?php echo ($_POST['month_'.$i]=="March"?'selected':''); ?> >March</option>
									<option value="April"<?php echo ($_POST['month_'.$i]=="April"?'selected':''); ?> >April</option>
									<option value="May"<?php echo ($_POST['month_'.$i]=="May"?'selected':''); ?> >May</option>
									<option value="June"<?php echo ($_POST['month_'.$i]=="June"?'selected':''); ?> >June</option>
									<option value="July"<?php echo ($_POST['month_'.$i]=="July"?'selected':''); ?> >July</option>
									<option value="August"<?php echo ($_POST['month_'.$i]=="August"?'selected':''); ?> >August</option>
									<option value="September"<?php echo ($_POST['month_'.$i]=="September"?'selected':''); ?> >September</option>
									<option value="October"<?php echo ($_POST['month_'.$i]=="October"?'selected':''); ?> >October</option>
									<option value="November"<?php echo ($_POST['month_'.$i]=="November"?'selected':''); ?> >November</option>
									<option value="December"<?php echo ($_POST['month_'.$i]=="December"?'selected':''); ?> >December</option>
							</select>
						</div>
						<div class="col-md-3">
							<label>माह का लक्ष्य </label>
							<input type="text" class="form-control" name="monthlytarget_<?php echo $i; ?>" id="monthlytarget_<?php echo $i; ?>" value="<?php echo $_POST['monthlytarget_'.$i]; ?>"></div>
						<div class="col-md-3">
							<label>माह की प्रगति (वर्क डन)</label>
							<input type="text" class="form-control" name="monthprogress_<?php echo $i; ?>" id="monthprogress_<?php echo $i; ?>" value="<?php echo $_POST['monthprogress_'.$i]; ?>">
						</div>
						<div class="col-md-3">
							<label>कुल प्रगति (01 अप्रैल 23 से अब तक)</label>
							<input type="text" class="form-control" name="totalprogress_<?php echo $i; ?>" id="totalprogress_<?php echo $i; ?>" value="<?php echo $_POST['totalprogress_'.$i]; ?>">
						</div>
						<div class="col-md-3">
							<label>वेतन</label>
							<input type="text" class="form-control" name="swekritdhanrashi_<?php echo $i; ?>" id="swekritdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['swekritdhanrashi_'.$i];?>">
						</div>
						<div class="col-md-3">
							<label>अन्य मद</label>
							<input type="text" class="form-control" name="avmuktdhanrashi_<?php echo $i; ?>" id="avmuktdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['avmuktdhanrashi_'.$i]; ?>">
						</div>
						<div class="col-md-3">
							<label>कुल योग</label>
							<input type="text" class="form-control" name="vyadhanrashi_<?php echo $i; ?>" id="vyadhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['vyadhanrashi_'.$i] ;?>">
						</div>
						<div class="col-md-3">
							<label>कुल प्रगति के सापेक्ष व्यय का %</label>
							<input type="text" class="form-control" name="avsheshdhanrashi_<?php echo $i; ?>" id="avsheshdhanrashi_<?php echo $i; ?>" value="<?php echo  $_POST['avsheshdhanrashi_'.$i]; ?>">
						</div>
						<div class="col-md-1 d-flex justify-content- align-items-center">
							<button type="button" id="prarup_13_add_button" class="btn btn-info pull-right" onClick="prarup_13_add_rows()">Add</button>
						</div>
					</div>
					<?php } ?>
						<input type="hidden" name="prarup_13_id" id="prarup_13_id" value="<?php echo $_POST['prarup_13_id']; ?>">
						<div id="prarup_13_insert"></div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success">Submit</button>
								<input type="hidden" id="id" name="id" value="1">
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script>
	function prarup_13_add_rows(){
       
		var id = parseFloat($("#prarup_13_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_13_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control"  value="" tabindex=""><option value="Select">Select--	</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div><div class="col-md-3"><label>माह का लक्ष्य </label><input type="text" class="form-control" name="monthlytarget_'+id+'" id="monthlytarget_'+id+'" value="" ></div><div class="col-md-3"><label>माह की प्रगति (वर्क डन)</label><input type="text" class="form-control" name="monthprogress_'+id+'" id="monthprogress_'+id+'" value="" ></div><div class="col-md-3"><label>कुल प्रगति (01 अप्रैल 23 से अब तक)</label><input type="text" class="form-control" name="totalprogress_'+id+'" id="totalprogress_'+id+'" value="" ></div><div class="col-md-3"><label>वेतन</label><input type="text" class="form-control" name="swekritdhanrashi_'+id+'" id="swekritdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>अन्य मद</label><input type="text" class="form-control" name="avmuktdhanrashi_'+id+'" id="avmuktdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>कुल योग</label><input type="text" class="form-control" name="vyadhanrashi_'+id+'" id="vyadhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>कुल प्रगति के सापेक्ष व्यय का %</label><input type="text" class="form-control" name="avsheshdhanrashi_'+id+'" id="avsheshdhanrashi_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_13_add_button" class="btn btn-info pull-right" onClick="prarup_13_add_rows()">Add</button></div></div>';
            $("#prarup_13_insert").append(txt);
            $("#prarup_13_id").val(id);
	}
	</script>
    
	<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्ड की प्रगति</h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>delete</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_13` where invoice_prarup_13.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_13 = execute_query($sql);
							$i=1;
							while($row_prarup_13 = mysqli_fetch_assoc($result_prarup_13)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_13['division_id'].'"';
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
								<td>'.$row_prarup_13['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_13['sno'].'" onClick="return confirm(\'Are you sure ?\');" ><i class="far fa-trash-alt"></i></a></td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_13['sno'].'" target="_blank"><i class="far fa-edit"></i></a></td>
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