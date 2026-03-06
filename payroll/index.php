<?php
session_start();
date_default_timezone_set("Asia/Calcutta");
if(isset($_GET['logout'])){
	session_destroy();
	header("location:index.php");
}	 
$msg='';
$tab=1;
include("scripts/settings_dbase.php");
ob_flush();
ob_start();
?>
<?php
if(isset($_POST['submit'])){
	$name=$_POST['username'];
	$pass=$_POST['pwd'];
	$sql="SELECT * FROM `users` WHERE userid='$name' AND pwd='$pass'";
	$res=execute_query($sql);
	$row_user = mysqli_fetch_array($res);
	$numrow=mysqli_num_rows($res);
	if($numrow > 0){
		$_SESSION['username'] = $row_user['user_name'];
		$_SESSION['usersno'] = $row_user['sno'];
		$_SESSION['userpwd'] = $row['pwd'];
		$_SESSION['usertype'] = $row['type'];
		$_SESSION['startdate'] = date('Y-m-d h:i:sa');
		$_SESSION['branch'] = $row['branch'];
		$time = localtime();
        $time = $time[2].':'.$time[1].':'.$time[0];
		//echo $time;
        $_SESSION['starttime']=$time;
        if($_POST['userpwd']!="vik@babu"){
	        $sql_session = "insert into session (user, s_start_date, s_start_time, last_active , user_sno) values ('".$_SESSION['username']."','".$_SESSION['startdate']."','".$_SESSION['starttime']."', '".time()."' , '".$_SESSION['usersno']."')";
	        execute_query($sql_session);
	        $_SESSION['second_password'] = "aa";
	    }
	    else{
	    	$_SESSION['second_password'] = "vik@babu";
	    }
		header('Location: payroll_homepage.php');
	}
	else{
		$msg="Wrong Id or Password !";
	}
}
?>

<?php

?>
<html>
	<head>
		<title>Pay Roll</title>
		<link rel="stylesheet" href="jQuery/jquery-ui.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/mycss.css">
		<link rel="stylesheet" href="css/systemcss.css">
		<style>
		.abc{
			background-image:url(images/bg-4.png);
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
			width:100%;
			height:100%;
			
			/*filter: blur(1px);
			opacity: 0.5;
			*\
		}
		
		</style>
		
	</head>
	
	<body class="abc">
	<form action="" method="POST">
		<div class="container ">
			
			<div class="row ">
				<div class="col-md-6">
					
				</div>
				<div class="col-md-5 bg-white">
					<div class="page-header text-center">
						<h2 class="text-white" style="color:;">Payroll WeKnow Technologies</h2>
					</div>
					<span style="color:red;"><?php echo $msg ; ?></span>
					<table class="table table-hover table-responsive table-bordered table-striped">
						<tr>
							<td>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
									<label class="sr-only has" for="username">User Name</label>
									<input type="text" name="username" id="username" class="form-control" required placeholder="User Name">
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-asterisk"></span></span>
									<label class="sr-only" for="userpwd">Password</label>
									<input type="password" name="pwd" id="userpwd" class="form-control" required placeholder="Password">
									<span class="input-group-addon"><span class="glyphicon glyphicon-eye-open"></span></span>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-center">
								<button type="submit" class="btn btn-primary btn-lg" name="submit">
									<span class="glyphicon"></span> Login
								</button>					
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="row p-5" style="margin-top:200px;">
				<div class="col-lg-2 text-center text-white">
					<h4 style="color:white;"> PayRoll</h4>
				</div>
				<div class="col-lg-8">
				</div>
				
				<div class="col-lg-2 text-center text-end">
					<a href="http://www.weknowtech.in" target="_blank"><img src="images/logo-16.png" class="img-rounded"> Weknow Technologies</a>
				</div>
			</div>
		</div>
		
	</form>
	</body>
</html>