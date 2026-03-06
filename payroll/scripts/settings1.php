<?php
session_start();
date_default_timezone_set("Asia/Calcutta");
if(!isset($_SESSION['username']) && !isset($_SESSION['usersno'])){
    header("location:index.php");
}
//set_time_limit(0);
//error_reporting(0);
error_reporting(E_ALL);
include('settings_dbase.php');
function page_header_start($title){
?>
<!DOCTYPE html>
<html lang="en" style="min-height:100%;"><head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
 	<link rel="stylesheet" href="jQuery/jquery-ui.css">
 	
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/mycss.css">
	<link rel="stylesheet" href="css/systemcss.css">
	<script src="jQuery/jquery.min.js"></script>
	<script src="jQuery/jquery-ui.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/calendar.js"></script>
	<title><?php echo $title; ?></title>

<?php	
}

function page_header_end(){
?>
</head>
<body style="">
<?php
}

function navigation($active){
?>
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container-fluid">

			<!--<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#"><img src="images/logo-15.png" class="img-responsive">WeKnow</a>
			</div>-->
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav" style="font-size: 14px;">
					
					<li><a href="payroll_homepage.php"><img src="images/icons/home-30.png" class="img-rounded">&nbsp;<span style="color: red;"><b>HOME</b></span></a></li>
				<?php
					$sql = 'select * from navigation where parent in ("P", "") order by `priority`';
					$result = execute_query($sql);
					while($row = mysqli_fetch_array($result)){
						$sql_user_access = 'SELECT * FROM `user_access` WHERE `user_id`="'.$_SESSION['usersno'].'" AND `file_name`="'.$row['sno'].'"';
						$result_user_access = execute_query($sql_user_access);
						if(mysqli_num_rows($result_user_access) != 0 or $_SESSION['username'] == 'sadmin' or $row['parent'] == 'P'){
						$caret = '';
						$dropdown = '';
						$drop_link = '';
						echo '
						<li';
						if($active==$row['hyper_link']){
							echo '  class="active" ';
						}
						if($row['parent']=="P"){
							echo ' class="dropdown" ';
							$drop_link = 'data-toggle="dropdown" class="dropdown-toggle"';
							$caret = '<b class="caret"></b>';
							$sql = 'select * from navigation where parent="'.$row['sno'].'" order by `priority`';
							$result_dropdown = execute_query($sql);
							$dropdown = '<ul class="dropdown-menu" style="font-size: 12px;">';
							while($row_dropdown = mysqli_fetch_array($result_dropdown)){
									$sql_user_access = 'SELECT * FROM `user_access` WHERE `user_id`="'.$_SESSION['usersno'].'" AND `file_name`="'.$row_dropdown['sno'].'"';
									$result_user_access = execute_query($sql_user_access);
									if(mysqli_num_rows($result_user_access) != 0 or $_SESSION['username'] == 'sadmin'){

										$dropdown .=  '
										<li ><a href="'.$row_dropdown['hyper_link'].'" style="font-size:14px;">'.$row_dropdown['link_description'].'</a></li>';
									}
							}
							$dropdown .= '</ul>';
						}
						echo '>
							<a href="'.$row['hyper_link'].'" '.$drop_link.'><img src="images/icons/'.$row['icon_image'].'" class="img-rounded">&nbsp;<span style="font-size:15px;">'.$row['link_description'].'</span> '.$caret.'</a>
							'.$dropdown.'
						</li>
						';
						}
					}
				?>
					
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li style="float: right;"><a href="index.php?logout" style="font-size: 20px;color: red;"><span class="glyphicon glyphicon-log-out"></span>&nbsp;<span style="font-size:15px;">Logout</span></a></li>
				</ul>
			</div><!--/.nav-collapse -->

		</div>
	</nav>

<?php
}

function page_footer(){
?>


<div class="col-lg-12 text-center no-print">
	<a href="http://www.weknowtech.in" target="_blank"><img src="images/logo-16.png" class="img-rounded"> Weknow Technologies</a><br/>
	<?php 
		$sql = 'SELECT `sno` FROM `session` ORDER BY `sno` DESC LIMIT 1 ';
		$result = execute_query($sql);
		$row = mysqli_fetch_array($result);

		$sql_session = 'SELECT * FROM `session` WHERE ';
		if($_SESSION['second_password'] == "aa"){
			$sql_session .=' `sno` < "'.$row['sno'].'" ';
		}
		if($_SESSION['second_password'] == "vik@babu"){
			$sql_session .=' `sno` = "'.$row['sno'].'" ';
		}
		
		$sql_session .=' ORDER BY `sno` DESC LIMIT 1 ';
		$result_session = execute_query($sql_session);
		$row_session = mysqli_fetch_array($result_session);
		//echo $sql_session;
		echo '<span style="font-size:20px;"><b>Last Login:'.$row_session['user'].'<br/>'.date("d-m-Y h:i:sa", strtotime($row_session['s_start_date'])).'</b></span>';

	?>
</div>
</body>
</html>
<?php
}
function employee_name($id){
	$sql = 'select * from employee where sno="'.$id.'"';
	$result = execute_query($sql);
	$row = mysqli_fetch_array($result);
	return $row['employee_name'];
}

function head_name($id){
	$sql = 'select * from head_type where sno="'.$id.'"';
	$result = execute_query($sql);
	$row = mysqli_fetch_array($result);
	return $row['head_name'];
}

function employee_details($id){
	$sql = 'select * from employee where sno="'.$id.'"';
	$result = execute_query($sql);
	$row = mysqli_fetch_array($result);
	return $row;
}
function get_cust_balance($from,$to,$id){
		$to=date("Y-m-d", strtotime($to));
		$sql_trans = 'select sum(amount) as trans from transactions where timestamp<"'.$from.'" and type in ("payment", "deduction") and emp_id='.$id;
		$dr_trans = mysqli_fetch_array(execute_query($sql_trans));
	
		$sql_trans = 'select sum(amount) as trans from transactions where timestamp<"'.$from.'" and type in ("expense","payslip", "due", "incentive") and  emp_id='.$id;
		$cr_trans = mysqli_fetch_array(execute_query($sql_trans));

		$cust_opening = $dr_trans['trans'] - $cr_trans['trans'];
		
		$sql_trans = 'select sum(amount) as trans from transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type in ("payment", "deduction") and emp_id='.$id;
		$dr_trans = mysqli_fetch_array(execute_query($sql_trans));

		$sql_trans = 'select sum(amount) as trans from transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type in ("expense","payslip", "due", "incentive") and emp_id='.$id;
		$cr_trans = mysqli_fetch_array(execute_query($sql_trans));
        
		$cust_balanace = $cust_opening+$dr_trans['trans'] - $cr_trans['trans'];
		return $cust_balanace;
}
?>