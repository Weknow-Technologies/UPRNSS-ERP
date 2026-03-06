<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){ 
		if($_POST['project_name_1']!=""){
			$sql='insert into invoice_prarup_8 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("d-m-Y").'", "'.$_POST['prarup_8_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_8_id']; $i++){
					if($_POST['vibhagname_'.$i]!=""){
						$sql = 'insert into trans_prarup_8 (`invoice_id`, `year`, `vibhagname`, `project_name`, `grahakgstnum`, `centagegsttds`, `prakhandgsttdsdhanrashi`, `mukhyalayaprakhandgsttdsdhanrashi`, `difference`, `projectstatus`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['year_'.$i].'", "'.$_POST['vibhagname_'.$i].'", "'.$_POST['project_name_'.$i].'", "'.$_POST['grahakgstnum_'.$i].'", "'.$_POST['centagegsttds_'.$i].'", "'.$_POST['prakhandgsttdsdhanrashi_'.$i].'", "'.$_POST['mukhyalayaprakhandgsttdsdhanrashi_'.$i].'", "'.$_POST['difference_'.$i].'", "'.$_POST['projectstatus_'.$i].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}
				if($msg==""){
					$msg="<p class='alert alert-success'>Data stored all</p>";
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
		$sql = 'update invoice_prarup_8 set 
			`total_trans`= "'.$_POST['prarup_8_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("d-m-Y H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_8 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1; $i<=$_POST['prarup_8_id']; $i++){
					if($_POST['vibhagname_'.$i]!=""){
						$sql = 'insert into trans_prarup_8 (`invoice_id`, `year`, `vibhagname`, `project_name`, `grahakgstnum`, `centagegsttds`, `prakhandgsttdsdhanrashi`, `mukhyalayaprakhandgsttdsdhanrashi`, `difference`, `projectstatus`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['year_'.$i].'", "'.$_POST['vibhagname_'.$i].'", "'.$_POST['project_name_'.$i].'", "'.$_POST['grahakgstnum_'.$i].'", "'.$_POST['centagegsttds_'.$i].'", "'.$_POST['prakhandgsttdsdhanrashi_'.$i].'", "'.$_POST['mukhyalayaprakhandgsttdsdhanrashi_'.$i].'", "'.$_POST['difference_'.$i].'", "'.$_POST['projectstatus_'.$i].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
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
	
	$_POST['year_1']="";
	$_POST['vibhagname_1']="";
	$_POST['project_name_1']="";
	$_POST['grahakgstnum_1']="";
	$_POST['centagegsttds_1']="";
	$_POST['prakhandgsttdsdhanrashi_1']="";
	$_POST['mukhyalayaprakhandgsttdsdhanrashi_1']="";
	$_POST['difference_1']="";
	$_POST['projectstatus_1']="";
	
	$_POST['edit_sno']="";
	$_POST['prarup_8_id']="1";
}



	if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_8 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_8 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}	
	if(isset($_GET['edit_sno'])){
		
			$sql = 'select * from trans_prarup_8 where invoice_id="'.$_GET['edit_sno'].'"';
			// echo $sql.'<br>';
			$result_rcpt = execute_query($sql);
			if(mysqli_num_rows($result_rcpt)!=0){
				$_POST['prarup_8_id'] = mysqli_num_rows($result_rcpt);
				$i=1;
				while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
					// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_8_id'].'<br/>';
					$_POST['year_'.$i] = $row_rcpt['year'];
					$_POST['vibhagname_'.$i]= $row_rcpt['vibhagname'];
					$_POST['project_name_'.$i]= $row_rcpt['project_name'];
					$_POST['grahakgstnum_'.$i]= $row_rcpt['grahakgstnum'];
					$_POST['centagegsttds_'.$i]= $row_rcpt['centagegsttds'];
					$_POST['prakhandgsttdsdhanrashi_'.$i]= $row_rcpt['prakhandgsttdsdhanrashi'];
					$_POST['mukhyalayaprakhandgsttdsdhanrashi_'.$i]= $row_rcpt['mukhyalayaprakhandgsttdsdhanrashi'];
					$_POST['difference_'.$i]= $row_rcpt['difference'];
					$_POST['projectstatus_'.$i]= $row_rcpt['projectstatus'];
					
					$i++;
				}
			}
			else{
				$_POST['year_1']="";
				$_POST['vibhagname_1']="";
				$_POST['project_name_1']="";
				$_POST['grahakgstnum_1']="";
				$_POST['centagegsttds_1']="";
				$_POST['prakhandgsttdsdhanrashi_1']="";
				$_POST['mukhyalayaprakhandgsttdsdhanrashi_1']="";
				$_POST['difference_1']="";
				$_POST['projectstatus_1']="";
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
						<h4 class="card-title text-center">प्रखण्डों द्वारा अभिलेखों में दर्शित / मॉग की जा रही ग्राहक विभाग द्वारा कटौती की गई आयकर की धनराशि का विवरण </h4></br>
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
						// echo '<h1>'.$_POST['prarup_8_id'].'</h1>';
						for($i=1;$i<= $_POST['prarup_8_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-3">
								<label>वित्तीय वर्ष </label>
								<select class="form-control" id="year_<?php echo $i; ?>" name="year_<?php echo $i; ?>">
									<?php
									// Start from the year 2000 and go up to 20 years in the future
									$startYear = 2000;
									$endYear = $startYear + 40;

									$currentYear = date('Y');

									// for ($year = $startYear; $year <= $endYear; $year++) {
										// // $selected = ($year == $currentYear) ? 'selected' : ;
										// $selected="";
										// if(isset($_POST['year_'.$i]) && $_POST['year_'.$i] == "$year-" . ($year + 1)){
											// $selected="selected";
										// }else{
											// $selected = ($year == $currentYear) ? 'selected' :'' ;
										// }
										// echo "<option value=\"$year-" . ($year + 1) . "\" $selected>$year-" . ($year + 1) . "</option>";
									// }
									
									for ($year = $startYear; $year <= $endYear; $year++) {
											// Check if the current year matches the value in $_POST
											$selected = (isset($_POST['year_'.$i]) && $_POST['year_'.$i] == "$year-" . ($year + 1)) ? 'selected' : '';

											echo "<option value=\"$year-" . ($year + 1) . "\" $selected>$year-" . ($year + 1) . "</option>";
										}

										
									?>
								</select>
							</div>
							<div class="col-md-3"><label>विभाग का नाम </label>
									<select class="form-control" name="vibhagname_<?php echo $i; ?>" id="vibhagname_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, department_name_hindi from uprnss_department_name)';
										// echo $query;
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['vibhagname_'.$i])){
												if($_POST['vibhagname_'.$i]==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['department_name_hindi']).'</option>';
										}
									?>
								</select>
							</div>
							<div class="col-md-3">
								<label>परियोजना का नाम </label>
								<select class="form-control" name="project_name_<?php echo $i; ?>" id="project_name_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" >
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
										// echo $query;
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_name_'.$i])){
												if($_POST['project_name_'.$i]==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}
									?>
								</select>
							</div>
							<div class="col-md-3">
								<label>ग्राहक विभाग का टी०ए०न० नम्बर</label>
								<input type="text" class="form-control" name="grahakgstnum_<?php echo $i; ?>" id="grahakgstnum_<?php echo $i; ?>" value="<?php echo $_POST['grahakgstnum_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>मुख्यालय स्तर पर प्राप्त धनराशि पर टी०डी०एस० की धनराशि</label>
								<input type="text" class="form-control" name="centagegsttds_<?php echo $i; ?>" id="centagegsttds_<?php echo $i; ?>" value="<?php echo $_POST['centagegsttds_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>प्रखण्ड पर प्राप्त धनराशि पर टी०डी०एस० की धनराशि  </label>
								<input type="text" class="form-control" name="prakhandgsttdsdhanrashi_<?php echo $i; ?>" id="prakhandgsttdsdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['prakhandgsttdsdhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>मुख्यालय द्वारा प्रखण्ड को प्रेषित टी०डी०एस० की धनराशि</label>
								<input type="text" class="form-control" name="mukhyalayaprakhandgsttdsdhanrashi_<?php echo $i; ?>" id="mukhyalayaprakhandgsttdsdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['mukhyalayaprakhandgsttdsdhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अंतर</label>
								<input type="text" class="form-control" name="difference_<?php echo $i; ?>" id="difference_<?php echo $i; ?>" value="<?php echo $_POST['difference_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>परियोजना  पूर्ण /अपूर्ण </label>
								<input type="text" class="form-control" name="projectstatus_<?php echo $i; ?>" id="projectstatus_<?php echo $i; ?>" value="<?php echo $_POST['projectstatus_'.$i]; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_8_add_button" class="btn btn-info pull-right" onClick="prarup_8_add_rows()">Add</button>
								
							</div>
								
						</div>
						<?php } ?>
							<input type="hidden" name="prarup_8_id" id="prarup_8_id" value="<?php echo $_POST['prarup_8_id']; ?>">
							<div id="prarup_8_insert"></div>
					</div>
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
	</form>
	<script>
	function prarup_8_add_rows(){
       
		var id = parseFloat($("#prarup_8_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_8_add_button").remove();
		var txt = '<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>वित्तीय वर्ष </label><select class="form-control" name="year_' + id + '" id="year_' + id + '" >';
		<?php
		$startYear = 2000;
		$endYear = $startYear + 40;
		$currentYear = date('Y');
		for ($year = $startYear; $year <= $endYear; $year++) {
			echo 'txt += "<option value=\"'.$year.'-' . ($year + 1) . '\" ' . $selected . '>'.$year.'-' . ($year + 1) . '</option>";' . "\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label>विभाग का नाम </label><select class="form-control" name="vibhagname_'+id+'" id="vibhagname_'+id+'" ><option value="">--- Select--</option>';
		<?php
		$query = '(SELECT sno, department_name_hindi from uprnss_department_name )';
		$run = mysqli_query($db,$query);
		while($data = mysqli_fetch_array($run)){
			echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['department_name_hindi'].'</option>";'."\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label>परियोजना का नाम </label><select class="form-control" name="project_name_'+id+'" id="project_name_'+id+'" ><option value="">--- Select--</option>';
		<?php
		$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
		$run = mysqli_query($db,$query);
		while($data = mysqli_fetch_array($run)){
			echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label>ग्राहक विभाग का जी०एस०टिन नम्बर</label><input type="text" class="form-control" name="grahakgstnum_'+id+'" id="grahakgstnum_'+id+'" value="" ></div><div class="col-md-3"><label>मुख्यालय पर प्राप्त धनराशि पर टी०डी०एस० की धनराशि</label><input type="text" class="form-control" name="centagegsttds_'+id+'" id="centagegsttds_'+id+'" value="" ></div><div class="col-md-3"><label>प्रखण्ड पर प्राप्त धनराशि पर टी०डी०एस० की धनराशि  </label><input type="text" class="form-control" name="prakhandgsttdsdhanrashi_'+id+'" id="prakhandgsttdsdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>मुख्यालय द्वारा प्रखण्ड को प्रेषित टी०डी०एस० की धनराशि</label><input type="text" class="form-control" name="mukhyalayaprakhandgsttdsdhanrashi_'+id+'" id="mukhyalayaprakhandgsttdsdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>अंतर</label><input type="text" class="form-control" name="difference_'+id+'" id="difference_'+id+'" value="" ></div><div class="col-md-3"><label>परियोजना  पूर्ण /अपूर्ण </label><input type="text" class="form-control" name="projectstatus_'+id+'" id="projectstatus_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_8_add_button" class="btn btn-info pull-right" onClick="prarup_8_add_rows()">Add</button></div></div>';
            $("#prarup_8_insert").append(txt);
            $("#prarup_8_id").val(id);
	}
	</script>
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्डों द्वारा अभिलेखों में दर्शित / मॉग की जा रही ग्राहक विभाग द्वारा कटौती की गई आयकर की धनराशि का विवरण</h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>Delete</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_8` where invoice_prarup_8.created_by = "'.$_SESSION['username'].'"';
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
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_new_4['sno'].'" target="_blank"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit " ></span>
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