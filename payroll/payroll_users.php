<?php
include("scripts/settings.php");
$msg='';
$tab=1;
$response=1;
page_header_start('Users Master');
if(!isset($_REQUEST['cid'])){
	$_REQUEST['cid']=1;
}

if(isset($_GET['id'])){
	$response=2;
}
elseif(isset($_GET['new'])){
	$response=3;
}
else{
	$response=1;
}

if(isset($_POST['submit'])){
	$sql ='select * from users where sno ="'.$_POST['user'].'"';
	$user = mysqli_fetch_array(execute_query($sql));
	
	if($_POST['user']!=1 and $_SESSION['usersno'] == 1){
		$sql='delete from user_access where user_id="'.$user['sno'].'"';
		execute_query($sql);
		if(mysqli_error($db)){
			$msg .= '<li>Error # 1 : '.mysqli_error($db).' >> '.$sql;
		}
		$sql = 'select * from navigation';
		$result=execute_query($sql);
		while($nav=mysqli_fetch_array($result)){
			$check1='check_'.$nav['sno'];
			if(isset($_POST[$check1])){
				$sql='INSERT INTO `user_access`(`user_id`, `file_name`, `created_by`, `creation_time`) 
				VALUES("'.$user['sno'].'","'.$nav['sno'].'", "'.$_SESSION['username'].'","'.date("Y-m-d H:i:s").'")';
				execute_query($sql);
			}
		}
	}
	
	$sql='update users set 
	userid = "'.$_POST['user_id'].'", 
	pwd ="'.$_POST['user_pass'].'", 
	user_name="'.$_POST['user_name'].'", 
	father_name="'.$_POST['father_name'].'", 
	address="'.$_POST['address'].'", 
	mobile="'.$_POST['mobile'].'" 
	where sno ="'.$user['sno'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<li>Error # 4 : '.mysqli_error($db).' >> '.$sql;
	}

	if($msg==''){
		$msg .= '<li>Successful</li>';
	}
	else{
		$msg .= '<li>Please insert user detail correctly</li>';
	}
}

if(isset($_POST['create_new'])){
	if($_POST['user_pass']!==''){
		$sql='select * from users where userid = "'.$_POST['user_id'].'"';
		$user = execute_query($sql);
		if(mysqli_num_rows($user)==0) {
			$sql='INSERT INTO `users`(`userid`,`pwd`,`type`, user_name, father_name, address, mobile) VALUES 
			("'.$_POST['user_id'].'","'.$_POST['user_pass'].'",1, "'.$_POST['user_name'].'", "'.$_POST['father_name'].'", "'.$_POST['address'].'", "'.$_POST['mobile'].'")';
			execute_query($sql);
			$user_sno = mysqli_insert_id($db);
			
			$sql = 'select * from navigation';
			$result=execute_query($sql);
			while($nav=mysqli_fetch_array($result)){
				$check1='check_'.$nav['sno'];
				if(isset($_POST[$check1])){
					$sql='INSERT INTO `user_access`(`user_id`, `file_name`, `created_by`, `creation_time`) 
					VALUES("'.$user_sno.'","'.$nav['sno'].'", "'.$_SESSION['username'].'","'.date("Y-m-d H:i:s").'")';
					execute_query($sql);
				}
			}
			$msg .= '<li>New User Created</li>';
			$response=1;
		}
	    else {
		   $msg .=  '<li>User already exist</li>';
			$response=3;
		}
	}
	else{
		$msg .=  '<li>Please insert user detail correctly</li>';
		$response=3;
	}
}

if(isset($_REQUEST['del'])){
	if($_REQUEST['del'] != 1){
			$sql='select * from users where sno='.$_REQUEST['del'];
			$s_inv=mysqli_fetch_array(execute_query($sql));
			// $sql = "delete from user_access_detail where user_id=".$_REQUEST['del'];
			// execute_query($sql);
			// $sql = "delete from user_stock_access where user_id=".$_REQUEST['del'];
			// execute_query($sql);
		    // $sql = "delete from user_store_access where user_id=".$_REQUEST['del'];
			// execute_query($sql);
			$sql = "delete from users where sno=".$_GET['del'];
			execute_query($sql);
			
			$sql = "delete from user_access where user_id=".$_GET['del'];
			execute_query($sql);
			echo '<script>alert("Record deleted.")</script>';
	}
	else{
		echo '<script>alert("Permission Not Granted.")</script>';
	}	

}

page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
<?php
switch($response) {
	case 1:{
?>
	
	<div class="container-fluid">
		<div class="page-header">
			<h1>Users <?php if($_SESSION['usersno'] == 1){ ?> <small>(<a href="payroll_users.php?new=true">Create New User</a>)</small><?php } ?></h1>
		</div>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
			<table class="table table-hover table-responsive table-bordered table-striped">
				<tr>
					<td colspan="8"><ul><?php echo $msg; ?></ul></td>
				</tr>
				<tr>
					<th>Sno</th>
					<th>User ID</th>
					<th>User Name</th>
					<th>Father Name</th>
					<th>Address</th>
					<th>Mobile</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
               		<?php
						$_SESSION['sql_result_filter'] = "select * from users order by sno";
						$result = execute_query($_SESSION['sql_result_filter']);
						$i=1;
						$tot=0;
						while($row_invoice = mysqli_fetch_array($result)) {
							echo '
							<tr>
							<th>'.($i++).'</th>
							<td>'.$row_invoice['userid'].'</td>
							<td>'.$row_invoice['user_name'].'</td>
							<td>'.$row_invoice['father_name'].'</td>
							<td>'.$row_invoice['address'].'</td>
							<td>'.$row_invoice['mobile'].'</td>';
							if($row_invoice['sno'] == $_SESSION['usersno'] or $_SESSION['usersno'] == 1){
								echo '<td><a href="payroll_users.php?id='.$row_invoice['sno'].'"><span class="glyphicon glyphicon-edit" aria-hidden="true" data-toggle="tooltip" title="Edit"></span></a></td>';
								if($row_invoice['sno'] != 1){
							echo '<td><a href="'.$_SERVER['PHP_SELF'].'?del='.$row_invoice['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="glyphicon glyphicon-trash" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a></td>';
								}
								else{
									echo '<td>&nbsp;</td>';
								}
							}
							else{
								echo '<td>&nbsp;</td><td>&nbsp;</td>';
							}
							echo '</tr>';
						}		
					?>
                </table>
			</form>
		</div>
<?php
     break;
  }
  case 2: {
	$sql='select * from users where sno='.$_REQUEST['id'];
	$sale=mysqli_fetch_array(execute_query($sql));
	$sql='select * from user_access where user_id = "'.$sale['sno'].'"';
	$user = mysqli_fetch_array(execute_query($sql));
	// $tab=CURL_HTTP_VERSION_1_0;
	  
?>
	<div class="container-fluid">
		<div class="page-header">
			<h1>Users <?php if($_SESSION['usersno'] == 1){ ?> <small>(<a href="payroll_users.php?new=true">Create New User</a>)</small><?php } ?></h1>
		</div>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
			<table class="table table-hover table-responsive table-bordered table-striped">
				<tr>
					<td colspan="2"><ul><?php echo $msg; ?></ul></td>
				</tr>
				<tr>	
					<td>User ID</td>
					<td><input id="user_id" name="user_id" value="<?php echo $sale['userid']; ?>" class="fieldtextmedium" required maxlength="25" tabindex="1" type="text" <?php if($sale['sno']==1){echo 'readonly="readonly"';}?>/></td>
				</tr>
				<tr>
					<td>Password</td>
					<td><input id="user_pass" name="user_pass" value="<?php echo $sale['pwd']; ?>" class="fieldtextmedium" required maxlength="25" tabindex="2" type="text"/></td>
				</tr>
				<tr>
					<td>User Name</td>
					<td><input id="user_name" name="user_name" value="<?php echo $sale['user_name']; ?>" class="fieldtextmedium" maxlength="25" tabindex="3" type="text"/></td>
				</tr>
				<tr>
					<td>Father Name</td>
					<td><input id="father_name" name="father_name" value="<?php echo $sale['father_name']; ?>" class="fieldtextmedium" maxlength="25" tabindex="4" type="text"/></td>
				</tr>
				<tr>
					<td>Address</td>
					<td><input id="address" name="address" value="<?php echo $sale['address']; ?>" class="fieldtextmedium" maxlength="25" tabindex="5" type="text"/></td>
				</tr>
				<tr>
					<td>Mobile</td>
					<td><input id="mobile" name="mobile" value="<?php echo $sale['mobile']; ?>" class="fieldtextmedium" maxlength="25" tabindex="6" type="text"/></td>
				</tr>
			</table>
  				<?php if($_GET['id']!=1 and $_SESSION['usersno'] == 1){?>
   				<h2>User Accsses</h2>
				<table class="table table-hover table-responsive table-bordered table-striped">
					<tr>
						<th>Module Access</th>
					</tr>
					<tr>
						<td width="100%">
							<table class="table table-hover table-responsive table-bordered table-striped">
								<?php
								$sql='select * from navigation where parent in ("") order by link_description';
								$new = execute_query($sql);
								$i=1;
								while($row = mysqli_fetch_array($new)){
									$sql = 'select * from user_access where user_id="'.$sale['sno'].'" and file_name="'.$row['sno'].'"';
									$result_access = execute_query($sql);
									if(mysqli_num_rows($result_access)==1){
										$selected = 'checked="checked"';

									}
									else{
										$selected = '';
									}
									echo '<tr>
									<td>'.$row['link_description'].'</td>
									<td><input type="checkbox"  name="check_'.$row['sno'].'" value="" tabindex="'.$tab++.'" '.$selected.'><input type="hidden" name="id" id="id" value="'.$i++.'"></td>
									</tr>';
								}

								$sql='select * from navigation where parent in ("P") order by link_description';
								$new = execute_query($sql);
								$i=1;
								while($row = mysqli_fetch_array($new)){
									echo '<tr><th colspan="2">'.$row['link_description'].'</th></tr>';
									$sql = 'select * from navigation where parent in ('.$row['sno'].') order by link_description';
									$res_sub_menu = execute_query($sql);
									while($row_sub_menu = mysqli_fetch_array($res_sub_menu)){
										$sql = 'select * from user_access where user_id="'.$sale['sno'].'" and file_name="'.$row_sub_menu['sno'].'"';
										$result_access = execute_query($sql);
										//echo $sql.'<br>';
										if(mysqli_num_rows($result_access)==1){
											$selected = 'checked="checked"';

										}
										else{
											$selected = '';
										}
										echo '<tr>
										<td>'.$row_sub_menu['link_description'].'</td>
										<td><input type="checkbox"  name="check_'.$row_sub_menu['sno'].'" value="" tabindex="'.$tab++.'" '.$selected.'><input type="hidden" name="id" id="id" value="'.$i++.'">
										</tr>';

									}
								}

								?>
							</table>
						</td>
					</tr> 
				</table>
               <?php } ?>
                <input type="hidden" value="<?php echo $_GET['id'] ?>" id="user" name="user">
                <input id="save" name="submit" class="submit" type="submit" value="Submit" tabindex="10000">
			</form> 
	</div>
<?php
		  break;
  }
	case 3:{
?>
	<div class="container-fluid">
		<div class="page-header">
			<h1>New User <small>(<a href="users.php">View Users</a>)</small></h1>
		</div>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
			<table class="table table-hover table-responsive table-bordered table-striped">
				<td colspan="2"><ul><?php echo $msg; ?></ul></td>
				<tr>
					<td>User ID</td>
					<td><input id="user_id" name="user_id"  class="fieldtextmedium" required maxlength="25" tabindex="<?php echo $tab++; ?>" type="text"/></td>
				</tr>
				<tr>	
					<td>User Password</td>
					<td><input id="user_pass" name="user_pass"  class="fieldtextmedium" required maxlength="25" tabindex="<?php echo $tab++; ?>" type="text"/></td>
				</tr>
				<tr>
					<td>User Name</td>
					<td><input id="user_name" name="user_name"  class="fieldtextmedium" maxlength="25" tabindex="<?php echo $tab++; ?>" type="text"/></td>
				</tr>
				<tr>
					<td>Father Name</td>
					<td><input id="father_name" name="father_name"  class="fieldtextmedium" maxlength="25" tabindex="<?php echo $tab++; ?>" type="text"/></td>
				</tr>
				<tr>
					<td>Address</td>
					<td><input id="address" name="address"  class="fieldtextmedium" maxlength="25" tabindex="<?php echo $tab++; ?>" type="text"/></td>
				</tr>
				<tr>
					<td>Mobile</td>
					<td><input id="mobile" name="mobile"  class="fieldtextmedium" maxlength="25" tabindex="<?php echo $tab++; ?>" type="text"/></td>
				</tr>
			</table>
			<h2>User Access</h2>
			<table class="table table-hover table-responsive table-bordered table-striped">
				<tr>
					<th>Module Access</th>
				</tr>
				<tr>
					<td width="100%">
						<table class="table table-hover table-responsive table-bordered table-striped">
							<?php
							$sql='select * from navigation where parent in ("") order by link_description';
							$new = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_array($new)){
								echo '<tr>
								<td>'.$row['link_description'].'</td>
								<td><input type="checkbox"  name="check_'.$row['sno'].'" value="" tabindex="'.$tab++.'"></td>
								<input type="hidden" name="id" id="id" value="'.$i++.'">
								</tr>';
							}

							$sql='select * from navigation where parent in ("P") order by link_description';
							$new = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_array($new)){
								echo '<tr><th colspan="2">'.$row['link_description'].'</th></tr>';
								$sql = 'select * from navigation where parent in ('.$row['sno'].') order by link_description';
								$res_sub_menu = execute_query($sql);
								while($row_sub_menu = mysqli_fetch_array($res_sub_menu)){
									echo '<tr>
									<td>'.$row_sub_menu['link_description'].'</td>
									<td><input type="checkbox"  name="check_'.$row_sub_menu['sno'].'" value="" tabindex="'.$tab++.'"></td>
									<input type="hidden" name="id" id="id" value="'.$i++.'">
									</tr>';

								}
							}

							?>
						</table>
					</td>
				</tr> 
			</table>
			<input id="save" name="create_new" class="submit" type="submit" value="Submit" tabindex="10000">
		</form>
	</div>
<?php
	break;
}
}
?>
<?php
page_footer();
?>
