<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){ 
		if($_POST['project_id_1']!=""){
			$sql='insert into invoice_prarup_new_4 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_new_4_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_new_4_id']; $i++){
					if($_POST['project_id_'.$i]!=""){
						$sql = 'insert into trans_prarup_new_4 (`invoice_id`, `project_id`, `praptdhanrashi`, `vyadhanrashi`, `avsheshdhanrashi`, `bankdebitdate`, `utrnum`, `agrimsamayojan`, `ayakarsamayojan`, `gsttds`, `totel`, `condition`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['project_id_'.$i].'", "'.$_POST['praptdhanrashi_'.$i].'", "'.$_POST['vyadhanrashi_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_POST['bankdebitdate_'.$i].'", "'.$_POST['utrnum_'.$i].'", "'.$_POST['agrimsamayojan_'.$i].'", "'.$_POST['ayakarsamayojan_'.$i].'", "'.$_POST['gsttds_'.$i].'", "'.$_POST['totel_'.$i].'", "'.$_POST['condition_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
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
		$sql = 'update invoice_prarup_new_4 set 
			`total_trans`= "'.$_POST['prarup_new_4_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("Y-m-d H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_new_4 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1; $i<=$_POST['prarup_new_4_id']; $i++){
					if($_POST['project_id_'.$i]!=""){
						$sql = 'insert into trans_prarup_new_4 (`invoice_id`, `project_id`, `praptdhanrashi`, `vyadhanrashi`, `avsheshdhanrashi`, `bankdebitdate`, `utrnum`, `agrimsamayojan`, `ayakarsamayojan`, `gsttds`, `totel`, `condition`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['project_id_'.$i].'", "'.$_POST['praptdhanrashi_'.$i].'", "'.$_POST['vyadhanrashi_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_POST['bankdebitdate_'.$i].'", "'.$_POST['utrnum_'.$i].'", "'.$_POST['agrimsamayojan_'.$i].'", "'.$_POST['ayakarsamayojan_'.$i].'", "'.$_POST['gsttds_'.$i].'", "'.$_POST['totel_'.$i].'", "'.$_POST['condition_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
	$_POST['project_id_1']="";
	$_POST['praptdhanrashi_1']="";
	$_POST['vyadhanrashi_1']="";
	$_POST['avsheshdhanrashi_1']="";
	$_POST['bankdebitdate_1']="";
	$_POST['utrnum_1']="";
	$_POST['agrimsamayojan_1']="";
	$_POST['ayakarsamayojan_1']="";
	$_POST['gsttds_1']="";
	$_POST['totel_1']="";
	$_POST['condition_1']="";
	$_POST['edit_sno']="";
	$_POST['prarup_new_4_id']="1";
}


	if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_new_4 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_new_4 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}	
	if(isset($_GET['edit_sno'])){
		
			$sql = 'select * from trans_prarup_new_4 where invoice_id="'.$_GET['edit_sno'].'"';
			// echo $sql.'<br>';
			$result_rcpt = execute_query($sql);
			if(mysqli_num_rows($result_rcpt)!=0){
				$_POST['prarup_new_4_id'] = mysqli_num_rows($result_rcpt);
				$i=1;
				while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
					// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_new_4_id'].'<br/>';
					
					$_POST['project_id_'.$i] = $row_rcpt['project_id'];
					$_POST['praptdhanrashi_'.$i]= $row_rcpt['praptdhanrashi'];
					$_POST['vyadhanrashi_'.$i]= $row_rcpt['vyadhanrashi'];
					$_POST['avsheshdhanrashi_'.$i]= $row_rcpt['avsheshdhanrashi'];
					$_POST['bankdebitdate_'.$i]= $row_rcpt['bankdebitdate'];
					$_POST['utrnum_'.$i]= $row_rcpt['utrnum'];
					$_POST['agrimsamayojan_'.$i]= $row_rcpt['agrimsamayojan'];
					$_POST['ayakarsamayojan_'.$i]= $row_rcpt['ayakarsamayojan'];
					$_POST['gsttds_'.$i]= $row_rcpt['gsttds'];
					$_POST['totel_'.$i]= $row_rcpt['totel'];
					$_POST['condition_'.$i]= $row_rcpt['condition'];
					
					$i++;
				}
			}
			else{
				$_POST['project_id_1']='';
				$_POST['praptdhanrashi_1']="";
				$_POST['vyadhanrashi_1']="";
				$_POST['avsheshdhanrashi_1']="";
				$_POST['bankdebitdate_1']="";
				$_POST['utrnum_1']="";
				$_POST['agrimsamayojan_1']="";
				$_POST['ayakarsamayojan_1']="";
				$_POST['gsttds_1']="";
				$_POST['totel_1']="";
				$_POST['condition_1']="";
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
						<h4 class="card-title text-center bg-danger text-white p-2">निर्माण कार्य की अवशेष धनराशि जो मुख्यालय को प्रेषित की गई विवरण वर्ष 2023-24 </h4></br>
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
						// echo '<h1>'.$_POST['add_rows_id_revised_estimate'].'</h1>';
						for($i=1;$i<= $_POST['prarup_new_4_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="" >
							<div class="col-md-3">
								<label>परियोजना  का नाम</label>
								<select class="form-control" name="project_id_<?php echo $i; ?>" id="project_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value), fill_sub_department(this.value)">
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . ') and status!="5" )';
										// echo $query;
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_id_'.$i])){
												if($_POST['project_id_'.$i]==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}
									?>
								</select>
							</div>
							<div class="col-md-3">
								<label>परियोजना पर प्राप्त धनराशि</label>
								<input type="text" class="form-control"  name="praptdhanrashi_<?php echo $i; ?>" id="praptdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['praptdhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>परियोजना पर व्यय धनराशि</label>
								<input type="text" class="form-control"  name="vyadhanrashi_<?php echo $i; ?>" id="vyadhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['vyadhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष धनराशि</label>
								<input type="text" class="form-control" onInput="addCalc1()" name="avsheshdhanrashi_<?php echo $i; ?>" id="avsheshdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['avsheshdhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>बैंक से डेबिट होने का दिनांक</label>
								<input type="date" class="form-control"  name="bankdebitdate_<?php echo $i; ?>" id="bankdebitdate_<?php echo $i; ?>" value="<?php echo $_POST['bankdebitdate_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>यू०टी०आर० नम्बर</label>
								<input type="text" class="form-control"  name="utrnum_<?php echo $i; ?>" id="utrnum_<?php echo $i; ?>" value="<?php echo $_POST['utrnum_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अग्रिम सेंटेज धनराशि (समायोजन)</label>
								<input type="text" class="form-control" onInput="addCalc1()" name="agrimsamayojan_<?php echo $i; ?>" id="agrimsamayojan_<?php echo $i; ?>" value="<?php echo $_POST['agrimsamayojan_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>आयकर (समायोजन)</label>
								<input type="text" class="form-control"  name="ayakarsamayojan_<?php echo $i; ?>" id="ayakarsamayojan_<?php echo $i; ?>" value="<?php echo $_POST['ayakarsamayojan_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>जी०एस०टी० टी०डी०एस० (समायोजन)</label>
								<input type="text" class="form-control"  name="gsttds_<?php echo $i; ?>" id="gsttds_<?php echo $i; ?>" value="<?php echo $_POST['gsttds_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>कुल धनराशि</label>
								<input type="text" class="form-control" onInput="addCalc1()"  name="totel_<?php echo $i; ?>" id="totel_<?php echo $i; ?>" value="<?php echo $_POST['totel_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>परियोजना की स्थिति</label>
								<input type="text" class="form-control"  name="condition_<?php echo $i; ?>" id="condition_<?php echo $i; ?>" value="<?php echo $_POST['condition_'.$i]; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_new_4_add_button" class="btn btn-info pull-right" onClick="prarup_new_4_add_rows()">Add</button>
								
							</div>
							
						</div>
						<?php } ?>
						<input type="hidden" name="prarup_new_4_id" id="prarup_new_4_id" value="<?php echo $_POST['prarup_new_4_id']; ?>">
						<div id="prarup_new_4_insert"></div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<!--<button type="submit" name="submit" class="btn btn-success">Submit</button> -->
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								<?php 
									if (isset($_GET['edit_sno']) && $_GET['edit_sno'] != "") {
										echo'<button type="submit" name="submit" class="btn btn-success">Update</button>
										<a href="prarup_new_4.php" class="btn btn-info">Back</a>';
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
	function addCalc1(line_serial) {
		var id = parseFloat($("#prarup_new_4_id").val());

		for (let i = 1; i <= id; i++) {
			let avsheshdhanrashi = parseFloat(document.getElementById("avsheshdhanrashi_" + i).value);
			if (!avsheshdhanrashi) {
				avsheshdhanrashi = 0;
			}

			let agrimsamayojan = parseFloat(document.getElementById("agrimsamayojan_" + i).value);
			if (!agrimsamayojan) {
				agrimsamayojan = 0;
			}
			
			
			var ans=avsheshdhanrashi+agrimsamayojan;
			document.getElementById("totel_"+i).value=ans;
			console.log(ans);
		}
	}

	function prarup_new_4_add_rows(){
       
		var id = parseFloat($("#prarup_new_4_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_new_4_add_button").remove();
		var txt ='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>परियोजना का नाम</label><select class="form-control" name="project_id_'+id+'" id="project_id_'+id+'" ><option value="">--- Select--</option>';
		<?php
		$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
		$run = mysqli_query($db,$query);
		while($data = mysqli_fetch_array($run)){
			echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label>परियोजना पर प्राप्त धनराशि</label><input type="text" class="form-control" name="praptdhanrashi_'+id+'" id="praptdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>परियोजना पर व्यय धनराशि</label><input type="text" class="form-control" name="vyadhanrashi_'+id+'" id="vyadhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>अवशेष धनराशि</label><input type="text" class="form-control" name="avsheshdhanrashi_'+id+'" id="avsheshdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>बैंक से डेबिट होने का दिनांक</label><input type="date" class="form-control" name="bankdebitdate_'+id+'" id="bankdebitdate_'+id+'" value="" ></div><div class="col-md-3"><label>यू०टी०आर० नम्बर</label><input type="text" class="form-control" name="utrnum_'+id+'" id="utrnum_'+id+'" value="" ></div><div class="col-md-3"><label>अग्रिम सेंटेज धनराशि (समायोजन)</label><input type="text" class="form-control" name="agrimsamayojan_'+id+'" id="agrimsamayojan_'+id+'" value="" ></div><div class="col-md-3"><label>आयकर (समायोजन)</label><input type="text" class="form-control" name="ayakarsamayojan_'+id+'" id="ayakarsamayojan_'+id+'" value="" ></div><div class="col-md-3"><label>जी०एस०टी० टी०डी०एस० (समायोजन)</label><input type="text" class="form-control" name="gsttds_'+id+'" id="gsttds_'+id+'" value="" ></div><div class="col-md-3"><label>कुल धनराशि</label><input type="text" class="form-control" name="totel_'+id+'" id="totel_'+id+'" value="" ></div><div class="col-md-3"><label>परियोजना की स्थिति</label><input type="text" class="form-control" name="condition_'+id+'" id="condition_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_new_4_add_button" class="btn btn-info pull-right" onClick="prarup_new_4_add_rows()">Add</button></div></div>';
            $("#prarup_new_4_insert").append(txt);
            $("#prarup_new_4_id").val(id);
	}
	</script>
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">निर्माण कार्य की अवशेष धनराशि जो मुख्यालय को प्रेषित की गई विवरण वर्ष 2023-24 </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover justify-content-center">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>delete</th>
									<th>View & Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if($_SESSION['usertype']=="sadmin"){
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_4` ORDER BY division_id';
							}else{
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_4` where invoice_prarup_new_4.created_by = "'.$_SESSION['username'].'"';
							}
							
							$result_prarup_new_4 = execute_query($sql);
							$i=1;
							while($row_prarup_new_4 = mysqli_fetch_assoc($result_prarup_new_4)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_4['division_id'].'"';
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
								<td>'.$row_prarup_new_4['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_4['sno'].'" ><i class="far fa-trash-alt"></i></a></td>
								
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_new_4['sno'].'" ><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip"></span></a></td>
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


	function addCalc(line_serial){
		var id = parseFloat($("#prarup_new_4_id").val());
		var tot=0;
		for(i=1; i<=id; i++){
			var amt = $('#p_receive_amount_'+i).val();
			amt = parseFloat(amt);
			if(!amt){
				amt = 0;
			}
			tot += amt;
			console.log("AMT : "+amt+" : ID: "+i);
		}
		$("#tot_receive_amount").val(tot);
		
		
	}

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>