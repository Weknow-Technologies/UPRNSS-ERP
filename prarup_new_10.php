<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['project_id_1']!=""){
		$sql='insert into invoice_prarup_new_10 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_new_10_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			
			$inv_id = mysqli_insert_id($db);
			
			for($i=1; $i<=$_POST['prarup_new_10_id']; $i++){
				if($_POST['avsheshrashi_'.$i]!=""){
					$sql = 'insert into trans_prarup_new_10 (`invoice_id`,`project_id`, `avsheshrashi`, `bankname`, `accname`, `utrdate`, `toteldhanrashi`, `dersitavadhi`, `remark`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['project_id_'.$i].'", "'.$_POST['avsheshrashi_'.$i].'", "'.$_POST['bankname_'.$i].'", "'.$_POST['accname_'.$i].'", "'.$_POST['utrdate_'.$i].'", "'.$_POST['toteldhanrashi_'.$i].'", "'.$_POST['dersitavadhi_'.$i].'", "'.$_POST['remark_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
if(isset($_GET['delid'])){

	$sql = 'delete from invoice_prarup_new_10 where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		
	}
	else{
		$sql = 'delete from trans_prarup_new_10 where invoice_id="'.$_GET['delid'].'"';
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
						<h4 class="card-title text-center">प्रखण्डों के अभिलेखों में दर्शित बी०आर०जी०एफ० की अवशेष धनराशि का विवरण</h4></br>
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
								<label>परियोजना का नाम</label>
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
								<label>परियोजना की अवशेष धनराशि</label>
								<input type="text" class="form-control" name="avsheshrashi_<?php echo $i; ?>" id="avsheshrashi_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['avsheshrashi_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>बैंक का नाम</label>
								<input type="text" class="form-control" name="bankname_<?php echo $i; ?>" id="bankname_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['bankname_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>खाते का प्रकार </label>
								<input type="text" class="form-control" name="accname_<?php echo $i; ?>" id="accname_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['accname_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>अर्जित ब्याज की धनराशि</label>
								<input type="text" class="form-control" name="utrdate_<?php echo $i; ?>" id="utrdate_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['utrdate_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>कुल धनराशि</label>
								<input type="text" class="form-control" name="toteldhanrashi_<?php echo $i; ?>" id="toteldhanrashi_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['toteldhanrashi_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>दर्शित अवधि (प्रखण्ड स्तर पर किस दिनाक से लंबित है )</label>
								<input type="text" class="form-control" name="dersitavadhi_<?php echo $i; ?>" id="dersitavadhi_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['dersitavadhi_'.$i] : ''; ?>">
							</div>
							<div class="col-md-3">
								<label>अभियुक्ति</label>
								<input type="text" class="form-control" name="remark_<?php echo $i; ?>" id="remark_<?php echo $i; ?>" value="<?php echo isset($_GET['editid']) ? $editrow['remark_'.$i] : ''; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_new_10_add_button" class="btn btn-info pull-right" onClick="prarup_new_10_add_rows()">Add</button>
								
							</div>
							
						</div>
						<input type="hidden" name="prarup_new_10_id" id="prarup_new_10_id" value="1">
						<div id="prarup_new_10_insert"></div>
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
	function prarup_new_10_add_rows(){
       
		var id = parseFloat($("#prarup_new_10_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		$("#prarup_new_10_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>योजना// विभाग का नाम</label><select class="form-control" name="project_id_'+id+'" id="project_id_'+id+'" ><option value="">--- Select--</option>';
		<?php
		$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
		$run = mysqli_query($db,$query);
		while($data = mysqli_fetch_array($run)){
			echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
		}
		?>
		txt += '</select></div><div class="col-md-3"><label>परियोजना की अवशेष धनराशि</label><input type="text" class="form-control" name="avsheshrashi_'+id+'" id="avsheshrashi_'+id+'" value="" ></div><div class="col-md-3"><label>बैंक का नाम</label><input type="text" class="form-control" name="bankname_'+id+'" id="bankname_'+id+'" value="" ></div><div class="col-md-3"><label>खाते का प्रकार </label><input type="text" class="form-control" name="accname_'+id+'" id="accname_'+id+'" value="" ></div><div class="col-md-3"><label>अर्जित ब्याज की धनराशि</label><input type="text" class="form-control" name="utrdate_'+id+'" id="utrdate_'+id+'" value="" ></div><div class="col-md-3"><label>कुल धनराशि</label><input type="text" class="form-control" name="toteldhanrashi_'+id+'" id="toteldhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>दर्शित अवधि (प्रखण्ड स्तर पर किस दिनाक से लंबित है )</label><input type="text" class="form-control" name="dersitavadhi_'+id+'" id="dersitavadhi_'+id+'" value="" ></div><div class="col-md-3"><label>अभियुक्ति</label><input type="text" class="form-control" name="remark_'+id+'" id="remark_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_new_10_add_button" class="btn btn-info pull-right" onClick="prarup_new_10_add_rows()">Add</button></div></div>';
            $("#prarup_new_10_insert").append(txt);
            $("#prarup_new_10_id").val(id);
	}
</script>


	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्डों के अभिलेखों में दर्शित बी०आर०जी०एफ० की अवशेष धनराशि का विवरण </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>DELETE</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_10` where invoice_prarup_new_10.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_new_10 = execute_query($sql);
							$i=1;
							while($row_prarup_new_10 = mysqli_fetch_assoc($result_prarup_new_10)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_10['division_id'].'"';
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
								<td>'.$row_prarup_new_10['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_10['sno'].'" ><i class="far fa-trash-alt"></i></a></td>
								
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