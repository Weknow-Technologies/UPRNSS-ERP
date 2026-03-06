<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){
		if($_POST['bankname_1']!=""){
			$sql='insert into invoice_prarup_2 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_2_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_2_id']; $i++){
					if($_POST['month_'.$i]!=""){
						$sql = 'insert into trans_prarup_2 (`invoice_id`,`month`, `bankname`, `acctype`, `rate`, `startingavshesh`, `monthbyaj`, `kramikarjit`, `mukhyalayamahpreshit`, `utrdate`, `mukhyalayakramik`, `avsheshvyaj`, `resonavshesh`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['month_'.$i].'","'.$_POST['bankname_'.$i].'", "'.$_POST['acctype_'.$i].'", "'.$_POST['rate_'.$i].'", "'.$_POST['startingavshesh_'.$i].'", "'.$_POST['monthbyaj_'.$i].'", "'.$_POST['kramikarjit_'.$i].'", "'.$_POST['mukhyalayamahpreshit_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['mukhyalayakramik_'.$i].'", "'.$_POST['avsheshvyaj_'.$i].'", "'.$_POST['resonavshesh_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
						echo $sql = 'insert into trans_prarup_2 (`invoice_id`,`month`, `bankname`, `acctype`, `rate`, `startingavshesh`, `monthbyaj`, `kramikarjit`, `mukhyalayamahpreshit`, `utrdate`, `mukhyalayakramik`, `avsheshvyaj`, `resonavshesh`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['month_'.$i].'","'.$_POST['bankname_'.$i].'", "'.$_POST['acctype_'.$i].'", "'.$_POST['rate_'.$i].'", "'.$_POST['startingavshesh_'.$i].'", "'.$_POST['monthbyaj_'.$i].'", "'.$_POST['kramikarjit_'.$i].'", "'.$_POST['mukhyalayamahpreshit_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['mukhyalayakramik_'.$i].'", "'.$_POST['avsheshvyaj_'.$i].'", "'.$_POST['resonavshesh_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
	
		$sql = 'delete from invoice_prarup_2 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_2 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}
?>


	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center bg-danger text-white p-2">मुख्यालय को ब्याज मद में धनराशि प्रेषण का विवरण वर्ष 2023-24 </h4></br>
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
							for($i=1;$i<= $_POST['prarup_2_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="">
							<div class="col-md-3">
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
							<div class="col-md-3">
								<label>बैंक का नाम </label>
								<input type="text" class="form-control" name="bankname_<?php echo $i; ?>" id="bankname_<?php echo $i; ?>"
								 value="<?php echo $_POST['bankname_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>बचत / सावधि जमा संख्या एवं खाते का प्रकार </label>
								<input type="text" class="form-control" name="acctype_<?php echo $i; ?>" id="acctype_<?php echo $i; ?>"
								 value="<?php echo $_POST['acctype_'.$i]; ?>">
							</div>
							<div class="col-md-3"><label>ब्याज की दर </label><input type="text" class="form-control" name="rate_<?php echo $i; ?>" id="rate_<?php echo $i; ?>" value="<?php echo $_POST['rate_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>प्रारम्भिक अवशेष 01.04. 2023 </label>
								<input type="text" onInput="addCalc()" class="form-control" name="startingavshesh_<?php echo $i; ?>" id="startingavshesh_<?php echo $i; ?>" value="<?php echo $_POST['startingavshesh_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>माह मे अर्जित ब्याज </label>
								<input type="text" class="form-control" name="monthbyaj_<?php echo $i; ?>" id="monthbyaj_<?php echo $i; ?>" value="<?php echo $_POST['monthbyaj_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>क्रमिक अर्जित ब्याज </label>
								<input type="text" onInput="addCalc()" class="form-control" name="kramikarjit_<?php echo $i; ?>" id="kramikarjit_<?php echo $i; ?>" value="<?php echo $_POST['kramikarjit_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>मुख्यालय को माह मे प्रेषित ब्याज  </label>
								<input type="text" class="form-control" name="mukhyalayamahpreshit_<?php echo $i; ?>" id="mukhyalayamahpreshit_<?php echo $i; ?>" value="<?php echo $_POST['mukhyalayamahpreshit_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>यू०टी०आर० नम्बर दिनांक  </label>
								<input type="text" class="form-control" name="utrdate_<?php echo $i; ?>" id="utrdate_<?php echo $i; ?>" value="<?php echo $_POST['utrdate_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>मुख्यालय को क्रमिक प्रेषित ब्याज </label>
								<input type="text" onInput="addCalc()" class="form-control" name="mukhyalayakramik_<?php echo $i; ?>" id="mukhyalayakramik_<?php echo $i; ?>" value="<?php echo $_POST['mukhyalayakramik_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष देय ब्याज </label>
								<input type="text" readonly onInput="addCalc()" class="form-control" name="avsheshvyaj_<?php echo $i; ?>" id="avsheshvyaj_<?php echo $i; ?>" value="<?php echo $_POST['avsheshvyaj_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष का कारण </label>
								<input type="text" class="form-control" name="resonavshesh_<?php echo $i; ?>" id="resonavshesh_<?php echo $i; ?>" value="<?php echo $_POST['resonavshesh_'.$i]; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_2_add_button" class="btn btn-info pull-right" onClick="prarup_2_add_rows()">Add</button>
							</div>	
						</div>
						<?php } ?>
						<input type="hidden" name="prarup_2_id" id="prarup_2_id" value="<?php echo $_POST['prarup_2_id']; ?>">
						<div id="prarup_2_insert"></div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<!--<button type="submit" name="submit" class="btn btn-success">Submit</button> -->
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								<?php 
									if (isset($_GET['edit_sno']) && $_GET['edit_sno'] != "") {
										echo'<button type="submit" name="submit" class="btn btn-success">Update</button>
										<a href="prarup_new_2.php" class="btn btn-info">Back</a>';
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
		var id = parseFloat($("#prarup_2_id").val());

		for (let i = 1; i <= id; i++) {
			let startingavshesh = parseFloat(document.getElementById("startingavshesh_" + i).value);
			if (!startingavshesh) {
				startingavshesh = 0;
			}

			let kramikarjit = parseFloat(document.getElementById("kramikarjit_" + i).value);
			if (!kramikarjit) {
				kramikarjit = 0;
			}

			let mukhyalayakramik = parseFloat(document.getElementById("mukhyalayakramik_" + i).value);
			if (!mukhyalayakramik) {
				mukhyalayakramik = 0;
			}
			
			var ans=((startingavshesh+kramikarjit)-(mukhyalayakramik));
			document.getElementById("avsheshvyaj_"+i).value=ans.toFixed(2);
			// console.log(ans);
		}
	}

function prarup_2_add_rows() {
    var id = parseFloat($("#prarup_2_id").val());
    if (!id) {
        id = 0;
    }
    id = id + 1;
    $("#prarup_2_add_button").remove();
    var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="">' +
        '<div class="col-md-3"><label>माह</label>' +
        '<select name="month_' + id + '" id="month_' + id + '" class="form-control" value="" tabindex="">' +
        '<option value="Select">Select--</option>' +
        '<option value="January">January</option>' +
        '<option value="February">February</option>' +
        '<option value="March">March</option>' +
        '<option value="April">April</option>' +
        '<option value="May">May</option>' +
        '<option value="June">June</option>' +
        '<option value="July">July</option>' +
        '<option value="August">August</option>' +
        '<option value="September">September</option>' +
        '<option value="October">October</option>' +
        '<option value="November">November</option>' +
        '<option value="December">December</option>' +
        '</select></div>' +
        '<div class="col-md-3"><label>बैंक का नाम </label><input type="text" class="form-control" name="bankname_' + id + '" id="bankname_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>बचत / सावधि जमा संख्या एवं खाते का प्रकार </label><input type="text" class="form-control" name="acctype_' + id + '" id="acctype_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>ब्याज की दर </label><input type="text" class="form-control" name="rate_' + id + '" id="rate_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>प्रारम्भिक अवशेष 01.04. 2023 </label><input onInput="addCalc()" type="text" class="form-control" name="startingavshesh_' + id + '" id="startingavshesh_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>माह मे अर्जित ब्याज </label><input type="text" class="form-control" name="monthbyaj_' + id + '" id="monthbyaj_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>क्रमिक अर्जित ब्याज </label><input onInput="addCalc()" type="text" class="form-control" name="kramikarjit_' + id + '" id="kramikarjit_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>मुख्यालय को माह मे प्रेषित ब्याज  </label><input type="text" class="form-control" name="mukhyalayamahpreshit_' + id + '" id="mukhyalayamahpreshit_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>यू०टी०आर० नम्बर दिनांक  </label><input type="text" class="form-control" name="utrdate_' + id + '" id="utrdate_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>मुख्यालय को क्रमिक प्रेषित ब्याज </label><input onInput="addCalc()" type="text" class="form-control" name="mukhyalayakramik_' + id + '" id="mukhyalayakramik_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>अवशेष देय ब्याज </label><input onInput="addCalc()" readonly type="text" class="form-control" name="avsheshvyaj_' + id + '" id="avsheshvyaj_' + id + '" value=""></div>' +
        '<div class="col-md-3"><label>अवशेष का कारण </label><input type="text" class="form-control" name="resonavshesh_' + id + '" id="resonavshesh_' + id + '" value=""></div>' +
        '<div class="col-md-1 d-flex justify-content-align-items-center"><button type="button" id="prarup_2_add_button" class="btn btn-info pull-right" onClick="prarup_2_add_rows()">Add</button></div></div>';
    $("#prarup_2_insert").append(txt);
    $("#prarup_2_id").val(id);
}
</script>
		
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">मुख्यालय को ब्याज मद में धनराशि प्रेषण का विवरण वर्ष 2023-24 </h4></br>
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
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_2` ORDER BY division_id';
							}else{
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_2` where invoice_prarup_2.created_by = "'.$_SESSION['username'].'"';
							}
							
							$result_prarup_2 = execute_query($sql);
							$i=1;
							while($row_prarup_2 = mysqli_fetch_assoc($result_prarup_2)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_2['division_id'].'"';
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
								<td>'.$row_prarup_2['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_2['sno'].'" ><i class="far fa-trash-alt"></i></a></td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_2['sno'].'"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip"></span></a></td>
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