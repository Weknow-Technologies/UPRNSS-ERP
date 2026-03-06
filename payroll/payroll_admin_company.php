<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `company_details` SET `mobile`="'.$_POST['mobile'].'" , `company_name`="'.$_POST['company_name'].'" ,`address`="'.$_POST['address'].'",`pan`="'.$_POST['pan'].'",`gst`="'.$_POST['gst'].'",`email`="'.$_POST['email'].'", `state`="'.$_POST['state'].'" , `tan`="'.$_POST['tan'].'" , `registration_number`="'.$_POST['registration_number'].'" , `phone_number`="'.$_POST['phone_number'].'" , `owner_name`="'.$_POST['owner_name'].'" , `description`="'.$_POST['description'].'" , `company_type`="'.$_POST['company_type'].'" , `edited_by`="'.$_SESSION['username'].'" , `edition_time`="'.date('Y-m-d h:i:s').'" , `pincode`="'.$_POST['pincode'].'"  WHERE sno="'.$_POST['edit_sno'].'"';
		//echo $sql;
		if ($db->query($sql) === TRUE) {
			$msg='<div class="alert alert-success">New Record Updated Successfully!</div>';
		} 
		else{
				echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	else {
		$sql='INSERT INTO `company_details`(`company_name` , `address` , `mobile` , `pan` , `gst` , `email` , `state` , `tan` , `registration_number` , `phone_number` , `owner_name` , `description` , `company_type` , `created_by` , `creation_time` , `pincode`) VALUES ("'.$_POST['company_name'].'" , "'.$_POST['address'].'" , "'.$_POST['mobile'].'" , "'.$_POST['pan'].'" , "'.$_POST['gst'].'" , "'.$_POST['email'].'" , "'.$_POST['state'].'" , "'.$_POST['tan'].'" , "'.$_POST['registration_number'].'" , "'.$_POST['phone_number'].'" , "'.$_POST['owner_name'].'" , "'.$_POST['description'].'" , "'.$_POST['company_type'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'" , "'.$_POST['pincode'].'")';
		if ($db->query($sql) === TRUE) {
		$msg='<div class="alert alert-success">New Record Created Successfully</div>';
		}
		else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
		}
	}
}
if(isset($_GET['del'])){
	$sql ="delete from company_details where sno='".$_GET['del']."'";
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else {
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
}	
if(isset($_GET['id'])){
	$sql ="select * from company_details where sno='".$_GET['id']."'";
	$query = execute_query($sql);
	$row_edit = mysqli_fetch_array($query);
}
page_header_start('Company Master');
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
<style type="text/css">
	table, tr{
		padding:  20px;
	}
</style>

<div class="container">
	<div class="row">
		<?php echo $msg; ?>   
		<div class="col-sm-12">
			 
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel">
				<div class="panel-heading">Company Panel</div>
				<div class="panel-body">
					<div class="row"> 
						<div class="col-sm-6">
							<table class="table table-hover table-responsive table-condensed">
								<tr>
									<td >Company Name *</td>
									<td>
										<input type="text" name="company_name" placeholder="Enter Company name" value="<?php 
										if(isset($_GET['id'])){echo $row_edit['company_name']; }?>" class="form-control" tabindex="1"  required>
									</td>
								</tr>
								<tr>
									<td >Company Type *</td>
									<td>
										<select name="company_type" required class="form-control" tabindex="3">
											<option value="">-SELECT ANY ONE-</option>
											<?php 
												$sql_type = 'SELECT * FROM `company_type`';
												$result_type = execute_query($sql_type);
												while($row_type = mysqli_fetch_array($result_type)){
													?>
											<option value="<?php echo $row_type['sno']; ?>" <?php if(isset($_GET['id'])){if($row_type['sno']==$row_edit['company_type']){echo 'selected';}} ?>><?php echo $row_type['company_type_name']; ?></option>
													<?php
												}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td>GST Number *</td>
									<td>
										<input type="text" class="form-control"  required name="gst" placeholder="Enter GST Number" value="<?php if(isset($_GET['id'])){echo $row_edit['gst'];}?>" tabindex="5">
									</td>	
								</tr>
								<tr>
									<td>PAN Number</td>
									<td>
										<input type="text" class="form-control" name="pan" placeholder="Enter PAN Number" value="<?php if(isset($_GET['id'])){echo $row_edit['pan']; }?>" tabindex="7"></td>
								</tr>
								<tr>
									<td>TAN Number</td>
									<td>
										<input type="text" class="form-control" name="tan" placeholder="Enter TAN Number" value="<?php if(isset($_GET['id'])){echo $row_edit['tan']; }?>" tabindex="9"></td>
								</tr>
								<tr>
									<td>Registration Number</td>
									<td>
										<input type="text" class="form-control" name="registration_number" placeholder="Enter Registration Number" value="<?php if(isset($_GET['id'])){echo $row_edit['registration_number']; }?>" tabindex="11"></td>
								</tr>
								<tr>
									<td>Description</td>
									<td>
										<textarea class="form-control"  name="description" value="" id="title" tabindex="13"><?php if(isset($_GET['id'])){echo $row_edit['description'];}?></textarea>
									</td>

								</tr>
							</table>
						</div>
						<div class="col-sm-6">
							<table class="table table-hover table-responsive table-condensed">
								<tr>
									<td>Owner Name *</td>
									<td>
										<input type="text" class="form-control" name="owner_name" placeholder="Enter Owner Name"  required value="<?php if(isset($_GET['id'])){echo $row_edit['owner_name'];}?>" tabindex="2">
									</td>
								</tr>
								<tr>
									<td>Mobile *</td>
									<td>
										<input type="number" class="form-control" name="mobile" placeholder="Enter Mobile number"  required value="<?php if(isset($_GET['id'])){echo $row_edit['mobile'];}?>" tabindex="4">
									</td>
								</tr>
								<tr>
									<td>Phone Number</td>
									<td>
										<input type="number" class="form-control" name="phone_number" placeholder="Enter Phone number" value="<?php if(isset($_GET['id'])){echo $row_edit['phone_number'];}?>" tabindex="6">
									</td>
								</tr>
								<tr>
									<td>Email Id</td>
									<td>
										<input type="email" class="form-control" name="email" placeholder="Email" value="<?php if(isset($_GET['id'])){echo $row_edit['email'];}?>" tabindex="8">
									</td>
								</tr>
								<tr>
									<td>State *</td>
									<td>
										<select id="state" name="state" tabindex="10" class="form-control">
											<option value="Andaman and Nicobar Islands" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Andaman and Nicobar Islands"){echo 'selected';}}?>>Andaman and Nicobar Islands</option>
											<option value="Andhra Pradesh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Andhra Pradesh"){echo 'selected';}}?>>Andhra Pradesh</option>
											<option value="Andhra Pradesh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Andhra Pradesh"){echo 'selected';}}?>>Andhra Pradesh</option>
											<option value="Arunachal Pradesh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Arunachal Pradesh"){echo 'selected';}}?>>Arunachal Pradesh</option>
											<option value="Assam" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Assam"){echo 'selected';}}?>>Assam</option>
											<option value="Bihar" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Bihar"){echo 'selected';}}?>>Bihar</option>
											<option value="Chandigarh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Chandigarh"){echo 'selected';}}?>>Chandigarh</option>
											<option value="Chattisgarh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Chattisgarh"){echo 'selected';}}?>>Chattisgarh</option>
											<option value="Dadra and Nagar Haveli" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Dadra and Nagar Haveli"){echo 'selected';}}?>>Dadra and Nagar Haveli</option>
											<option value="Daman and Diu" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Daman and Diu"){echo 'selected';}}?>>Daman and Diu</option>
											<option value="Delhi" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Delhi"){echo 'selected';}}?>>Delhi</option>
											<option value="Goa" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Goa"){echo 'selected';}}?>>Goa</option>
											<option value="Gujarat" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Gujarat"){echo 'selected';}}?>>Gujarat</option>
											<option value="Haryana" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Haryana"){echo 'selected';}}?>>Haryana</option>
											<option value="Himachal Pradesh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Himachal Pradesh"){echo 'selected';}}?>>Himachal Pradesh</option>
											<option value="Jammu and Kashmir" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Jammu and Kashmir"){echo 'selected';}}?>>Jammu and Kashmir</option>
											<option value="Jharkhand" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Jharkhand"){echo 'selected';}}?>>Jharkhand</option>
											<option value="Karnataka" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Karnataka"){echo 'selected';}}?>>Karnataka</option>
											<option value="Kerala" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Kerala"){echo 'selected';}}?>>Kerala</option>
											<option value="Lakshadweep Islands" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Lakshadweep Islands"){echo 'selected';}}?>>Lakshadweep Islands</option>
											<option value="Madhya Pradesh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Madhya Pradesh"){echo 'selected';}}?>>Madhya Pradesh</option>
											<option value="Maharashtra" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Maharashtra"){echo 'selected';}}?>>Maharashtra</option>
											<option value="Manipur" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Manipur"){echo 'selected';}}?>>Manipur</option>
											<option value="Meghalaya" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Meghalaya"){echo 'selected';}}?>>Meghalaya</option>
											<option value="Mizoram" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Mizoram"){echo 'selected';}}?>>Mizoram</option>
											<option value="Nagaland" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Nagaland"){echo 'selected';}}?>>Nagaland</option>
											<option value="Odisha" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Odisha"){echo 'selected';}}?>>Odisha</option>
											<option value="Pondicherry" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Pondicherry"){echo 'selected';}}?>>Pondicherry</option>
											<option value="Punjab" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Punjab"){echo 'selected';}}?>>Punjab</option>
											<option value="Rajasthan" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Rajasthan"){echo 'selected';}}?>>Rajasthan</option>
											<option value="Sikkim" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Sikkim"){echo 'selected';}}?>>Sikkim</option>
											<option value="Tamil Nadu" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Tamil Nadu"){echo 'selected';}}?>>Tamil Nadu</option>
											<option value="Telangana" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Telangana"){echo 'selected';}}?>>Telangana</option>
											<option value="Tripura" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Tripura"){echo 'selected';}}?>>Tripura</option>
											<option value="Uttar Pradesh" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Uttar Pradesh"){echo 'selected';}}else{echo 'selected';}?>>Uttar Pradesh</option>
											<option value="Uttarakhand" <?php if(isset($_GET['id'])){if($row_edit['state'] == "Uttarakhand"){echo 'selected';}}?>>Uttarakhand</option>
											<option value="West Bengal" <?php if(isset($_GET['id'])){if($row_edit['state'] == "West Bengal"){echo 'selected';}}?>>West Bengal</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>PinCode *</td>
									<td>
										<input type="text" class="form-control" name="pincode" placeholder="Enter PinCode" 
										value="<?php if(isset($_GET['id'])){echo $row_edit['pincode'];}?>" tabindex="12"  required>
									</td>
								</tr>
								<tr>
									<td>Address *</td>
									<td>
										<textarea class="form-control"  name="address"  required value="" id="title" tabindex="14"><?php if(isset($_GET['id'])){echo $row_edit['address'];}?></textarea>
									</td>

								</tr>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-3">&nbsp;</div>
						<div class="col-sm-6 ">
							<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];}?>">
							<input type="submit" id="submit" name="submit" class="form-control" value="Submit" tabindex="15">
						</div>
						<div class="col-sm-3">&nbsp;</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="panel">
		<table class="table table-hover table-responsive table-bordered table-striped">
			<thead>
				<tr class="panel-heading">
					<th>Sno</th>
					<th>Company Name</th>
					<th>Owner Name</th>
					<th>Mobile Number</th>
					<th>Company Type</th>
					<th>Pan Num</th>
					<th>GST Num</th>
					<th>Email</th>
					<th>Address</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$n = 1;
			$sql ="SELECT * FROM `company_details`";	
			$query = mysqli_query($db,$sql);
			while($row=mysqli_fetch_array($query)){ 
				$sql_type = 'SELECT * FROM `company_type` WHERE `sno`="'.$row['company_type'].'"';
				$result_type = execute_query($sql_type);
				$row_type = mysqli_fetch_array($result_type);
				echo "<tr>";
				echo '<td>';
				echo $n++;
				echo '</td>';
				echo '<td>';
				echo $row["company_name"];
				echo '</td><td>'.$row['owner_name'].'</td><td>'.$row['mobile'].'</td>';
				echo '<td>'.$row_type['company_type_name'].'</td>';echo '<td>';
				echo $row["pan"];
				echo '</td>';echo '<td>';
				echo $row["gst"];
				echo '</td>';echo '<td>';
				echo $row["email"];
				echo '</td>';echo '<td>';
				echo $row["state"].'<br/>'.$row['pincode'].'<br/>'.$row['address'];
				echo '</td>';
				echo '<td>';
				echo'<a href="payroll_admin_company.php?id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a>';
				echo '</td>';
				echo '<td>';
				echo'<a href="payroll_admin_company.php?del='.$row["sno"].'"><i class="glyphicon glyphicon-remove"></i></a>';
				echo'</td>';
				echo '</tr>';
			}
			?>
			</tbody>
		</table>
	</div>
</div>
<?php
page_footer();
?>
	