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
$_POST['prarup_new_5_id']="1";
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
	$_POST['prarup_new_5_id'] = mysqli_num_rows($result_rcpt);
	$i=1;
	while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
		// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_new_5_id'].'<br/>';
		
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

	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center bg-danger text-white p-2">जी०एस०टी० टी०डी०एस० का विवरण वर्ष 2023-24</h4></br>
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
						<p style="font-size: 14px; margin-left:10px; margin-bottom:-8px; "><b>Note:</b><span style="color: red;font-size:12px;"> धनराशि रुपए मे भरे जाएंगे </span></p>
						<?php
						// echo '<h1>'.$_POST['prarup_new_6_id'].'</h1>';
						for($i=1;$i<= $_POST['prarup_new_5_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-2">
								<label >माह</label>
								<select name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>">
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
							<div class="col-md-4">
								<label>देय जी०एस०टी० टी०डी०एस० का प्रारम्भिक अवशेष 01.04.2023</label>
								<input type="text" class="form-control" onInput="addCalc()" name="avsheshdate_<?php echo $i; ?>" id="avsheshdate_<?php echo $i; ?>" value="<?php echo $_POST['avsheshdate_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में किया गया कुल भुगतान</label>
								<input type="text" class="form-control" name="totelpayment_<?php echo $i; ?>" id="totelpayment_<?php echo $i; ?>" value="<?php echo $_POST['totelpayment_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>भुगतान के सापेक्ष काटी गई जी०एस०टी० टी०डी०एस० की राशि</label>
								<input type="text" class="form-control" name="gsttdsbhuktan_<?php echo $i; ?>" id="gsttdsbhuktan_<?php echo $i; ?>" value="<?php echo $_POST['gsttdsbhuktan_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>मुख्यालय को प्रेषित जी०एस०टी० टी०डी०एस० की राशि</label>
								<input type="text" class="form-control" name="gsttdsrashi_<?php echo $i; ?>" id="gsttdsrashi_<?php echo $i; ?>" value="<?php echo $_POST['gsttdsrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>यू०टी०आर०नम्बर एवं दिनांक</label>
								<input type="text" class="form-control" name="utrdate_<?php echo $i; ?>" id="utrdate_<?php echo $i; ?>" value="<?php echo $_POST['utrdate_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>भुगतान के सापेक्ष काटी गई जी०एस०टी० टी०डी०एस० की क्रमिक राशि</label>
								<input type="text" class="form-control" onInput="addCalc()" name="gsttdssno_<?php echo $i; ?>" id="gsttdssno_<?php echo $i; ?>" value="<?php echo $_POST['gsttdssno_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष</label>
								<input type="text" class="form-control" onInput="addCalc()" name="avshesh_<?php echo $i; ?>" id="avshesh_<?php echo $i; ?>" value="<?php echo $_POST['avshesh_'.$i]; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_new_5_add_button" class="btn btn-info pull-right" onClick="prarup_new_5_add_rows()">Add</button>
							</div>
							
						</div>
						<?php
						}
						?>
						<input type="hidden" name="prarup_new_5_id" id="prarup_new_5_id" value="1">
						<div id="prarup_new_5_insert"></div>
					</div>
					
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<!--<button type="submit" name="submit" class="btn btn-success">Submit</button> -->
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								<?php 
									if (isset($_GET['edit_sno']) && $_GET['edit_sno'] != "") {
										echo'<button type="submit" name="submit" class="btn btn-success">Update</button>
										<a href="prarup_new_5.php" class="btn btn-info">Back</a>';
									}else{
										echo '<button type="submit" name="submit" class="btn btn-success">Submit</button>';
										$_GET['edit_sno']="";
									}	
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<script>
		function addCalc(line_serial) {
			var id = parseFloat($("#prarup_new_5_id").val());

			for (let i = 1; i <= id; i++) {
				let avsheshdate = parseFloat(document.getElementById("avsheshdate_" + i).value);
				if (!avsheshdate) {
					avsheshdate = 0;
				}

				let gsttdssno = parseFloat(document.getElementById("gsttdssno_" + i).value);
				if (!gsttdssno) {
					gsttdssno = 0;
				}
				
				var ans=avsheshdate+gsttdssno;
				document.getElementById("avshesh_"+i).value=ans;
				// console.log(ans);
			}
		}

	function prarup_new_5_add_rows(){
       
		var id = parseFloat($("#prarup_new_5_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_new_5_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-2"><label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control"  value="" tabindex=""><option value="Select">Select--	</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div><div class="col-md-4"><label>देय जी०एस०टी० टी०डी०एस० का प्रारम्भिक अवशेष 01.04.2023</label><input type="text" class="form-control" name="avsheshdate_'+id+'" id="avsheshdate_'+id+'" value="" ></div><div class="col-md-3"><label>माह में किया गया कुल भुगतान</label><input type="text" class="form-control" name="totelpayment_'+id+'" id="totelpayment_'+id+'" value="" ></div><div class="col-md-3"><label>भुगतान के सापेक्ष काटी गई जी०एस०टी० टी०डी०एस० की राशि</label><input type="text" class="form-control" name="gsttdsbhuktan_'+id+'" id="gsttdsbhuktan_'+id+'" value="" ></div><div class="col-md-3"><label>मुख्यालय को प्रेषित जी०एस०टी० टी०डी०एस० की राशि</label><input type="text" class="form-control" name="gsttdsrashi_'+id+'" id="gsttdsrashi_'+id+'" value="" ></div><div class="col-md-3"><label>यू०टी०आर०नम्बर एवं दिनांक</label><input type="text" class="form-control" name="utrdate_'+id+'" id="utrdate_'+id+'" value="" ></div><div class="col-md-3"><label>भुगतान के सापेक्ष काटी गई जी०एस०टी० टी०डी०एस० की क्रमिक राशि</label><input type="text" class="form-control" name="gsttdssno_'+id+'" id="gsttdssno_'+id+'" value="" ></div><div class="col-md-3"><label>अवशेष</label><input type="text" class="form-control" name="avshesh_'+id+'" id="avshesh_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_new_5_add_button" class="btn btn-info pull-right" onClick="prarup_new_5_add_rows()">Add</button></div></div>';
            $("#prarup_new_5_insert").append(txt);
            $("#prarup_new_5_id").val(id);
	}
	</script>

	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">जी०एस०टी० टी०डी०एस० का विवरण वर्ष 2023-24 </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>Delete</th>
									<th>View & Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if($_SESSION['usertype']=="sadmin"){
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_5`ORDER BY division_id';
							}else{
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_5` where invoice_prarup_new_5.created_by = "'.$_SESSION['username'].'"';
							}
							
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
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_5['sno'].'" onClick="return confirm(\'Are you sure ?\');"><i class="far fa-trash-alt"></i></a></td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_new_5['sno'].'" onClick="return confirm(\'Are you sure ?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit " ></span></a></td>
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