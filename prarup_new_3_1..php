<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		if($_POST['yojnaname_1']!=""){
			$sql='insert into invoice_prarup_3 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_3_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_3_id']; $i++){
					if($_POST['yojnaname_'.$i]!= ""){
						$sql = 'insert into trans_prarup_3 (`invoice_id`, `yojnaname`, `yojnalagat`, `getdhanrashi`, `dateofcredit`, `utrnum`, `mukhyalayaagrimsentage`, `mukhyalayawithhold`, `gsttds`, `leboreses`, `mukhyalaya_gstdeduction`, `totalrupees`, `avsheshdhanrashi`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['yojnaname_'.$i].'", "'.$_POST['yojnalagat_'.$i].'", "'.$_POST['getdhanrashi_'.$i].'", "'.$_POST['dateofcredit_'.$i].'", "'.$_POST['utrnum_'.$i].'", "'.$_POST['mukhyalayaagrimsentage_'.$i].'", "'.$_POST['mukhyalayawithhold_'.$i].'", "'.$_POST['gsttds_'.$i].'", "'.$_POST['leboreses_'.$i].'", "'.$_POST['mukhyalaya_gstdeduction_'.$i].'", "'.$_POST['totalrupees_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
		$sql = 'update invoice_prarup_3 set 
			`total_trans`= "'.$_POST['prarup_3_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("Y-m-d H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_3 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
					for($i=1; $i<=$_POST['prarup_3_id']; $i++){
						if($_POST['yojnaname_1']!=""){
						$sql = 'insert into trans_prarup_3 (`invoice_id`, `yojnaname`, `yojnalagat`, `getdhanrashi`, `dateofcredit`, `utrnum`, `mukhyalayaagrimsentage`, `mukhyalayawithhold`, `gsttds`, `leboreses`,`mukhyalaya_gstdeduction`, `totalrupees`, `avsheshdhanrashi`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['yojnaname_'.$i].'", "'.$_POST['yojnalagat_'.$i].'", "'.$_POST['getdhanrashi_'.$i].'", "'.$_POST['dateofcredit_'.$i].'", "'.$_POST['utrnum_'.$i].'", "'.$_POST['mukhyalayaagrimsentage_'.$i].'", "'.$_POST['mukhyalayawithhold_'.$i].'", "'.$_POST['gsttds_'.$i].'", "'.$_POST['leboreses_'.$i].'", "'.$_POST['mukhyalaya_gstdeduction_'.$i].'", "'.$_POST['totalrupees_'.$i].'", "'.$_POST['avsheshdhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
	$_POST['prarup_3_id']="1";
	$_POST['edit_sno']="";
	
	
	$_POST['yojnalagat_1'] ="";
	$_POST['getdhanrashi_1'] ="";
	$_POST['dateofcredit_1'] ="";
	$_POST['utrnum_1'] ="";
	$_POST['mukhyalayaagrimsentage_1'] ="";
	$_POST['mukhyalayawithhold_1'] ="";
	$_POST['gsttds_1'] ="";
	$_POST['leboreses_1'] ="";
	$_POST['totalrupees_1'] ="";
	$_POST['avsheshdhanrashi_1'] ="";
	$_POST['mukhyalaya_gstdeduction_1'] ="";
	
	
}
if(isset($_GET['edit_sno'])){
	$sql = 'select * from trans_prarup_3 where invoice_id="'.$_GET['edit_sno'].'"';
	//   $sql.'<br>';
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['prarup_3_id'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_new_5_id'].'<br/>';
			
			$_POST['yojnaname_'.$i] =$row_rcpt['yojnaname'];
			$_POST['yojnalagat_'.$i] =$row_rcpt['yojnalagat'];
			$_POST['getdhanrashi_'.$i] =$row_rcpt['getdhanrashi'];
			$_POST['dateofcredit_'.$i] =$row_rcpt['dateofcredit'];
			$_POST['utrnum_'.$i] =$row_rcpt['utrnum'];
			$_POST['mukhyalayaagrimsentage_'.$i] =$row_rcpt['mukhyalayaagrimsentage'];
			$_POST['mukhyalayawithhold_'.$i] =$row_rcpt['mukhyalayawithhold'];
			$_POST['gsttds_'.$i] =$row_rcpt['gsttds'];
			$_POST['leboreses_'.$i] =$row_rcpt['leboreses'];
			$_POST['totalrupees_'.$i] =$row_rcpt['totalrupees'];
			$_POST['avsheshdhanrashi_'.$i] =$row_rcpt['avsheshdhanrashi'];
			$_POST['mukhyalaya_gstdeduction_'.$i] =$row_rcpt['mukhyalaya_gstdeduction'];
			
			$i++;
		}
	}
	else{
		$_POST['yojnaname_1']="";
		$_POST['yojnalagat_1'] ="";
		$_POST['getdhanrashi_1'] ="";
		$_POST['dateofcredit_1'] ="";
		$_POST['utrnum_1'] ="";
		$_POST['mukhyalayaagrimsentage_1'] ="";
		$_POST['mukhyalayawithhold_1'] ="";
		$_POST['gsttds_1'] ="";
		$_POST['leboreses_1'] ="";
		$_POST['totalrupees_1'] ="";
		$_POST['avsheshdhanrashi_1'] ="";
		$_POST['prarup_3_id'] ="1";
	}
	$_POST['edit_sno']=$_GET['edit_sno'];
}

if(isset($_GET['delid'])){

	$sql = 'delete from invoice_prarup_2 where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	$sql = 'delete from trans_prarup_3 where invoice_id="'.$_GET['delid'].'"';
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
                            <h4 class="card-title text-center">निर्माण कार्य हेतु मुख्यालय से प्राप्त धनराशि का विवरण वर्ष 2023-24</h4></br>
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
							for($i=1; $i<=$_POST['prarup_3_id']; $i++){
							?>
							<div class="row border rounded m-2 p-2 border-secondary">
								<div class="col-md-3">
									<label>योजना का नाम </label>
									<select class="form-control" name="yojnaname_<?php echo $i; ?>" id="yojnaname_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value), fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
											$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id
											 IN (' . implode(",", $_SESSION['divisions']) . '))';
											// echo $query;
											$run = mysqli_query($db,$query);
											while($data = mysqli_fetch_array($run)){
												echo '<option value="'.$data['sno'].'" ';
												if(isset($_POST['yojnaname_'.$i])){
													if($_POST['yojnaname_'.$i]==$data['sno']){
														echo ' selected="Selected"';
													}
												}
												echo '>'.trim($data['project_name_hindi']).'</option>';
											}
										?>
									</select>
								</div>
								<div class="col-md-3"><label>योजना की लागत </label>
								<input onInput="addCalc()" type="text" class="form-control" name="yojnalagat_<?php echo $i; ?>" id="yojnalagat_<?php echo $i; ?>" value="<?php echo $_POST['yojnalagat_'.$i]; ?>"></div>

								<div class="col-md-3"><label>प्राप्त धनराशि </label>
								<input onInput="addCalc()" type="text" class="form-control" name="getdhanrashi_<?php echo $i; ?>" id="getdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['getdhanrashi_'.$i]; ?>"></div>

								<div class="col-md-3"><label>बैंक में क्रेडिट होने का दिनांक</label>
								<input type="text" class="form-control" name="dateofcredit_<?php echo $i; ?>" id="dateofcredit_<?php echo $i; ?>" value="<?php echo $_POST['dateofcredit_'.$i]; ?>"></div>

								<div class="col-md-3"><label>यू०टी०आर० नम्बर </label>
								<input type="text" class="form-control" name="utrnum_<?php echo $i; ?>" id="utrnum_<?php echo $i; ?>" value="<?php echo $_POST['utrnum_'.$i] ?>"></div>

								<div class="col-md-3"><label>मुख्यालय द्वारा अग्रिम सेंटेज कटौती </label>
								<input onInput="addCalc()" type="text" class="form-control" name="mukhyalayaagrimsentage_<?php echo $i; ?>" id="mukhyalayaagrimsentage_<?php echo $i; ?>" value="<?php echo $_POST['mukhyalayaagrimsentage_'.$i]; ?>"></div>

								<div class="col-md-3"><label>मुख्यालय द्वारा विदहेल्ड आयकर </label>
								<input onInput="addCalc()" type="text" class="form-control" name="mukhyalayawithhold_<?php echo $i; ?>" id="mukhyalayawithhold_<?php echo $i; ?>" value="<?php echo $_POST['mukhyalayawithhold_'.$i];?>"></div>

								<div class="col-md-3"><label>जी०एस०टी० टी०डी०एस०</label>
								<input type="text" class="form-control" name="gsttds_<?php echo $i; ?>" id="gsttds_<?php echo $i; ?>" value="<?php echo $_POST['gsttds_'.$i]; ?>"></div>
								
								<div class="col-md-3"><label>लेबर सेस</label>
								<input type="text" class="form-control" name="leboreses_<?php echo $i; ?>" id="leboreses_<?php echo $i; ?>" value="<?php echo $_POST['leboreses_'.$i]; ?>"></div>

								<div class="col-md-3"><label>मुख्यालय द्वारा अग्रिम GST कटौती </label>
								<input onInput="addCalc()" type="text" class="form-control" name="mukhyalaya_gstdeduction_<?php echo $i; ?>" id="mukhyalaya_gstdeduction_<?php echo $i; ?>" value="<?php echo $_POST['mukhyalaya_gstdeduction_'.$i]; ?>"></div>

								<div class="col-md-3"><label>कुल धनराशि  </label>
								<input readonly onInput="addCalc()" type="text" class="form-control" name="totalrupees_<?php echo $i; ?>" id="totalrupees_<?php echo $i; ?>" value="<?php echo $_POST['totalrupees_'.$i]; ?>">

								</div><div class="col-md-3"><label>अवशेष धनराशि</label>
								<input readonly type="text" class="form-control" name="avsheshdhanrashi_<?php echo $i; ?>" id="avsheshdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['avsheshdhanrashi_'.$i]; ?>"></div>
								<!-- isset($_GET['editid']) ? $editrow['avsheshdhanrashi_'.$i] : ''; -->

								<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_3_add_button" class="btn btn-info pull-right" onClick="prarup_3_add_rows()">Add</button></div>
							</div>
							<?php
							}
							?>
							<input type="hidden" name="prarup_3_id" id="prarup_3_id" value="<?php echo $_POST['prarup_3_id']; ?>">
							<div id="prarup_3_insert"></div>
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
		</div>
	</form>

	<script>
	function prarup_3_add_rows(){
		var id = parseFloat($("#prarup_3_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_3_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>योजना का नाम </label><select class="form-control" name="yojnaname_'+id+'" id="yojnaname_'+id+'" ><option value="">--- Select--</option>';
		<?php
		$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
		$run = mysqli_query($db,$query);
		while($data = mysqli_fetch_array($run)){
			echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label>योजना की लागत </label><input onInput="addCalc()" type="text" class="form-control" name="yojnalagat_'+id+'" id="yojnalagat_'+id+'" value="" ></div><div class="col-md-3"><label>प्राप्त धनराशि </label><input onInput="addCalc()" type="text" class="form-control" name="getdhanrashi_'+id+'" id="getdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>बैंक में क्रेडिट होने का दिनांक</label><input onInput="addCalc()" type="text" class="form-control" name="dateofcredit_'+id+'" id="dateofcredit_'+id+'" value="" ></div><div class="col-md-3"><label>यू०टी०आर० नम्बर </label><input onInput="addCalc()" type="text" class="form-control" name="utrnum_'+id+'" id="utrnum_'+id+'" value="" ></div><div class="col-md-3"><label>मुख्यालय द्वारा अग्रिम सेंटेज कटौती </label><input onInput="addCalc()" type="text" class="form-control" name="mukhyalayaagrimsentage_'+id+'" id="mukhyalayaagrimsentage_'+id+'" value="" ></div><div class="col-md-3"><label>मुख्यालय द्वारा विदहेल्ड आयकर </label><input  onInput="addCalc()"type="text" class="form-control" name="mukhyalayawithhold_'+id+'" id="mukhyalayawithhold_'+id+'" value="" ></div><div class="col-md-3"><label>जी०एस०टी० टी०डी०एस०</label><input onInput="addCalc()" type="text" class="form-control" name="gsttds_'+id+'" id="gsttds_'+id+'" value="" ></div><div class="col-md-3"><label>लेबर सेस</label><input onInput="addCalc()" type="text" class="form-control" name="leboreses_'+id+'" id="leboreses_'+id+'" value="" ></div><div class="col-md-3"><label>मुख्यालय द्वारा अग्रिम GST कटौती</label><input onInput="addCalc()" type="text" class="form-control" name="mukhyalaya_gstdeduction_'+id+'" id="mukhyalaya_gstdeduction_'+id+'" value="" ></div><div class="col-md-3"><label>कुल धनराशि  </label><input readonly onInput="addCalc()" type="text" class="form-control" name="totalrupees_'+id+'" id="totalrupees_'+id+'" value="" ></div><div class="col-md-3"><label>अवशेष धनराशि</label><input readonly onInput="addCalc()" type="text" class="form-control" name="avsheshdhanrashi_'+id+'" id="avsheshdhanrashi_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_3_add_button" class="btn btn-info pull-right" onClick="prarup_3_add_rows()">Add</button></div></div>';
            $("#prarup_3_insert").append(txt);
            $("#prarup_3_id").val(id);
	}
	</script>
 
	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">निर्माण कार्य हेतु मुख्यालय से प्राप्त धनराशि का विवरण वर्ष 2023-24 </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_3` where invoice_prarup_3.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_new_3 = execute_query($sql);
							$i=1;
							while($row_prarup_new_3 = mysqli_fetch_assoc($result_prarup_new_3)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_3['division_id'].'"';
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
								<td>'.$row_prarup_new_3['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_3['sno'].'" onClick="return confirm(\'Are you sure ?\');"><i class="far fa-trash-alt"></i></a></td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_new_3['sno'].'" onClick="return confirm(\'Are you sure ?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit " ></span></a></td>
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
		function addCalc(line_serial) {
			var id = parseFloat($("#prarup_3_id").val());

			for (let i = 1; i <= id; i++) {
				let yojnalagat = parseFloat(document.getElementById("yojnalagat_" + i).value);
				if (!yojnalagat) {
					yojnalagat = 0;
				}

				let getdhanrashi = parseFloat(document.getElementById("getdhanrashi_" + i).value);
				if (!getdhanrashi) {
					getdhanrashi = 0;
				}

				let mukhyalayaagrimsentage = parseFloat(document.getElementById("mukhyalayaagrimsentage_" + i).value);
				if (!mukhyalayaagrimsentage) {
					mukhyalayaagrimsentage = 0;
				}

				let mukhyalayawithhold = parseFloat(document.getElementById("mukhyalayawithhold_" + i).value);
				if (!mukhyalayawithhold) {
					mukhyalayawithhold = 0;
				}

				let mukhyalaya_gstdeduction = parseFloat(document.getElementById("mukhyalaya_gstdeduction_" + i).value);
				if (!mukhyalaya_gstdeduction) {
					mukhyalaya_gstdeduction = 0;
				}
				
				let totalrupees = parseFloat(document.getElementById("totalrupees_" + i).value);
				if (!totalrupees) {
					totalrupees = 0;
				}
				let leboreses = parseFloat(document.getElementById("leboreses_" + i).value);
				if (!leboreses) {
					leboreses = 0;
				}
				
				let gsttds = parseFloat(document.getElementById("gsttds_" + i).value);
				if (!gsttds) {
					gsttds = 0;
				}
				
				var awashesh_dhanrashi_ans=totalrupees-yojnalagat;
				document.getElementById("avsheshdhanrashi_"+i).value=awashesh_dhanrashi_ans;
				console.log(awashesh_dhanrashi_ans);
				
				var total_rashi_ans=getdhanrashi+mukhyalayaagrimsentage+mukhyalayawithhold+mukhyalaya_gstdeduction+leboreses+gsttds;
				document.getElementById("totalrupees_"+i).value=total_rashi_ans;
				
			}
		}

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>