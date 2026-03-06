<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';
// print_r($_POST);
if(isset($_POST['submit'])){
	if($_POST['editid']==""){
		if($_POST['bankinfo_1']!=""){
			$sql='insert into invoice_prarup_new_11 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date("Y-m-d").'", "'.$_POST['prarup_new_11_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
				
				for($i=1; $i<=$_POST['prarup_new_11_id']; $i++){
					if($_POST['bankinfo_'.$i]!=""){
						$sql = 'insert into trans_prarup_new_11 (`invoice_id`, `bankinfo`,`branch`, `accnum`, `dhanrashi`, `savadhidhanrashi`, `sum`, `rate`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['bankinfo_'.$i].'","'.$_POST['branch_'.$i].'", "'.$_POST['accnum_'.$i].'", "'.$_POST['dhanrashi_'.$i].'", "'.$_POST['savadhidhanrashi_'.$i].'", "'.$_POST['sum_'.$i].'", "'.$_POST['rate_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
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
	} else {
		// Edit mode
		$sql = 'update invoice_prarup_new_11 set 
				`total_trans`= "'.$_POST['prarup_new_11_id'].'",
				`edited_by` = "'.$_SESSION['username'].'", 
				`edition_time` = "'.date("Y-m-d H:i:s").'"
				where sno="'.$_POST['editid'].'"';
		execute_query($sql);

		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		} else {
			// Delete existing trans_prarup_new_11 records for the edited invoice
			$sql = 'delete from trans_prarup_new_11 where invoice_id="'.$_POST['editid'].'"';
			execute_query($sql);

			// Insert updated data into trans_prarup_new_11
			for($i=1; $i<=$_POST['prarup_new_11_id']; $i++){
				if($_POST['bankinfo_'.$i]!=""){
					$sql = 'insert into trans_prarup_new_11 (`invoice_id`, `bankinfo`,`branch`, `accnum`, `dhanrashi`, `savadhidhanrashi`, `sum`, `rate`, created_by, creation_time) values ("'.$_POST['editid'].'", "'.$_POST['bankinfo_'.$i].'","'.$_POST['branch_'.$i].'", "'.$_POST['accnum_'.$i].'", "'.$_POST['dhanrashi_'.$i].'", "'.$_POST['savadhidhanrashi_'.$i].'", "'.$_POST['sum_'.$i].'", "'.$_POST['rate_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'");';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}else{
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
} else {
	// Reset form values in case of a blank form submission
postblank:
	$_POST['prarup_new_11_id'] = "1";
	$_POST['editid'] = "";

	for ($i = 1; $i <= $_POST['prarup_new_11_id']; $i++) {
		$_POST['bankinfo_' . $i] = "";
		$_POST['branch_' . $i] = "";
		$_POST['accnum_' . $i] = "";
		$_POST['dhanrashi_' . $i] = "";
		$_POST['savadhidhanrashi_' . $i] = "";
		$_POST['sum_' . $i] = "";
		$_POST['rate_' . $i] = "";
	}
}

if(isset($_GET['editid'])){

	
	$sql_invoice = 'select * from invoice_prarup_new_11 where sno="'.$_GET['editid'].'"';
	$result_invoice = execute_query($sql_invoice);

	if(mysqli_num_rows($result_invoice) != 0){

		$row_invoice = mysqli_fetch_assoc($result_invoice);

		$_POST['prarup_new_11_id'] = $row_invoice['total_trans'];
		$_POST['division_id'] = $row_invoice['division_id'];

		$sql_trans = 'select * from trans_prarup_new_11 where invoice_id="'.$_GET['editid'].'"';
		$result_trans = execute_query($sql_trans);

		if(mysqli_num_rows($result_trans) != 0){
			$i = 1;
			while($row_trans = mysqli_fetch_assoc($result_trans)){
				$_POST['bankinfo_'.$i] = $row_trans['bankinfo'];
				$_POST['branch_'.$i] = $row_trans['branch'];
				$_POST['accnum_'.$i] = $row_trans['accnum'];
				$_POST['dhanrashi_'.$i] = $row_trans['dhanrashi'];
				$_POST['savadhidhanrashi_'.$i] = $row_trans['savadhidhanrashi'];
				$_POST['sum_'.$i] = $row_trans['sum'];
				$_POST['rate_'.$i] = $row_trans['rate'];

				$i++;
			}
		}
		else{
			for ($i = 1; $i <= $_POST['prarup_new_11_id']; $i++) {
				$_POST['bankinfo_'.$i] = "";
				$_POST['branch_'.$i] = "";
				$_POST['accnum_'.$i] = "";
				$_POST['dhanrashi_'.$i] = "";
				$_POST['savadhidhanrashi_'.$i] = "";
				$_POST['sum_'.$i] = "";
				$_POST['rate_'.$i] = "";
			}
		}

		$_POST['editid'] = $_GET['editid'];
	} else {
		// Handle the case where the invoice entry is not found
	}
}


	if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_new_11 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			
		}
		else{
			$sql = 'delete from trans_prarup_new_11 where invoice_id="'.$_GET['delid'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<div class="alert alert-warning text-dark">Data Delete</div>';
			}
		}		
	}
?>


	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्डों के अभिलेखों में बचत खाता एवं सावधि जमा में धनराशि का विवरण</h4></br>
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
							for($i=1;$i<= $_POST['prarup_new_11_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-3">
								<label>बैंक का नाम</label>
								<input type="text" class="form-control" name="bankinfo_<?php echo $i; ?>" id="bankinfo_<?php echo $i; ?>" value="<?php echo $_POST['bankinfo_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>बैंक का  शाखा </label>
								<input type="text" class="form-control" name="branch_<?php echo $i; ?>" id="branch_<?php echo $i; ?>" value="<?php echo $_POST['branch_'.$i] ?>">
							</div>
							<div class="col-md-3">
								<label>खाता संख्या</label>
								<input type="text" class="form-control" name="accnum_<?php echo $i; ?>" id="accnum_<?php echo $i; ?>" value="<?php echo $_POST['accnum_'.$i] ; ?>">
							</div>
							<div class="col-md-3">
								<label>बचत खाते में धनराशि</label>
								<input type="text" class="form-control" name="dhanrashi_<?php echo $i; ?>" id="dhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['dhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>सवधि खाते में धनराशि</label>
								<input type="text" class="form-control" name="savadhidhanrashi_<?php echo $i; ?>" id="savadhidhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['savadhidhanrashi_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>योग</label>
								<input type="text" class="form-control" name="sum_<?php echo $i; ?>" id="sum_<?php echo $i; ?>" value="<?php echo $_POST['sum_'.$i]; ?>">
							</div>
							<div class="col-md-3">
								<label>बचत / सावधि की दर</label>
								<input type="text" class="form-control" name="rate_<?php echo $i; ?>" id="rate_<?php echo $i; ?>" value="<?php echo $_POST['rate_'.$i] ; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_new_11_add_button" class="btn btn-info pull-right" onClick="prarup_new_11_add_rows()">Add</button>
							</div>
							
						</div>
						<?php } ?>
						<input type="hidden" name="prarup_new_11_id" id="prarup_new_11_id" value="<?php echo $_POST['prarup_new_11_id'];?>">
						<div id="prarup_new_11_insert"></div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<input type="hidden" id="editid" name="editid" value="<?php echo $_POST['editid']; ?>">
								<?php 
									if (isset($_GET['editid']) && $_GET['editid'] != "") {
										echo'<button type="submit" name="submit" class="btn btn-success">Update</button>
										<a href="prarup_new_11.php" class="btn btn-info">Back</a>';
									}else{
										echo '<button type="submit" name="submit" class="btn btn-success">Submit</button>';
										$_GET['editid']="";
									}	
								?>
								<!-- <button type="submit" name="submit" class="btn btn-success">Submit</button>
								<input type="hidden" id="id" name="id" value="1"> -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	
<script>
	function prarup_new_11_add_rows(){
       
		var id = parseFloat($("#prarup_new_11_id").val());
		if(!id){
			id=0;
		}
		id = id+1;
		
		$("#prarup_new_11_add_button").remove();
		var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>बैंक का नाम</label><input type="text" class="form-control" name="bankinfo_'+id+'" id="bankinfo_'+id+'" value="" ></div><div class="col-md-3"><label>बैंक का  शाखा </label><input type="text" class="form-control" name="branch_'+id+'" id="branch_'+id+'" value="" ></div><div class="col-md-3"><label>खाता संख्या</label><input type="text" class="form-control" name="accnum_'+id+'" id="accnum_'+id+'" value="" ></div><div class="col-md-3"><label>बचत खाते में धनराशि</label><input type="text" class="form-control" name="dhanrashi_'+id+'" id="dhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>सवधि खाते में धनराशि</label><input type="text" class="form-control" name="savadhidhanrashi_'+id+'" id="savadhidhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>योग</label><input type="text" class="form-control" name="sum_'+id+'" id="sum_'+id+'" value="" ></div><div class="col-md-3"><label>बचत / सावधि की दर</label><input type="text" class="form-control" name="rate_'+id+'" id="rate_'+id+'" value="" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_new_11_add_button" class="btn btn-info pull-right" onClick="prarup_new_11_add_rows()">Add</button></div></div>';
            $("#prarup_new_11_insert").append(txt);
            $("#prarup_new_11_id").val(id);
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
									<th>Delete</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_11` where invoice_prarup_new_11.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_new_11 = execute_query($sql);
							$i=1;
							while($row_prarup_new_11 = mysqli_fetch_assoc($result_prarup_new_11)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_11['division_id'].'"';
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
								<td>'.$row_prarup_new_11['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_11['sno'].'" onClick="return confirm(\'Are you sure ?\');" ><i class="far fa-trash-alt"></i></a></td>';
								?>
								<td><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?editid=<?php echo $row_prarup_new_11['sno']; ?>"  onClick="return confirm(\'Are you sure ?\');"><i class="far fa-edit"></i></a></td></tr>
								<?php


								//<td><a href="view_format_1.php?id='.$row_prarup_new_11['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
								
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