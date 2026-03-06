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
			$sql='insert into invoice_prarup_1 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_1_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");
			';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_1_id']; $i++){
					if($_POST['project_id_'.$i]!=""){
						$sql = 'insert into trans_prarup_1 (`invoice_id`, `project_id`,`month`, `prambhik_dey`, `monthduemukhyalaya`, `monthdueprakhand`, `monthduemukhyalayasentage`, `monthdueprakhandsentage`, `monthsentageutr`, `khamikdeymukhyalaya`, `khamikdeyprakhand`, `khamikpreshitmukhyalaya`, `khamikpreshitprakhand`, `remaindey`, `remainreason`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['project_id_'.$i].'", "'.$_POST['month_'.$i].'", "'.$_POST['prambhik_dey_'.$i].'", "'.$_POST['monthduemukhyalaya_'.$i].'", "'.$_POST['monthdueprakhand_'.$i].'", "'.$_POST['monthduemukhyalayasentage_'.$i].'", "'.$_POST['monthdueprakhandsentage_'.$i].'", "'.$_POST['monthsentageutr_'.$i].'", "'.$_POST['khamikdeymukhyalaya_'.$i].'", "'.$_POST['khamikdeyprakhand_'.$i].'", "'.$_POST['khamikpreshitmukhyalaya_'.$i].'", "'.$_POST['khamikpreshitprakhand_'.$i].'", "'.$_POST['remaindey_'.$i].'", "'.$_POST['remainreason_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';

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
		$sql = 'update invoice_prarup_1 set 
			`total_trans`= "'.$_POST['prarup_1_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("Y-m-d H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_1 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1; $i<=$_POST['prarup_1_id']; $i++){
					if($_POST['project_id_'.$i]!=""){
						$sql = 'insert into trans_prarup_1 (`invoice_id`, `project_id`,`month`, `prambhik_dey`, `monthduemukhyalaya`, `monthdueprakhand`, `monthduemukhyalayasentage`, `monthdueprakhandsentage`, `monthsentageutr`, `khamikdeymukhyalaya`, `khamikdeyprakhand`, `khamikpreshitmukhyalaya`, `khamikpreshitprakhand`, `remaindey`, `remainreason`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['project_id_'.$i].'", "'.$_POST['month_'.$i].'", "'.$_POST['prambhik_dey_'.$i].'", "'.$_POST['monthduemukhyalaya_'.$i].'", "'.$_POST['monthdueprakhand_'.$i].'", "'.$_POST['monthduemukhyalayasentage_'.$i].'", "'.$_POST['monthdueprakhandsentage_'.$i].'", "'.$_POST['monthsentageutr_'.$i].'", "'.$_POST['khamikdeymukhyalaya_'.$i].'", "'.$_POST['khamikdeyprakhand_'.$i].'", "'.$_POST['khamikpreshitmukhyalaya_'.$i].'", "'.$_POST['khamikpreshitprakhand_'.$i].'", "'.$_POST['remaindey_'.$i].'", "'.$_POST['remainreason_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
	$_POST['prarup_1_id']="1";
	$_POST['edit_sno']="";
	
	$_POST['project_id_1']= "";
	$_POST['month_1']="";
	$_POST['prambhik_dey_1']= "";
	$_POST['monthduemukhyalaya_1']= "";
	$_POST['monthdueprakhand_1']= "";
	$_POST['monthduemukhyalayasentage_1']= "";
	$_POST['monthdueprakhandsentage_1']= "";
	$_POST['monthsentageutr_1']= "";
	$_POST['khamikdeymukhyalaya_1']= "";
	$_POST['khamikdeyprakhand_1']= "";
	$_POST['khamikpreshitmukhyalaya_1']= "";
	$_POST['khamikpreshitprakhand_1']= "";
	$_POST['remaindey_1']= "";
	$_POST['remainreason_1']= "";
	
}
	if(isset($_GET['edit_sno'])){
		$sql = 'select * from trans_prarup_1 where invoice_id="'.$_GET['edit_sno'].'"';
		// echo $sql.'<br>';
		$result_rcpt = execute_query($sql);
		if(mysqli_num_rows($result_rcpt)!=0){
			$_POST['prarup_1_id'] = mysqli_num_rows($result_rcpt);
			$i=1;
			while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
				// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_new_4_id'].'<br/>';
				
				$_POST['project_id_'.$i]= $row_rcpt['project_id'];
				$_POST['month_'.$i]= $row_rcpt['month'];
				$_POST['prambhik_dey_'.$i]= $row_rcpt['prambhik_dey'];
				$_POST['monthduemukhyalaya_'.$i]= $row_rcpt['monthduemukhyalaya'];
				$_POST['monthdueprakhand_'.$i]= $row_rcpt['monthdueprakhand'];
				$_POST['monthduemukhyalayasentage_'.$i]= $row_rcpt['monthduemukhyalayasentage'];
				$_POST['monthdueprakhandsentage_'.$i]= $row_rcpt['monthdueprakhandsentage'];
				$_POST['monthsentageutr_'.$i]= $row_rcpt['monthsentageutr'];
				$_POST['khamikdeymukhyalaya_'.$i]= $row_rcpt['khamikdeymukhyalaya'];
				$_POST['khamikdeyprakhand_'.$i]= $row_rcpt['khamikdeyprakhand'];
				$_POST['khamikpreshitmukhyalaya_'.$i]= $row_rcpt['khamikpreshitmukhyalaya'];
				$_POST['khamikpreshitprakhand_'.$i]= $row_rcpt['khamikpreshitprakhand'];
				$_POST['remaindey_'.$i]= $row_rcpt['remaindey'];
				$_POST['remainreason_'.$i]= $row_rcpt['remainreason'];
				
				
				$i++;
			}
		}
		else{
			$_POST['project_id_1']= "";

			$_POST['month_1']="";
			$_POST['prambhik_dey_1']= "";
			$_POST['monthduemukhyalaya_1']= "";
			$_POST['monthdueprakhand_1']= "";
			$_POST['monthduemukhyalayasentage_1']= "";
			$_POST['monthdueprakhandsentage_1']= "";
			$_POST['monthsentageutr_1']= "";
			$_POST['khamikdeymukhyalaya_1']= "";
			$_POST['khamikdeyprakhand_1']= "";
			$_POST['khamikpreshitmukhyalaya_1']= "";
			$_POST['khamikpreshitprakhand_1']= "";
			$_POST['remaindey_1']= "";
			$_POST['remainreason_1']= "";
		}
		$_POST['edit_sno']=$_GET['edit_sno'];
	}

if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_1 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_1 where invoice_id="'.$_GET['delid'].'"';
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
						<h4 class="card-title text-center">मुख्यालय  को सेंटेज प्रेषण का विवरण वर्ष 2023-24</h4></br>
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
							for($i=1;$i<= $_POST['prarup_1_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-3">
								<label>विभाग का नाम/परियोजना का नाम </label>
								<select class="form-control" name="project_id_<?php echo $i; ?>" id="project_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value), fill_sub_department(this.value)">
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
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
								<label >माह</label>
								<select name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control"  value="<?php echo $editrow['month_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
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
								<label>प्रारम्भिक अवशेष 01.04. 2023</label><input onInput="addCalc()" type="text" class="form-control" name="prambhik_dey_<?php echo $i; ?>" id="prambhik_dey_<?php echo $i; ?>" value="<?php echo $_POST['prambhik_dey_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में देय सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष</label>
								<input  type="text" class="form-control" name="monthduemukhyalaya_<?php echo $i; ?>" id="monthduemukhyalaya_<?php echo $i; ?>" value="<?php echo $_POST['monthduemukhyalaya_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में देय सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष</label>
								<input type="text" class="form-control" name="monthdueprakhand_<?php echo $i; ?>" id="monthdueprakhand_<?php echo $i; ?>" value="<?php echo $_POST['monthdueprakhand_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में प्रेषित सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष समायोजित सेंटेज </label>
								<input type="text" class="form-control" name="monthduemukhyalayasentage_<?php echo $i; ?>" id="monthduemukhyalayasentage_<?php echo $i; ?>" value="<?php echo $_POST['monthduemukhyalayasentage_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में प्रेषित सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष प्राप्त</label>
								<input type="text" class="form-control" name="monthdueprakhandsentage_<?php echo $i; ?>" id="monthdueprakhandsentage_<?php echo $i; ?>" value="<?php echo $_POST['monthdueprakhandsentage_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह में प्रेषित सेंटेज प्रेषित धनराशि का यू०टी०आर० नम्बर एवं दिनांक</label>
								<input type="text" class="form-control" name="monthsentageutr_<?php echo $i; ?>" id="monthsentageutr_<?php echo $i; ?>" value="<?php echo $_POST['monthsentageutr_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>क्रमिक देय सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष </label>
								<input onInput="addCalc()" type="text" class="form-control" name="khamikdeymukhyalaya_<?php echo $i; ?>" id="khamikdeymukhyalaya_<?php echo $i; ?>" value="<?php echo $_POST['khamikdeymukhyalaya_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>क्रमिक देय सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष  </label>
								<input onInput="addCalc()" type="text" class="form-control" name="khamikdeyprakhand_<?php echo $i; ?>" id="khamikdeyprakhand_<?php echo $i; ?>" value="<?php echo $_POST['khamikdeyprakhand_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>क्रमिक प्रेषित सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष  </label>
								<input onInput="addCalc()" type="text" class="form-control" name="khamikpreshitmukhyalaya_<?php echo $i; ?>" id="khamikpreshitmukhyalaya_<?php echo $i; ?>" value="<?php echo $_POST['khamikpreshitmukhyalaya_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>क्रमिक प्रेषित सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष  </label>
								<input onInput="addCalc()" type="text" class="form-control" name="khamikpreshitprakhand_<?php echo $i; ?>" id="khamikpreshitprakhand_<?php echo $i; ?>" value="<?php echo $_POST['khamikpreshitprakhand_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष देय सेंटेज</label>
								<input type="text" class="form-control" onInput="addCalc()"  name="remaindey_<?php echo $i; ?>" id="remaindey_<?php echo $i; ?>" value="<?php echo $_POST['remaindey_'.$i]; ?>" readonly>
							</div>
							<div class="col-md-3">
								<label>अवशेष का कारण </label>
								<input type="text" class="form-control" name="remainreason_<?php echo $i; ?>" id="remainreason_<?php echo $i; ?>" value="<?php echo $_POST['remainreason_'.$i]; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
							<button type="button" id="prarup_1_add_button" class="btn btn-info pull-right" onClick="prarup_1_add_rows()">Add</button>
							</div>
							
						</div>
						<?php } ?>
						<div id="prarup_1_insert"></div>
						<input type="hidden" name="prarup_1_id" id="prarup_1_id" value="<?php echo $_POST['prarup_1_id']; ?>">
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success">Submit</button>
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

 
	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">मुख्यालय  को सेंटेज प्रेषण का विवरण वर्ष 2023-24</h4></br>
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
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_1` where invoice_prarup_1.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_2 = execute_query($sql);
							$i=1;
							while($row_prarup_1 = mysqli_fetch_assoc($result_prarup_2)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_1['division_id'].'"';
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
								<td>'.$row_prarup_1['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_1['sno'].'" onClick="return confirm(\'Are you sure ?\');"><i class="far fa-trash-alt"></i></a></td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_1['sno'].'" onClick="return confirm(\'Are you sure ?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit " ></span></a></td>
								</tr>';
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		
	<script>
		function addCalc(line_serial) {
			var id = parseFloat($("#prarup_1_id").val());

			for (let i = 1; i <= id; i++) {
				let prambhik_dey = parseFloat(document.getElementById("prambhik_dey_" + i).value);
				if (!prambhik_dey) {
					prambhik_dey = 0;
				}

				let khamikdeymukhyalaya = parseFloat(document.getElementById("khamikdeymukhyalaya_" + i).value);
				if (!khamikdeymukhyalaya) {
					khamikdeymukhyalaya = 0;
				}

				let khamikdeyprakhand = parseFloat(document.getElementById("khamikdeyprakhand_" + i).value);
				if (!khamikdeyprakhand) {
					khamikdeyprakhand = 0;
				}

				let khamikpreshitmukhyalaya = parseFloat(document.getElementById("khamikpreshitmukhyalaya_" + i).value);
				if (!khamikpreshitmukhyalaya) {
					khamikpreshitmukhyalaya = 0;
				}

				let khamikpreshitprakhand = parseFloat(document.getElementById("khamikpreshitprakhand_" + i).value);
				if (!khamikpreshitprakhand) {
					khamikpreshitprakhand = 0;
				}
				
				var ans=prambhik_dey+khamikdeymukhyalaya+khamikdeyprakhand-khamikpreshitmukhyalaya-khamikpreshitprakhand;
				document.getElementById("remaindey_"+i).value=ans;
				// console.log(ans);
			}
		}

	
	
	
	function prarup_1_add_rows(){
       
		var id = parseFloat($("#prarup_1_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_1_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>विभाग का नाम/परियोजना का नाम </label><select class="form-control" name="project_id_'+id+'" id="project_id_'+id+'" ><option value="">--- Select--</option>';
		<?php
		$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
		$run = mysqli_query($db,$query);
		while($data = mysqli_fetch_array($run)){
			echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control"  value="" tabindex=""><option value="Select">Select--	</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div><div class="col-md-3"><label>प्रारम्भिक अवशेष 01.04. 2023</label><input type="text" class="form-control" onInput="addCalc()"  name="prambhik_dey_'+id+'" id="prambhik_dey_'+id+'" value="" ></div><div class="col-md-3"><label>माह में देय सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष</label><input type="text" class="form-control" name="monthduemukhyalaya_'+id+'" id="monthduemukhyalaya_'+id+'" value="" ></div><div class="col-md-3"><label>माह में देय सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष</label><input type="text" class="form-control" name="monthdueprakhand_'+id+'" id="monthdueprakhand_'+id+'" value="" ></div><div class="col-md-3"><label>माह में प्रेषित सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष समायोजित सेंटेज </label><input type="text" class="form-control" name="monthduemukhyalayasentage_'+id+'" id="monthduemukhyalayasentage_'+id+'" value="" ></div><div class="col-md-3"><label>माह में प्रेषित सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष प्राप्त</label><input type="text" class="form-control" name="monthdueprakhandsentage_'+id+'" id="monthdueprakhandsentage_'+id+'" value="" ></div><div class="col-md-3"><label>माह में प्रेषित सेंटेज प्रेषित धनराशि का यू०टी०आर० नम्बर एवं दिनांक</label><input type="text" class="form-control" name="monthsentageutr_'+id+'" id="monthsentageutr_'+id+'" value="" ></div><div class="col-md-3"><label>क्रमिक देय सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष </label><input onInput="addCalc()"  type="text" class="form-control" name="khamikdeymukhyalaya_'+id+'" id="khamikdeymukhyalaya_'+id+'" value="" ></div><div class="col-md-3"><label>क्रमिक देय सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष  </label><input onInput="addCalc()"  type="text" class="form-control" name="khamikdeyprakhand_'+id+'" id="khamikdeyprakhand_'+id+'" value="" ></div><div class="col-md-3"><label>क्रमिक प्रेषित सेंटेज मुख्यालय से प्राप्त धनराशि के सापेक्ष  </label><input  type="text" onInput="addCalc()" class="form-control" name="khamikpreshitmukhyalaya_'+id+'" id="khamikpreshitmukhyalaya_'+id+'" value="" ></div><div class="col-md-3"><label>क्रमिक प्रेषित सेंटेज प्रखण्ड स्तर पर प्राप्त धनराशि के सापेक्ष  </label><input  type="text" onInput="addCalc()"  class="form-control" name="khamikpreshitprakhand_'+id+'" id="khamikpreshitprakhand_'+id+'" value="" ></div><div class="col-md-3"><label>अवशेष देय सेंटेज</label><input type="text" onInput="addCalc()"  class="form-control" name="remaindey_'+id+'" id="remaindey_'+id+'" value="" ></div><div class="col-md-3"><label>अवशेष का कारण </label><input type="text" class="form-control" name="remainreason_'+id+'" id="remainreason_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_1_add_button" class="btn btn-info pull-right" onClick="prarup_1_add_rows()">Add</button></div></div>';
            $("#prarup_1_insert").append(txt);
            $("#prarup_1_id").val(id);
	}
	</script>
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