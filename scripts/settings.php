<?php
if (session_status() === PHP_SESSION_NONE && !defined('AJAX_CALL')) {
	session_cache_limiter('nocache');
	session_start();
}
$time_track = array();
$time_track[] = microtime(true);
set_time_limit(0);
error_reporting(E_ALL);
/*if($_SERVER["HTTPS"] != "on")
{
	header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	exit();
}*/
?>
<?php
include("settings_dbase.php");
//include("settings_dbase_payroll_for_erp.php");

$sms_result = execute_query("select * from general_settings where `desc`='sms_user'");
if ($sms_result) {
	$sms_user = mysqli_fetch_array($sms_result);
	$sms_user = $sms_user['rate'];
} else {
	$sms_user = '';
}

$sms_pwd_result = execute_query("select * from general_settings where `desc`='sms_password'");
if ($sms_pwd_result) {
	$sms_pwd = mysqli_fetch_array($sms_pwd_result);
	$sms_pwd = $sms_pwd['rate'];
} else {
	$sms_pwd = '';
}


sethistory();
date_default_timezone_set('Asia/Calcutta');

$company_result = execute_query("select * from general_settings where `desc`='company'");
if ($company_result) {
	$company_name = mysqli_fetch_array($company_result);
	$company_name = $company_name['rate'];
} else {
	$company_name = 'UPRNSS';
}

$software_result = execute_query("select * from general_settings where `desc`='software_type'");
if ($software_result) {
	$software_type = mysqli_fetch_array($software_result);
	$software_type = $software_type['rate'];
} else {
	$software_type = 'ERP';
}

$mobile_result = execute_query("select * from general_settings where `desc`='mobile'");
if ($mobile_result) {
	$mobile = mysqli_fetch_array($mobile_result);
	$mobile = $mobile['rate'];
} else {
	$mobile = '';
}

$state_result = execute_query("select * from general_settings where `desc`='state'");
if ($state_result) {
	$state = mysqli_fetch_array($state_result);
	$state = abs($state['rate']);
} else {
	$state = 0;
}

if (!function_exists('dbconnect')) {
	function dbconnect()
	{
		global $db;
		return $db;
	}
}

function page_header_start($title = 'UPRNSS|AIPPCA')
{
	global $software_type;
	global $time_track;
	$time_track[] = microtime(true);
	$sql = 'select * from general_settings where `desc`="company"';
	$company = mysqli_fetch_array(execute_query($sql));
	$company = explode(" ", $company['rate']);
	$company_name = '';
	foreach ($company as $k => $v) {
		$company_name .= '<span>' . substr($v, 0, 1) . '</span>' . substr($v, 1) . ' ';
		//echo $v.'<br>';
	}
	$current_file_name = basename($_SERVER['PHP_SELF']);
	$GLOBALS['title'] = 'UPRNSS ERP'; // Default title
	if (isset($_SESSION['session_id'])) {
		$sql = 'select * from navigation where hyper_link="' . $current_file_name . '"';
		$file = mysqli_fetch_array(execute_query($sql));
		if ($file) {
			logvalidate($file['sno']);
			$title = $file['link_description'];
			$GLOBALS['title'] = $title ?? 'UPRNSS ERP';
		} else {
			logvalidate();
		}
	} else {
		$file['color'] = '';
		logvalidate();
	}
	global $time_track;
	$time_track[] = microtime(true);
	$time_track[] = microtime(true);
	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>' . $title . '</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico">

	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta name="viewport" content="width=device-width" />
	
    <!--     Fonts and icons     -->
    <link href="fa/css/all.min.css" rel="stylesheet" media="screen">
    <script src="fa/js/fontawesome.min.js"></script>
    <link href="css/pe-icon-7-stroke.css" rel="stylesheet" media="screen" />

    <!-- Bootstrap core CSS     -->
    <link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css" media="screen">
	<link rel="stylesheet" href="dataTables/datatables.min.css" media="screen">
	<script src="js/jquery.3.2.1.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui.js" type="text/javascript"></script>
	<script src="js/popper.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-switch.js"></script>
	<script src="js/calendar.js" language="javascript" type="text/javascript"></script>
	<script src="js/bpopup.js" language="javascript" type="text/javascript"></script>
	<script src="jquery/jquery.ba-throttle-debouce.min.js" type="text/javascript"></script>
	<script src="jquery/jquery.multiselect.js" language="javascript"></script>

    <!-- Animation library for notifications   -->
    <link href="css/animate.min.css" rel="stylesheet" media="screen"/>

    
    <link href="css/jquery-ui.css" rel="stylesheet"  media="screen"/>
	<!--
	<!--<link href="css/component.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="css/jcarousel.css" rel="stylesheet" type="text/css" media="screen" />-->
	<link href="css/styles.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="css/pagination.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="css/jquery.multiselect.css" rel="stylesheet" type="text/css" media="screen" />
	<!--  Light Bootstrap Table core CSS    -->
	<link href="css/light-bootstrap-dashboard.css?v=1.4.0" rel="stylesheet" media="screen"/>
	<link href="css/print_sheet.css" rel="stylesheet" media="print"/>


    <style type="text/css">
        .nav_style li:hover{
            background-color: #b2b8ae;
        }
        
       body {
    text-transform: "Segoe UI", Tahoma, sans-serif;
}
        
        /* 🎨 UPRNSS Sidebar Matching Theme */
        :root {
            --red-light: #e53935;
            --red-dark: #b71c1c;
            --yellow: #f6b800;
            --green: #008b00;
            --soft-bg: #fff8f6;
        }


        /* Cards */
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            background: #fff;
            margin-bottom: 25px;
        }

        .card-custom .card-header {
            background: linear-gradient(45deg, var(--red-light), var(--red-dark));
            color: white;
            font-weight: 600;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 12px 18px;
            letter-spacing: 0.5px;
        }

        /* Form controls */
        .form-select, .form-control {
            border-radius: 8px;
        }

        /* Primary button */
        .btn-primary {
            background: linear-gradient(45deg, var(--red-light), var(--red-dark));
            border: none;
            color: #fff;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--red-dark), var(--yellow));
            transform: scale(1.03);
        }

        /* Success (Print) button */
        .btn-success {
            background: linear-gradient(45deg, var(--yellow), var(--red-light));
            border: none;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, var(--red-dark), var(--yellow));
        }

        /* Table Styling */
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        table thead th {
            background: linear-gradient(45deg, var(--red-light), var(--red-dark));
            text-align: center;
            vertical-align: middle;
            font-size: 14px !important;
            font-weight: 700 !important;
            white-space: nowrap;
            color: white !important;
            text-transform: capitalize !important;
        }

        table tbody td {
            font-size: 13px !important;
            font-weight: 600 !important;
            text-align: center;
            vertical-align: middle;
            text-transform: capitalize !important;
        }

        table tr:nth-child(even) {
            background-color: #fff5f5;
        }

        table tr:hover {
            background-color: #ffeaea;
        }

        .sticky-header thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        /* GLOBAL PERFECT CENTER ALIGN FOR ALL INPUTS & SELECTS */
        input.form-control,
        select.form-select,
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select {
            height: 40px !important;
            text-align: center !important;
            padding: 5px 10px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            text-transform: none !important;
        }

        /* For placeholder center */
        input::placeholder,
        select::placeholder {
            text-align: center !important;
        }

        /* Form labels */
        .form-label, label {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #333 !important;
            text-transform: capitalize !important;
        }

        /* Card titles */
        .card-title, h1, h2, h3, h4, h5, h6 {
            font-size: 18px !important;
            font-weight: 700 !important;
            text-transform: capitalize!important;
        }

        /* Button text */
        .btn {
            font-size: 14px !important;
            font-weight: 600 !important;
            text-transform: capitalize !important;
        }

        /* Sidebar always on top */
        .sidebar {
            position: fixed;
            z-index: 9999 !important;
        }

        /* Your fixed TH behind sidebar */
        table th {
            position: sticky;
            top: 0;
            z-index: 10 !important;
        }

        /* Print mode */
    </style>
    <style type="text/css">
        .daterclass{
            padding: 5px;
            border: 2px solid lightblue;
        }
        .daterclass tr td{
          
        }
    </style>
	<style>
		@media print {
			body::after {
				content: "Powered By AIPPCA";
				position: fixed;
				font-size: 50px;
				color: rgba(0, 0, 0, 0.1);
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%) rotate(-45deg);
				z-index: -2;
			}
		}
	</style>
	<script type="text/javascript" language="javascript">
		var software_type="' . $software_type . '";
		$(document).ready( 
			function() {
				// Add the "focus" value to class attribute
				$("input").focusin( 
					function() {
						$(this).addClass("focus");
					}
				);
				$("select").focusin( 
					function() {
						$(this).addClass("focus");
					}
				);
				$(":checkbox").focusin( 
					function() {
						$(this).addClass("focus");
					}
				);
				// Remove the "focus" value to class attribute
				$("input").focusout( 
					function() {
						$(this).removeClass("focus");
					}
				);
				$("select").focusout( 
					function() {
						$(this).removeClass("focus");
					}
				);
				$(":checkbox").focusout( 
					function() {
						$(this).removeClass("focus");
					}
				);
				$(\'[data-toggle="tooltip"]\').tooltip(); 
			}
		);

		$(function() {
			var options = {
				source: function (request, response){
					$.getJSON("scripts/ajax.php?id=nav",request, response);
				},
				position: {
					my: "left top",
					at: "left bottom",
					collision: "flip"
				},
				minLength: 1,
				select: function( event, ui ) {
					log( ui.item ?
						"Selected: " + ui.item.value + " aka " + ui.item.label :
						"Nothing selected, input was " + this.value );
				},
				select: function( event, ui ) {
					window.open(ui.item.hyper_link, "_self");
					return false;
				}
			};
		$("input#shortcut_command").on("keydown.autocomplete", function() {
			$(this).autocomplete(options);
		});
		});


	</script>
	<script language="javascript" type="text/javascript">
		function check_prev_date(form_date){
			calculate_total(1);
			var cur_date = "' . date("Y-m-d") . '";
			var warn = 0;
			$(".noblank").each(function(index, element){
				if($(element).val()==""){
					$( element ).css( "backgroundColor", "yellow" );
					warn = 1;
				}
				else{
					$( element ).css( "backgroundColor", "white" );
				}
			});
			if(warn!=0){
				alert("Please enter all complusory blocks");
				return false;
			}

			if(cur_date>form_date){
				var response = confirm("Entry date is old than today. Do you want to proceed. ?");
			}
			else{
				var response = confirm("Are you sure?");
			}
			return response;
		}
	</script>
	<link href="jquery/jquery-ui.css" rel="stylesheet" type="text/css" media="screen" />
	<style>
		#wrapper{
			border: 5px solid #' . ($file['color'] ?? '#ccc') . ';
		}
	</style>';
	?>
		<script>
		$(".dropdown dt a").on('click', function() {
			$(".dropdown dd ul").slideToggle('fast');
		});

		$(".dropdown dd ul li a").on('click', function() {
			$(".dropdown dd ul").hide();
		});

		function getSelectedValue(id) {
		  return $("#" + id).find("dt a span.value").html();
		}

		$(document).bind('click', function(e) {
		  var $clicked = $(e.target);
		  if (!$clicked.parents().hasClass("dropdown")) $(".dropdown dd ul").hide();
		});

		$('.mutliSelect input[type="checkbox"]').on('click', function() {

		  var title = $(this).closest('.mutliSelect').find('input[type="checkbox"]').val(),
			title = $(this).val() + ",";

		  if ($(this).is(':checked')) {
			var html = '<span title="' + title + '">' + title + '</span>';
			$('.multiSel').append(html);
			$(".hida").hide();
		  } else {
			$('span[title="' + title + '"]').remove();
			var ret = $(".hida");
			$('.dropdown dt a').append(ret);

		  }
		});		
		

	// defining flags
	var isCtrl = false;
	var isAlt = false;
	// helpful function that outputs to the container
	// the magic :)

	<?php
	$current_file_name = basename($_SERVER['PHP_SELF']);
	if (isset($_SESSION['username'])) {
		$user = $_SESSION['username'];
		$sql = 'select * from session where user="' . $user . '" order by s_start_date desc, s_start_time desc';
		$last = execute_query($sql);
		if ($last && mysqli_num_rows($last) != 0) {
			$last = mysqli_fetch_array($last);
			$last = $last['s_start_date'] . ' ' . $last['s_start_time'];
		} else {
			$last = '';
		}
		$sql = 'select * from general_settings where `desc`="session_timeout"';
		$timeout = mysqli_fetch_array(execute_query($sql));
		if ($timeout['rate'] > 0) {
			$timeout = $timeout['rate'] * 60;
			$difference = time() - $timeout;
			$sql = 'select * from session where user!="' . $_SESSION['username'] . '" and last_active>' . $difference;
			$session = execute_query($sql);
			if ($session && mysqli_num_rows($session) != 0) {
				$other = mysqli_num_rows($session);
			} else {
				$other = 0;
			}
		}

	} else {
		$user = 'Guest';
		$last = '';
		$other = '';
	}

	if ($current_file_name == 'index.php') {
		?>
					$(document).ready(function(){

						demo.initChartist();

						$.notify({
							icon: 'pe-7s-gift',
							message: "Welcome <b><?php echo $user; ?></b>. Last Login: <b><?php echo $last; ?></b>. Currently active at <b><?php echo $other; ?></b> other location"

						},{
							type: 'success',
							timer: 2000
						});

					});

			<?php
	}
	?>
		</script>

	<?php
}


function page_header_end()
{
	$current_file_name = basename($_SERVER['PHP_SELF']);
	if ($current_file_name != 'index.php') {
		$class = 'sidebar-mini';
	} else {
		$class = '';
	}
	echo '
</head>

<body class="' . $class . '">
    <div class="wrapper">';
	$time_track[] = microtime(true);
}
function page_sidebar($id = '')
{
	?>	
	<?php
	echo $_SESSION['usertype'];
	if ($_SESSION['usertype'] == 'sadmin') {
		?>

		<div class="sidebar" data-color="red" data-image="images/sidebar-5.jpg">
			<div class="sidebar-wrapper">
				<div class="logo">
					<a href="#" class="simple-text logo-mini"><span class="nc-icon nc-send"></span></a>
					<a href="#" class="simple-text logo-normal">Project Tracker&trade;</a>
				</div>

				<ul class="nav">
					<li routerlinkactive="active" class="nav-item active">
						<a class="nav-link" href="index.php">
							<i class="fa fa-chart-pie"></i>
							<p>Dashboard</p>
						</a>
					</li>
					<?php
					// Fetch all main navigation items
					$sql = 'SELECT * FROM navigation WHERE parent!="P" AND parent!="PA" AND admin_sub_parent is NULL AND hyper_link!="index.php" ORDER BY ABS(sort_no)';
					$result = execute_query($sql);

					while ($row = mysqli_fetch_array($result)) {
						$active = ($row['hyper_link'] == basename($_SERVER['PHP_SELF'])) ? ' active' : '';

						// Check if this item is a standalone link or a parent
						if ($row['admin_parent'] != "P") {
							// Render standalone navigation item
							echo '<li routerlinkactive="active" class="nav-item' . $active . '">
                                <a class="nav-link" href="' . $row['hyper_link'] . '">
                                    <i class="' . $row['icon_image'] . '"></i>
                                    <p>' . $row['link_description'] . '</p>
                                </a>
                              </li>';
						} else {
							// This item has sub-items
							echo '<li routerlinkactive="active" class="nav-item' . $active . '">
                                <a data-toggle="collapse" data-target="#parent' . $row['sno'] . '" class="nav-link" href="#parent' . $row['sno'] . '">
                                    <i class="' . $row['icon_image'] . '"></i>
                                    <p>' . $row['link_description'] . '<b class="caret"></b></p>
                                </a>
                                <div class="collapse" id="parent' . $row['sno'] . '">
                                    <ul class="nav">';

							// Fetch first-level children
							$sql_sub = 'SELECT * FROM navigation WHERE admin_parent="' . $row['sno'] . '" ORDER BY ABS(sort_no)';
							$result_sub = execute_query($sql_sub);
							while ($row_sub = mysqli_fetch_assoc($result_sub)) {
								// Render second-level items
								echo '<li routerlinkactive="active" class="nav-item">';

								// Check if this second-level item has further children
								$sql_sub_sub = 'SELECT * FROM navigation WHERE admin_sub_parent="' . $row_sub['sno'] . '" ORDER BY sort_no ASC';
								$result_sub_sub = execute_query($sql_sub_sub);
								if (mysqli_num_rows($result_sub_sub) > 0) {
									// If there are further children, create a collapsible item
									echo '<a data-toggle="collapse" data-target="#sub' . $row_sub['sno'] . '" class="nav-link" href="#sub' . $row_sub['sno'] . '">
                                        <i class="' . $row_sub['icon_image'] . '" style="font-size:20px; margin-left:15px;"></i>
                                        <p>' . $row_sub['link_description'] . '<b class="caret"></b></p>
                                      </a>
                                      <div class="collapse" id="sub' . $row_sub['sno'] . '">
                                          <ul class="nav">';

									// Fetch second-level children
									while ($row_sub_sub = mysqli_fetch_assoc($result_sub_sub)) {
										echo '<li routerlinkactive="active" class="nav-item">
                                            <a class="nav-link" href="' . $row_sub_sub['hyper_link'] . '">
                                                <i class="' . $row_sub_sub['icon_image'] . '" style="font-size:20px; margin-left:30px;"></i>
                                                <p>' . $row_sub_sub['link_description'] . '</p>
                                            </a>
                                          </li>';
									}

									echo '</ul></div>'; // Close second-level children list
								} else {
									// No further children, render as a hyperlink
									echo '<a class="nav-link" href="' . $row_sub['hyper_link'] . '">
                                        <i class="' . $row_sub['icon_image'] . '" style="font-size:20px; margin-left:15px;"></i>
                                        <p>' . $row_sub['link_description'] . '</p>
                                      </a>';
								}

								echo '</li>'; // Close second-level item
							}

							echo '</ul></div></li>'; // Close first-level item
						}
					}
					?>
				</ul>
			</div>
		</div>



				<div class="main-panel">
					<nav class="navbar navbar-expand-lg ">
						<div class="container-fluid">
							<div class="navbar-wrapper">
								<a class="navbar-brand page-title" href="#" style="font-size:24px; color:#F83A3D"><?php echo $GLOBALS['title']; ?></a>
							</div>
							<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-bar burger-lines"></span>
								<span class="navbar-toggler-bar burger-lines"></span>
								<span class="navbar-toggler-bar burger-lines"></span>
							</button>
							<div class="collapse navbar-collapse justify-content-end">
								<ul class="nav navbar-nav mr-auto">
									<li><form class="navbar-form navbar-left navbar-search-form" role="search">
										<div class="input-group">
											<i class="fab fa-sistrix"></i>
											<input type="text" value="" class="form-control" placeholder="Search... (Shortcut : Ctrl+/)" id="shortcut_command">
										</div>
										</form></li>
								</ul>
								<ul class="navbar-nav">
									   <li class="nav-item dropdown"> 
										<a class="" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><button class="btn btn-info"><i class="fa fa-user-lock"></i> <?php echo $_SESSION['unit_name']; ?></button></a>&nbsp;|&nbsp; 
										<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
											<a class="dropdown-item" href="#">Profile</a>
											<a class="dropdown-item" href="#">Activity Log</a>
											<div class="divider"></div>
											<a class="dropdown-item" href="signout.php"><i class="fas fa-sign-out-alt"></i>Signout</a>
										</div>
									</li>
									<li class="nav-item">
										<a href="<?php echo returnlink("index.php", false); ?>"><button class="btn btn-danger"><i class="fa fa-backward"></i> Back</button></a>
									</li>
								</ul>
							</div>
						</div>
					</nav>
					<div class="content">
						<div class="container-fluid">
		<?php
	} else {
		?>	
				<div class="sidebar" data-color="red" data-image="images/sidebar-5.jpg">
				<!--

			Tip 1: you can change the color of the sidebar using: data-color="blue | azure | green | orange | red | purple"
			Tip 2: you can also add an image using data-image tag
		-->
					<div class="sidebar-wrapper">
						<div class="logo">
							<a href="#" class="simple-text logo-mini"><span class="nc-icon nc-send"></span></a>
							<a href="#" class="simple-text  logo-normal">Project Tracker&trade;</a>
						</div>

						<ul class="nav">
							<li class="nav-item active">
								<a class="nav-link" href="index.php">
									<i class="fa fa-chart-pie"></i>
									<p>Dashboard</p>
								</a>
							</li>

							<?php
							$userId = $_SESSION['usertype'];

							/* -------- MAIN LEVEL -------- */
							$sql = "
					SELECT * FROM navigation 
					WHERE parent!='P' 
					AND parent!='PA' 
					AND admin_sub_parent IS NULL 
					AND hyper_link!='index.php'
					ORDER BY ABS(sort_no)
					";

							$result = execute_query($sql);

							while ($row = mysqli_fetch_assoc($result)) {

								$active = ($row['hyper_link'] == basename($_SERVER['PHP_SELF'])) ? ' active' : '';

								/* -------- STANDALONE MENU -------- */
								if ($row['admin_parent'] != 'P') {

									$sqlAccess = "
							SELECT 1 FROM user_access 
							WHERE user_id='$userId' 
							AND file_name='{$row['sno']}'
							";
									if (mysqli_num_rows(execute_query($sqlAccess))) {

										echo '
								<li class="nav-item' . $active . '">
									<a class="nav-link" href="' . $row['hyper_link'] . '">
										<i class="' . $row['icon_image'] . '"></i>
										<p>' . $row['link_description'] . '</p>
									</a>
								</li>';
									}

								}
								/* -------- PARENT MENU -------- */ else {

									/* ---- FIRST LEVEL CHILDREN ---- */
									$sqlSub = "
							SELECT * FROM navigation 
							WHERE admin_parent='{$row['sno']}'
							ORDER BY ABS(sort_no)
							";
									$resSub = execute_query($sqlSub);

									$visibleChildren = [];

									while ($sub = mysqli_fetch_assoc($resSub)) {

										/* ---- CHECK DIRECT ACCESS ---- */
										$sqlCheck = "
								SELECT 1 FROM user_access 
								WHERE user_id='$userId' 
								AND file_name='{$sub['sno']}'
								";
										if (mysqli_num_rows(execute_query($sqlCheck))) {
											$visibleChildren[] = $sub;
											continue;
										}

										/* ---- CHECK GRAND CHILD ACCESS ---- */
										$sqlSubSub = "
								SELECT sno FROM navigation 
								WHERE admin_sub_parent='{$sub['sno']}'
								ORDER BY ABS(sort_no) ";
										$resSubSub = execute_query($sqlSubSub);

										while ($ss = mysqli_fetch_assoc($resSubSub)) {
											$sqlGC = "
									SELECT 1 FROM user_access 
									WHERE user_id='$userId' 
									AND file_name='{$ss['sno']}'
									";
											if (mysqli_num_rows(execute_query($sqlGC))) {
												$visibleChildren[] = $sub;
												break;
											}
										}
									}

									/* ---- IF NO ACCESSIBLE CHILD → SKIP PARENT ---- */
									if (empty($visibleChildren))
										continue;

									echo '
							<li class="nav-item' . $active . '">
								<a data-toggle="collapse" href="#parent' . $row['sno'] . '" class="nav-link">
									<i class="' . $row['icon_image'] . '"></i>
									<p>' . $row['link_description'] . ' <b class="caret"></b></p>
								</a>
								<div class="collapse" id="parent' . $row['sno'] . '">
									<ul class="nav">';

									/* -------- RENDER FIRST LEVEL -------- */
									foreach ($visibleChildren as $row_sub) {

										echo '<li class="nav-item">';

										/* ---- CHECK SECOND LEVEL ---- */
										$sqlSubSub = "
								SELECT * FROM navigation 
								WHERE admin_sub_parent='{$row_sub['sno']}'
								";
										$resSubSub = execute_query($sqlSubSub);

										$subSubVisible = [];

										while ($row_sub_sub = mysqli_fetch_assoc($resSubSub)) {
											$sqlGC = "
									SELECT 1 FROM user_access 
									WHERE user_id='$userId' 
									AND file_name='{$row_sub_sub['sno']}'
									";
											if (mysqli_num_rows(execute_query($sqlGC))) {
												$subSubVisible[] = $row_sub_sub;
											}
										}

										/* ---- HAS GRAND CHILDREN ---- */
										if (!empty($subSubVisible)) {

											echo '
									<a data-toggle="collapse" href="#sub' . $row_sub['sno'] . '" class="nav-link">
										<i class="' . $row_sub['icon_image'] . '" style="margin-left:15px;font-size:20px;"></i>
										<p>' . $row_sub['link_description'] . ' <b class="caret"></b></p>
									</a>
									<div class="collapse" id="sub' . $row_sub['sno'] . '">
										<ul class="nav">';

											foreach ($subSubVisible as $gc) {
												echo '
										<li class="nav-item">
											<a class="nav-link" href="' . $gc['hyper_link'] . '">
												<i class="' . $gc['icon_image'] . '" style="margin-left:30px;font-size:20px;"></i>
												<p>' . $gc['link_description'] . '</p>
											</a>
										</li>';
											}

											echo '</ul></div>';

										}
										/* ---- NO GRAND CHILD ---- */ else {
											echo '
									<a class="nav-link" href="' . $row_sub['hyper_link'] . '">
										<i class="' . $row_sub['icon_image'] . '" style="margin-left:15px;font-size:20px;"></i>
										<p>' . $row_sub['link_description'] . '</p>
									</a>';
										}

										echo '</li>';
									}

									echo '
									</ul>
								</div>
							</li>';
								}
							}
							?>



							<?php
							/*
							$sql = "
							SELECT * FROM navigation 
							WHERE (parent IS NULL OR parent='' OR parent='P') 
							AND hyper_link!='index.php'
							ORDER BY ABS(sort_no), sub_parent, link_description
							";

							$result = execute_query($sql);

							while ($row = mysqli_fetch_assoc($result)) {

								$active = ($row['hyper_link'] == basename($_SERVER['PHP_SELF'])) ? ' active' : '';

								// ---------- PARENT MENU ----------
								if ($row['parent'] == 'P') {

									// child sno list
									$sqlChild = "
									SELECT sno FROM navigation 
									WHERE parent='{$row['sno']}'
									";
									$resChild = execute_query($sqlChild);

									$childIds = [];
									while ($c = mysqli_fetch_assoc($resChild)) {
										$childIds[] = $c['sno'];
									}

									// agar child hi nahi → skip
									if (empty($childIds)) continue;

									// access check
									$ids = implode(',', $childIds);
									$sqlAccess = "
									SELECT 1 FROM user_access 
									WHERE user_id='{$_SESSION['usertype']}'
									AND file_name IN ($ids)
									";
									$resAccess = execute_query($sqlAccess);

									if (mysqli_num_rows($resAccess) == 0) continue;

									echo '
									<li class="nav-item'.$active.'">
										<a class="nav-link" data-toggle="collapse" href="#parent'.$row['sno'].'">
											<i class="'.$row['icon_image'].'"></i>
											<p>'.$row['link_description'].' <b class="caret"></b></p>
										</a>
										<div class="collapse" id="parent'.$row['sno'].'">
											<ul class="nav">';

									// child menus
									$sqlSub = "
									SELECT * FROM navigation 
									WHERE parent='{$row['sno']}'
									ORDER BY ABS(sort_no), sub_parent, link_description
									";
									$resSub = execute_query($sqlSub);

									while ($sub = mysqli_fetch_assoc($resSub)) {

										$sqlCheck = "
										SELECT 1 FROM user_access 
										WHERE user_id='{$_SESSION['usertype']}'
										AND file_name='{$sub['sno']}'
										";
										$resCheck = execute_query($sqlCheck);

										if (mysqli_num_rows($resCheck)) {
											echo '
											<li class="nav-item">
												<a class="nav-link" href="'.$sub['hyper_link'].'">
													<i class="'.$sub['icon_image'].'" style="font-size:20px;margin-left:15px;"></i>
													<span class="sidebar-normal">'.$sub['link_description'].'</span>
												</a>
											</li>';
										}
									}

									echo '
											</ul>
										</div>
									</li>';
								}

								// ---------- SINGLE MENU ----------
								else {
									$sqlAccess = "
									SELECT 1 FROM user_access 
									WHERE user_id='{$_SESSION['usertype']}'
									AND file_name='{$row['sno']}'
									";
									$resAccess = execute_query($sqlAccess);

									if (mysqli_num_rows($resAccess)) {
										echo '
										<li class="nav-item'.$active.'">
											<a class="nav-link" href="'.$row['hyper_link'].'">
												<i class="'.$row['icon_image'].'"></i>
												<p>'.$row['link_description'].'</p>
											</a>
										</li>';
									}
								}
							}
							*/
							?>


						</ul>
					</div>
				</div>
				<div class="main-panel">
					<nav class="navbar navbar-expand-lg ">
						<div class="container-fluid">
							<div class="navbar-wrapper">
								<a class="navbar-brand page-title" href="#" style="font-size:24px; color:#F83A3D"><?php echo $GLOBALS['title']; ?></a>
							</div>
							<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-bar burger-lines"></span>
								<span class="navbar-toggler-bar burger-lines"></span>
								<span class="navbar-toggler-bar burger-lines"></span>
							</button>
							<div class="collapse navbar-collapse justify-content-end">
								<ul class="nav navbar-nav mr-auto">
									<li><form class="navbar-form navbar-left navbar-search-form" role="search">
										<div class="input-group">
											<i class="fab fa-sistrix"></i>
											<input type="text" value="" class="form-control" placeholder="Search... (Shortcut : Ctrl+/)" id="shortcut_command">
										</div>
										</form></li>
								</ul>
								<ul class="navbar-nav">
									   <li class="nav-item dropdown"> 
										<a class="" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><button class="btn btn-info"><i class="fa fa-user-lock"></i> <?php echo $_SESSION['unit_name']; ?></button></a>&nbsp;|&nbsp; 
										<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
											<a class="dropdown-item" href="#">Profile</a>
											<a class="dropdown-item" href="#">Activity Log</a>
											<div class="divider"></div>
											<a class="dropdown-item" href="signout.php"><i class="fas fa-sign-out-alt"></i>Signout</a>
										</div>
									</li>
									<li class="nav-item">
										<a href="<?php echo returnlink("index.php", false); ?>"><button class="btn btn-danger"><i class="fa fa-backward"></i> Back</button></a>
									</li>
								</ul>
							</div>
						</div>
					</nav>
					<div class="content">
						<div class="container-fluid">

	
	<?php
	}
	?>		
	<?php

	$time_track[] = microtime(true);
}

function page_sidebar_old($id = '')
{
	?>	
	<?php

	if ($_SESSION['usertype'] == 'sadmin') {
		?>

		<div class="sidebar" data-color="red" data-image="images/sidebar-5.jpg">
			<div class="sidebar-wrapper">
				<div class="logo">
					<a href="#" class="simple-text logo-mini"><span class="nc-icon nc-send"></span></a>
					<a href="#" class="simple-text logo-normal">Project Tracker&trade;</a>
				</div>

				<ul class="nav">
					<li routerlinkactive="active" class="nav-item active">
						<a class="nav-link" href="index.php">
							<i class="fa fa-chart-pie"></i>
							<p>Dashboard</p>
						</a>
					</li>
					<?php
					// Fetch all main navigation items
					$sql = 'SELECT * FROM navigation WHERE parent!="P" AND parent!="PA" AND admin_sub_parent is NULL AND hyper_link!="index.php" ORDER BY ABS(sort_no), sub_parent, link_description';
					$result = execute_query($sql);

					while ($row = mysqli_fetch_array($result)) {
						$active = ($row['hyper_link'] == basename($_SERVER['PHP_SELF'])) ? ' active' : '';

						// Check if this item is a standalone link or a parent
						if ($row['admin_parent'] != "P") {
							// Render standalone navigation item
							echo '<li routerlinkactive="active" class="nav-item' . $active . '">
                                <a class="nav-link" href="' . $row['hyper_link'] . '">
                                    <i class="' . $row['icon_image'] . '"></i>
                                    <p>' . $row['link_description'] . '</p>
                                </a>
                              </li>';
						} else {
							// This item has sub-items
							echo '<li routerlinkactive="active" class="nav-item' . $active . '">
                                <a data-toggle="collapse" data-target="#parent' . $row['sno'] . '" class="nav-link" href="#parent' . $row['sno'] . '">
                                    <i class="' . $row['icon_image'] . '"></i>
                                    <p>' . $row['link_description'] . '<b class="caret"></b></p>
                                </a>
                                <div class="collapse" id="parent' . $row['sno'] . '">
                                    <ul class="nav">';

							// Fetch first-level children
							$sql_sub = 'SELECT * FROM navigation WHERE admin_parent="' . $row['sno'] . '" ORDER BY ABS(sort_no), sub_parent, link_description';
							$result_sub = execute_query($sql_sub);
							while ($row_sub = mysqli_fetch_assoc($result_sub)) {
								// Render second-level items
								echo '<li routerlinkactive="active" class="nav-item">';

								// Check if this second-level item has further children
								$sql_sub_sub = 'SELECT * FROM navigation WHERE admin_sub_parent="' . $row_sub['sno'] . '"';
								$result_sub_sub = execute_query($sql_sub_sub);
								if (mysqli_num_rows($result_sub_sub) > 0) {
									// If there are further children, create a collapsible item
									echo '<a data-toggle="collapse" data-target="#sub' . $row_sub['sno'] . '" class="nav-link" href="#sub' . $row_sub['sno'] . '">
                                        <i class="' . $row_sub['icon_image'] . '" style="font-size:20px; margin-left:15px;"></i>
                                        <p>' . $row_sub['link_description'] . '<b class="caret"></b></p>
                                      </a>
                                      <div class="collapse" id="sub' . $row_sub['sno'] . '">
                                          <ul class="nav">';

									// Fetch second-level children
									while ($row_sub_sub = mysqli_fetch_assoc($result_sub_sub)) {
										echo '<li routerlinkactive="active" class="nav-item">
                                            <a class="nav-link" href="' . $row_sub_sub['hyper_link'] . '">
                                                <i class="' . $row_sub_sub['icon_image'] . '" style="font-size:20px; margin-left:30px;"></i>
                                                <p>' . $row_sub_sub['link_description'] . '</p>
                                            </a>
                                          </li>';
									}

									echo '</ul></div>'; // Close second-level children list
								} else {
									// No further children, render as a hyperlink
									echo '<a class="nav-link" href="' . $row_sub['hyper_link'] . '">
                                        <i class="' . $row_sub['icon_image'] . '" style="font-size:20px; margin-left:15px;"></i>
                                        <p>' . $row_sub['link_description'] . '</p>
                                      </a>';
								}

								echo '</li>'; // Close second-level item
							}

							echo '</ul></div></li>'; // Close first-level item
						}
					}
					?>
				</ul>
			</div>
		</div>



				<div class="main-panel">
					<nav class="navbar navbar-expand-lg ">
						<div class="container-fluid">
							<div class="navbar-wrapper">
								<a class="navbar-brand page-title" href="#" style="font-size:24px; color:#F83A3D"><?php echo $GLOBALS['title']; ?></a>
							</div>
							<span style="margin-left:15px; font-weight:bold; position: relative; top: -35px;">
									<button class="navbar-toggler navbar-toggler-right text-end" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation" style="text-align:right;">
										<span class="navbar-toggler-bar burger-lines"></span>
										<span class="navbar-toggler-bar burger-lines"></span>
										<span class="navbar-toggler-bar burger-lines"></span>
									</button>
								</span>
							<div class="collapse navbar-collapse justify-content-end">
								<ul class="nav navbar-nav mr-auto">
									<li><form class="navbar-form navbar-left navbar-search-form" role="search">
										<div class="input-group">
											<i class="fab fa-sistrix"></i>
											<input type="text" value="" class="form-control" placeholder="Search... (Shortcut : Ctrl+/)" id="shortcut_command">
										</div>
										</form></li>
								</ul>
								<ul class="navbar-nav">
									   <li class="nav-item dropdown"> 
										<a class="" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><button class="btn btn-info"><i class="fa fa-user-lock"></i> <?php echo $_SESSION['unit_name']; ?></button></a>&nbsp;|&nbsp; 
										<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
											<a class="dropdown-item" href="#">Profile</a>
											<a class="dropdown-item" href="#">Activity Log</a>
											<div class="divider"></div>
											<a class="dropdown-item" href="signout.php"><i class="fas fa-sign-out-alt"></i>Signout</a>
										</div>
									</li>
									<li class="nav-item">
										<a href="<?php echo returnlink("index.php", false); ?>"><button class="btn btn-danger"><i class="fa fa-backward"></i> Back</button></a>
									</li>
								</ul>
							</div>
						</div>
					</nav>
					<div class="content">
						<div class="container-fluid">
		<?php
	} else {
		?>	
				<div class="sidebar" data-color="red" data-image="images/sidebar-5.jpg">
				<!--

			Tip 1: you can change the color of the sidebar using: data-color="blue | azure | green | orange | red | purple"
			Tip 2: you can also add an image using data-image tag
		-->
					<div class="sidebar-wrapper">
						<div class="logo">
							<a href="#" class="simple-text logo-mini"><span class="nc-icon nc-send"></span></a>
							<a href="#" class="simple-text  logo-normal">Project Tracker&trade;</a>
						</div>

						<ul class="nav">
							<li routerlinkactive="active" class="nav-item active"><a class="nav-link" href="index.php"><i class="fa fa-chart-pie"></i><p>Dashboard</p></a>
						<?php
						$sql = 'select * from navigation where (parent is null or parent="" or parent="P") and hyper_link!="index.php" order by abs(sort_no), sub_parent, link_description';
						$result = execute_query($sql);
						$sub_parent = '';
						while ($row = mysqli_fetch_array($result)) {
							if ($row['hyper_link'] == basename($_SERVER['PHP_SELF'])) {
								$active = ' active';
							} else {
								$active = '';
							}
							if ($_SESSION['username'] != 'sadmin') {
								if ($row['parent'] == 'P') {
									$sql = 'select group_concat(sno) as sno from navigation where parent="' . $row['sno'] . '" order by abs(sort_no), sub_parent, link_description';
									// echo $sql.'<br>';
									$row_sub = mysqli_fetch_assoc(execute_query($sql));

									$sql = 'select * from user_access where user_id="' . $_SESSION['usertype'] . '" and file_name in (' . $row_sub['sno'] . ')';
									//echo $sql.'<br><br>';
									$result_child_count = execute_query($sql);

									if (mysqli_num_rows($result_child_count) != 0) {
										echo '
									<li routerlinkactive="active" class="nav-item' . $active . '">
										<!----><a data-toggle="collapse" data-target="#parent' . $row['sno'] . '" class="nav-link" href="#parent' . $row['sno'] . '" ><i class="' . $row['icon_image'] . '"></i><p>' . $row['link_description'] . '<b class="caret"></b></p></a>
										<!---->
										<div class="collapse" id="parent' . $row['sno'] . '">
											<ul class="nav">';

										$sql = 'select * from navigation where parent="' . $row['sno'] . '" order by abs(sort_no), sub_parent, link_description';
										$result_sub = execute_query($sql);
										while ($row_sub = mysqli_fetch_assoc($result_sub)) {
											$sql = 'select * from user_access where user_id="' . $_SESSION['usertype'] . '" and file_name="' . $row_sub['sno'] . '"';
											//echo $sql;
											$result_access = execute_query($sql);
											if (mysqli_num_rows($result_access) == 1) {
												echo '<li routerlinkactive="active' . $active . '" class="nav-item"><a class="nav-link" href="' . $row_sub['hyper_link'] . '"><i class="' . $row_sub['icon_image'] . '" style="font-size:20px; margin-left:15px; margin-right:0px;"></i><span class="sidebar-mini"></span><span class="sidebar-normal">' . $row_sub['link_description'] . '</span></a>
											</li>';
											}
										}
										echo '
											</ul>
										</div>
										<!---->
									</li>';
									}

								} else {
									$sql = 'select * from user_access where user_id="' . $_SESSION['usertype'] . '" and file_name="' . $row['sno'] . '"';
									//echo $sql;
									$result_access = execute_query($sql);
									if (mysqli_num_rows($result_access) == 1) {
										echo '<li routerlinkactive="active" class="nav-item' . $active . '"><a class="nav-link" href="' . $row['hyper_link'] . '"><i class="' . $row['icon_image'] . '"></i><p>' . $row['link_description'] . '</p></a></li>';
									}
								}
							} else {
								if ($row['parent'] != "P") {
									echo '<li routerlinkactive="active" class="nav-item' . $active . '"><a class="nav-link" href="' . $row['hyper_link'] . '"><i class="' . $row['icon_image'] . '"></i><p>' . $row['link_description'] . '</p></a></li>';
								} else {
									echo '
								<li routerlinkactive="active" class="nav-item' . $active . '">
									<!----><a data-toggle="collapse" data-target="#parent' . $row['sno'] . '" class="nav-link" href="#parent' . $row['sno'] . '" ><i class="' . $row['icon_image'] . '"></i><p>' . $row['link_description'] . '<b class="caret"></b></p></a>
									<!---->
									<div class="collapse" id="parent' . $row['sno'] . '">
										<ul class="nav">';

									$sql = 'select * from navigation where parent="' . $row['sno'] . '" order by abs(sort_no), sub_parent, link_description';
									$result_sub = execute_query($sql);
									while ($row_sub = mysqli_fetch_assoc($result_sub)) {
										echo '<li routerlinkactive="active' . $active . '" class="nav-item"><a class="nav-link" href="' . $row_sub['hyper_link'] . '"><i class="' . $row_sub['icon_image'] . '" style="font-size:20px; margin-left:15px; margin-right:0px;"></i><span class="sidebar-mini"></span><span class="sidebar-normal">' . $row_sub['link_description'] . '</span></a>
											</li>';
									}
									echo '
										</ul>
									</div>
									<!---->
								</li>';
								}

							}
						}

						?>
						</ul>
					</div>
				</div>
				<div class="main-panel">
					<nav class="navbar navbar-expand-lg ">
						<div class="container-fluid">
							<div class="navbar-wrapper">
								<a class="navbar-brand page-title" href="#" style="font-size:24px; color:#F83A3D"><?php echo $GLOBALS['title']; ?></a>
							</div>
							<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-bar burger-lines"></span>
								<span class="navbar-toggler-bar burger-lines"></span>
								<span class="navbar-toggler-bar burger-lines"></span>
							</button>
							<div class="collapse navbar-collapse justify-content-end">
								<ul class="nav navbar-nav mr-auto">
									<li><form class="navbar-form navbar-left navbar-search-form" role="search">
										<div class="input-group">
											<i class="fab fa-sistrix"></i>
											<input type="text" value="" class="form-control" placeholder="Search... (Shortcut : Ctrl+/)" id="shortcut_command">
										</div>
										</form></li>
								</ul>
								<ul class="navbar-nav">
									   <li class="nav-item dropdown"> 
										<a class="" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><button class="btn btn-info"><i class="fa fa-user-lock"></i> <?php echo $_SESSION['unit_name']; ?></button></a>&nbsp;|&nbsp; 
										<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
											<a class="dropdown-item" href="#">Profile</a>
											<a class="dropdown-item" href="#">Activity Log</a>
											<div class="divider"></div>
											<a class="dropdown-item" href="signout.php"><i class="fas fa-sign-out-alt"></i>Signout</a>
										</div>
									</li>
									<li class="nav-item">
										<a href="<?php echo returnlink("index.php", false); ?>"><button class="btn btn-danger"><i class="fa fa-backward"></i> Back</button></a>
									</li>
								</ul>
							</div>
						</div>
					</nav>
					<div class="content">
						<div class="container-fluid">

	
	<?php
	}
	?>		
	<?php

	$time_track[] = microtime(true);
}
function page_footer_start()
{

	?>
					</div>
				</div>
				<footer class="footer">
					<div class="container-fluid">
						<nav class="pull-left">
							<ul>
								<li>
									<a href="#">
										Home
									</a>
								</li>
							</ul>
						</nav>
						<p class="copyright text-center">
							©
							<script>
								document.write(new Date().getFullYear())
							</script>
							<a href="http://www.weknowtech.in" target="_blank"><img src="images/logo-15.png" class="img-rounded"> Weknow Technologies</a>
						</p>
					</div>
				</footer>
			</div>
		</div>
	<?php
}
function page_footer_end()
{
	global $client_details;
	?>
		<!--  Notifications Plugin    -->
		<script src="js/bootstrap-notify.js"></script>
		<script src="js/light-bootstrap-dashboard.js"></script>
		<script src="dataTables/datatables.min.js"></script>
		<script src="js/demo.js"></script>
	
		<!--  Google Maps Plugin    -->
	   <!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>-->


	<script>
		$(document).ready(function() {
			// action on key up
			$(document).keyup(function(e) {
				if(e.which == 17) {
					isCtrl = false;
				}
			});
			$(document).keyup(function(e) {
				if(e.which == 18) {
					isAlt = false;
				}
			});
			// action on key down 17, 18, 82
			$(document).keydown(function(e) {
				if(e.which == 17) {
					isCtrl = true; 
				}
				if(e.which == 18) {
					isAlt = true; 
				}
				if(e.which == 191 && isCtrl) { 
					//console.log($("#shortcut_command"));
					$("#shortcut_command").focus();				
				} 
				if(e.which == 89 && isCtrl && isAlt) {
					if(form_type=='sale'){
						if($("#supplier_sno").val()==''){
							alert("Please select a customer.");
							$("#supplier").focus();
							return;
						}
						var current = $("#current").val();
						var part = "part_desc"+current;
						var parent_tr = $("input[name="+part+"_product]").closest('tr');
						if(parent_tr.css("background-color")=='rgb(255, 0, 0)'){
							parent_tr.css("background-color", "#cccccc");
							$("#part_desc"+current+"_return_flag").val("0");
						}
						else{
							parent_tr.css("background-color", "#FF0000");
							$("#part_desc"+current+"_return_flag").val("1");
						}
					}
				} 
			});

		});
		</script>
	<?php
	echo '
	<div class="clear" class="no-print"></div>
        <div id="footerstick" class="no-print">
            <div id="footercontent">
				<div id="support">
                    <strong>Helpdesk : <a href="http://www.weknowtech.in">Weknow Technologies</a></strong><br/>
                    M : +91-9554969771 to 779<br />
                </div>
            </div>
        </div>
    </div>
	
</body>
</html>';
}

function pagecount($sql, $script, $active)
{
	$result = execute_query($sql);
	$count = mysqli_num_rows($result);
	$page = ceil($count / 50);
	if ($active > 1 && $active < $page) {
		$print = '<a href="' . $script . '">&lt;&lt;</a> | <a href="' . $script . '?pg=' . ($active - 1) . '"> &lt;</a> |';
	} else {
		$print = '';
	}
	for ($i = 1; $i <= $page; $i++) {
		if ($active == $i) {
			$print .= $i . ' | ';
		} else {
			$print .= '<a href="' . $script . '?pg=' . $i . '">' . $i . '</a> | ';
		}
	}
	return $print;
}

function randomstring()
{

	$length = 16;

	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	$char_length = (strlen($chars) - 1);

	$string = $chars[rand(0, $char_length)];

	for ($i = 1; $i < $length; $i = strlen($string)) {

		$r = $chars[rand(0, $char_length)];

		if ($r != $string[$i - 1]) {

			$string .= $r;

		}

	}

	return $string;

}

function randompassword()
{
	$length = 8;
	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$char_length = (strlen($chars) - 1);
	$string = $chars[rand(0, $char_length)];
	for ($i = 1; $i < $length; $i = strlen($string)) {
		$r = $chars[rand(0, $char_length)];
		if ($r != $string[$i - 1]) {
			$string .= $r;
		}
	}
	return $string;
}

function randomnumber()
{
	$length = 6;
	$chars = '0123456789';
	$char_length = (strlen($chars) - 1);
	$string = $chars[rand(0, $char_length)];
	for ($i = 1; $i < $length; $i = strlen($string)) {
		$r = $chars[rand(0, $char_length)];
		if ($r != $string[$i - 1]) {
			$string .= $r;
		}
	}
	return $string;
}

function send_mail($customer_name, $mailid, $msg, $subject)
{
	$msg = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<title>Backup2mail status</title>
			<style type="text/css">body { background: #000; color: #0f0; text-transform: \'Courier New\', Courier; }</style>
		</head>
		<body><h3>' . $msg . '</h3></body></html>';

	$email = new PHPMailer();
	$email->From = 'info@weknowtech.in';
	$email->FromName = 'Weknow Technologies';
	$email->Subject = $subject;
	$email->Body = $msg;
	$email->AddAddress($mailid, $customer_name);

	$email->isHTML(true);

	$email->Send();

}

function send_sms($number, $get_msg, $hindi = '')
{
	/*ACE Mind Settings

	$get_msg = urlencode($get_msg);
	$ch = curl_init();
	$url = "http://sms.acemindtech.com/api/mt/SendSMS?";
	global $sms_user;
	global $sms_pwd;
	$no=$number;
	$senderID="WEBPRO";
	$route = 22;
	$param = "user=$sms_user&password=$sms_pwd&senderid=$senderID&channel=Trans&DCS=0&flashsms=0&number=$no&text=$get_msg&route=$route$hindi";
	$url = $url.$param;
	//echo $url;
	curl_setopt($ch, CURLOPT_URL,  $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$buffer = curl_exec($ch);
	if(empty($buffer)){
	   return $buffer;
	}
	else{
	   return $buffer;
	}

	*/

	/*SMS World*/
	if (!isset($_POST['sms_message'])) {
		$_POST['sms_message'] = '';
	}
	$msg = '';
	//$sql = 'http://smsw.co.in/API/WebSMS/Http/v1.0a/index.php?format=json&route_id=route+id&callback=Any+Callback+URL&unique=0&sendondate=19-05-2020T07:38:04&msgtype=unicode';
	/*if($hindi!=''){
		$hindi = '&DCS=8';
	}
	else{
		$hindi = '&DCS=0';
	}*/
	$get_msg = urlencode($get_msg);
	$ch = curl_init();
	$url = "http://smsw.co.in/API/WebSMS/Http/v1.0a/index.php?";
	global $sms_user;
	global $sms_pwd;
	$no = $number;
	$senderID = mysqli_fetch_array(execute_query("select * from general_settings where `desc`='sms_sender_id'"));
	$senderID = $senderID['rate'];


	$route = 39;
	$param = "username=$sms_user&password=$sms_pwd&sender=$senderID";
	$param = "username=$sms_user&password=$sms_pwd&sender=$senderID&to=$no&message=$get_msg&route_id=$route&reqid=1&format=json$hindi";
	$url = $url . $param;
	//echo $url.'<br><br>';
	//die();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$buffer = curl_exec($ch);
	if (empty($buffer)) {
		return $buffer;
	} else {
		$tot_credit = 0;
		$res = json_decode($buffer, true);
		//print_r($res);
		if (is_array($res)) {
			$row = array();
			$comma = 0;
			$i = 1;
			$msg_id = $res['msg_id'];
			$sender_id = $res['SenderId'];
			$message = $res['message'];
			$sendondate = $res['sendondate'];
			$sql = 'insert into sms_report (msg_id, sendondate, originalnumber, message, textMessage, SenderId, billcredit, dlr_seq) value ';
			foreach ($res['seq_id'] as $k => $v) {
				//$tot_credit += $res['billcredit'];
				if ($comma == 0) {
					$comma = 1;
					$sql .= '("' . $msg_id . '", "' . $sendondate . '", "' . $v['originalnumber'] . '",  "' . $_POST['sms_message'] . '", "' . $message . '", "' . $sender_id . '", "' . $v['billcredit'] . '", "' . $k . '")';
				} else {
					$sql .= ', ("' . $msg_id . '", "' . $sendondate . '", "' . $v['originalnumber'] . '",  "' . $_POST['sms_message'] . '", "' . $message . '", "' . $sender_id . '", "' . $v['billcredit'] . '", "' . $k . '")';

				}
				if ($i % 50 == 0) {
					execute_query($sql);
					//echo 'Count : '.$i.' >> '.$sql.'<br><br>';
					$sql = 'insert into sms_report (msg_id, sendondate, originalnumber, message, textMessage, SenderId, billcredit, dlr_seq) value ';
					$i++;
					$comma = 0;
				} else {
					$i++;
				}
			}
			$msg .= '<span class="alert-success">SMS Sent. Total Numbers : ' . ($i - 1) . '</span>';
			//echo $sql;
			execute_query($sql);
		} else {
			$msg .= '<span class="alert-failed">SMS Failed. ' . $buffer . '</span>';
		}
		//echo $msg;
		return $buffer;
	}

	/*Ashish Kalanoria

	$get_msg = urlencode($get_msg);
	$ch = curl_init();
	$url = "http://5.189.187.82/sendsms/bulk.php?";
	global $sms_user;
	global $sms_pwd;
	$no=$number;
	$senderID="UPDATE";
	$route = 22;
	$param = "username=$sms_user&password=$sms_pwd&sender=$senderID&mobile=$no&message=$get_msg&type=UNICODE";
	$url = $url.$param;
	//echo $url;
	curl_setopt($ch, CURLOPT_URL,  $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$buffer = curl_exec($ch);
	if(empty($buffer)){
	   return $buffer;
	}
	else{
	   return $buffer;
	}
	*/

}

function sms_delivery($msgid)
{
	$ch = curl_init();
	$url = "http://sms.acemindtech.com/API/WebSMS/Http/v1.0a/index.php?";
	$user = "knipss";
	$pwd = "knipss987";
	$senderID = "KNIPSS";
	$param = "method=show_dlr&username=$user&password=$pwd&msg_id=$msgid&seq_id=1,2&limit=0,100&format=json";
	$url = $url . $param;
	echo $url . '<br>';
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$buffer = curl_exec($ch);
	if (empty($buffer)) {
		return $buffer;
	} else {
		return $buffer;
	}
}


function logout()
{

	date_default_timezone_set('Asia/Calcutta');

	$_SESSION['enddate'] = date('y-m-d');

	$time = localtime();

	$time = $time[2] . ':' . $time[1] . ':' . $time[0];

	$_SESSION['endtime'] = $time;

	$sql = "update session set s_end_time='" . $_SESSION['endtime'] . "' where s_id='" . $_SESSION['id'] . "' and user='" . $_SESSION['username'] . "'";

	execute_query($sql);

	session_destroy();

	session_unset();

	session_write_close();

	header("Location: index.php");

	echo '<div id="container" class="ltr">

	<center><h2>Logged Out Succesfully. <a href="index.php">Click Here</a> to continue or close this window</center>

	</div>';

}


function sethistory()
{
	// make sure the container array exists
	// the paranoid will also check here that sessions are even being used 
	if (!isset($_SESSION['history'])) {
		$_SESSION['history'] = array();
	}
	// make an easier to use reference to the container
	$h =& $_SESSION['history'];
	// get the referring page and this page
	// we need to construct matching strings
	// put the referring page straight in the array
	if (!isset($_SERVER['HTTP_REFERER'])) {
		$_SERVER['HTTP_REFERER'] = '';
	}
	$h[] = $from = $_SERVER['HTTP_REFERER'];
	$here = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	// find out how many elements we have
	$count = count($h);
	//don't waste memory - trim off old entries
	while ($count > 20) {
		array_shift($h);
		$count--;
	}
	// don't want to get stuck in a reference loop
	// this can be falsely triggered by pages that link to each other 
	// but hopefully rarely and the button will still behave rationally
	// also catches use of the browser 'Back' button/key
	// remove last two items to rewind history state
	while ($count > 1 && $h[$count - 2] == $here) {
		array_pop($h);
		array_pop($h);
		$count -= 2;
	}
	// don't want to get stuck on one page either
	// for pages that process themselves or are returned to after process script
	// remove last item to rewind history state
	while ($count > 0 && $h[$count - 1] == $here) {
		array_pop($h);
		$count--;
	}
	// all done
	return;
}

function returnlink($defaulturl = 'index.php', $override = false)
{
	// initialise variables
	$c = 0;
	$url = '';
	// check that the history container exists
	// if so check it has something in it and set $url
	if (isset($_SESSION['history'])) {
		$c = count($_SESSION['history']);
		$url = ($c > 0) ? $_SESSION['history'][$c - 1] : '';
	}
	// check for use $defaulturl conditions
	// $c may still be > 0 if the page was accessed directly
	// but $url will be blank
	if ($override || $c == 0 || $url == '') {
		return $defaulturl;
	} else {
		return $url;
	}
}

function logvalidate($fileid = '')
{
	if (basename($_SERVER['PHP_SELF']) == 'index.php') {
		return true;
	}
	if (!isset($_SESSION['session_id'])) {
		header("Location: index.php");
	}
	$current_time = time();
	$sql = 'select * from general_settings where `desc`="session_timeout"';
	$timeout = mysqli_fetch_array(execute_query($sql));
	if ($timeout['rate'] > 0) {
		$sql = 'select * from session where s_id="' . $_SESSION['session_id'] . '"';
		$session = mysqli_fetch_array(execute_query($sql));
		$timeout = $timeout['rate'] * 60;
		$difference = $current_time - $session['last_active'];
		if ($difference > $timeout) {
			logout();
		}
	}
	if ($_SESSION['username'] == 'sadmin') {
		$sql = 'update session set last_active="' . time() . '" where s_id="' . $_SESSION['session_id'] . '"';
		execute_query($sql);
		return true;
	}
	$sql = 'select * from navigation where sno="' . $fileid . '"';
	$result_parent = execute_query($sql);
	$row_parent = mysqli_fetch_array($result_parent);
	if ($row_parent['parent'] == "P") {
		$sql = 'update session set last_active="' . time() . '" where s_id="' . $_SESSION['session_id'] . '"';
		execute_query($sql);
		return true;
	}
	if ($fileid != 'index.php' && $fileid != '') {
		$sql = 'select * from user_access where user_id="' . $_SESSION['usertype'] . '" and file_name="' . $fileid . '"';
		//echo $sql;
		$result_access = execute_query($sql);
		if (mysqli_num_rows($result_access) != 1) {
			header("Location: index.php");
		}
	}
	$sql = 'update session set last_active="' . time() . '" where s_id="' . $_SESSION['session_id'] . '"';
	//echo $sql;
	execute_query($sql);
}


function int_to_words($x)
{
	$nwords = array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten", "eleven", "twelve", "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eighteen", "nineteen", "twenty", 30 => "thirty", 40 => "forty", 50 => "fifty", 60 => "sixty", 70 => "seventy", 80 => "eighty", 90 => "ninety");
	if (!is_numeric($x)) {
		$w = '#';
	} else if (fmod($x, 1) != 0) {
		$w = '#';
	} else {
		if ($x < 0) {
			$w = 'minus ';
			$x = -$x;
		} else {
			$w = '';
		}
		if ($x < 21) {
			$w .= $nwords[$x];
		} else if ($x < 100) {
			$w .= $nwords[10 * floor($x / 10)];
			$r = fmod($x, 10);
			if ($r > 0) {
				$w .= '-' . $nwords[$r];
			}
		} else if ($x < 1000) {
			$w .= $nwords[floor($x / 100)] . ' hundred';
			$r = fmod($x, 100);
			if ($r > 0) {
				$w .= ' and ' . int_to_words($r);
			}
		} else if ($x < 100000) {
			$w .= int_to_words(floor($x / 1000)) . ' thousand';
			$r = fmod($x, 1000);
			if ($r > 0) {
				$w .= ' ';
				if ($r < 100) {
					$w .= 'and ';
				}
				$w .= int_to_words($r);
			}
		} else {
			$w .= int_to_words(floor($x / 100000)) . ' lakh';
			$r = fmod($x, 100000);
			if ($r > 0) {
				$w .= ' ';
				if ($r < 100) {
					$word .= 'and ';
				}
				$w .= int_to_words($r);
			}
		}
	}
	return $w;
}

function amount_format($amount)
{
	$formatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
	$amount = $formatter->formatCurrency($amount, 'INR');
	return $amount;

}

function upload_img($name, $target_dir, $new_name, $maxDim = 1500)
{

	$file_name = $name['tmp_name'];
	list($width, $height, $type, $attr) = getimagesize($file_name);
	if ($width > $maxDim || $height > $maxDim) {
		$target_filename = $file_name;
		$ratio = $width / $height;
		if ($ratio > 1) {
			$new_width = $maxDim;
			$new_height = $maxDim / $ratio;
		} else {
			$new_width = $maxDim * $ratio;
			$new_height = $maxDim;
		}
		$src = imagecreatefromstring(file_get_contents($file_name));
		$dst = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imagedestroy($src);
		imagejpeg($dst, $target_filename); // adjust format as needed
		imagedestroy($dst);
	}



	$msg = '';
	$imageFileType = strtolower(pathinfo($name['name'], PATHINFO_EXTENSION));
	$target_dir = 'user_data/' . $target_dir . '/';
	//echo $target_dir;
	$target_file = $target_dir . basename($new_name) . '.' . $imageFileType;

	$uploadOk = 1;
	// Check if image file is a actual image or fake image
	if (isset($_POST["submit"])) {
		$check = getimagesize($name["tmp_name"]);
		if ($check !== false) {
			//$msg .=  "<div class='text-danger'>File is an image - " . $check["mime"] . ".</div>";
			$uploadOk = 1;
		} else {
			$msg .= "<div class='text-danger'>File is not an image.</div>";
			$uploadOk = 0;
		}
	}

	// Check if file already exists
	/*if (file_exists($target_file)) {
		$msg .= "<div class='text-danger'>Sorry, file already exists.</div>";
		$uploadOk = 0;
	}*/

	// Check file size
	if ($name["size"] > 50000000) {
		$msg .= "<div class='text-danger'>Sorry, your file is too large.</div>";
		$uploadOk = 0;
	}

	// Allow certain file formats
	if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
		$msg .= "<div class='text-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
		$uploadOk = 0;
	}

	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		$msg .= "<div class='text-danger'>Sorry, your file was not uploaded.</div>";
		// if everything is ok, try to upload file
	} else {
		if (!file_exists($target_dir)) {
			mkdir($target_dir, 0777, true);
		}

		if (move_uploaded_file($name["tmp_name"], $target_file)) {
			$msg .= "<div class='text-success'>The file " . htmlspecialchars(basename($name["name"])) . " has been uploaded.</div>";
		} else {
			$msg .= "<div class='text-danger'>Sorry, there was an error uploading your file.</div>";
		}
	}
	$result = array("error" => $uploadOk, "msg" => $msg, "file_name" => basename($new_name) . '.' . $imageFileType);
	return $result;
}

function get_division($id)
{
	$sql = 'select * from uprnss_division where s_no="' . $id . '"';
	$row = mysqli_fetch_assoc(execute_query($sql));
	if (isset($row['division_name'])) {
		return $row['division_name'];
	} else {
		return '';
	}
}

function get_department($id)
{
	$sql = 'select * from uprnss_department_name where sno="' . $id . '"';
	$row = mysqli_fetch_assoc(execute_query($sql));
	if (isset($row['department_name_hindi'])) {
		return $row['department_name_hindi'];
	} else {
		return '';
	}
}

?>