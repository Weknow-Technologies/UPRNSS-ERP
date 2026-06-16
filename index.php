<?php
error_reporting(E_ALL);
include("scripts/settings.php");
$msg = '';
page_header_start();
page_header_end();
if (isset($_POST['submit'])) {
	if (isset($_POST['mobile_otp'])) {
		$sql = 'select * from session where sno="' . $_SESSION['session_insert_id'] . '"';
		$session_row = mysqli_fetch_assoc(execute_query($sql));
		$compare_otp = $session_row['sno'] . '_' . $_POST['mobile_otp'];
		//echo $compare_otp.'>>'.$session_row['otp_verification'];
		$msg = '<h1>Welcome ' . $_SESSION['username'] . '</h1>';
		if ($compare_otp == $session_row['otp_verification']) {
			$sql = 'update session set otp_verification="1" where sno=' . $_SESSION['session_insert_id'];
			execute_query($sql);
			$get_msg = "Welcome " . $_SESSION['username'] . ", your OTP is verified.";
			send_sms($mobile, $get_msg);
		} else {
			$msg .= '<h3>Invalid OTP.</h3>';
		}

	} elseif ($_POST['username'] != '' && $_POST['userpwd'] != '') {

		$sql = 'select * from users where userid="' . $_POST['username'] . '"';
		//echo $sql;
		$result = execute_query($sql);
		if (mysqli_num_rows($result) != 0) {

			$row = mysqli_fetch_array(execute_query($sql));
			if ($_POST['userpwd'] == $row['pwd']) {
				$sql = 'select * from user_access_detail where user_id = "' . $row['sno'] . '"';
				$row1 = mysqli_fetch_array(execute_query($sql));
				$_SESSION['usersno'] = $row['sno'];
				$_SESSION['username'] = $row['userid'];
				$_SESSION['unit_name'] = $row['user_name'];
				$_SESSION['userpwd'] = $row['pwd'];
				$_SESSION['usertype'] = $row['type'];
				$_SESSION['session_id'] = randomstring();
				$_SESSION['startdate'] = date('y-m-d');
				$_SESSION['accessid'] = $row1['auth_id'];
				$_SESSION['branch'] = $row['branch'];
				$_SESSION['otp_verify'] = 0;
				$_SESSION['divisions'] = array();
				if (!isset($_SESSION['authcode'])) {
					$_SESSION['authcode'] = '';
				}

				$sql = 'select * from user_division where user_id="' . $row['sno'] . '"';
				$result_user_division = execute_query($sql);
				if (mysqli_num_rows($result_user_division) != 0) {
					while ($row_user_division = mysqli_fetch_assoc($result_user_division)) {
						$_SESSION['divisions'][] = $row_user_division['division_id'];
					}
				}
				$_SESSION['department'] = array();
				if (!isset($_SESSION['authcode'])) {
					$_SESSION['authcode'] = '';
				}

				$sql = 'select * from user_department where user_id="' . $row['sno'] . '"';
				$result_user_division = execute_query($sql);
				if (mysqli_num_rows($result_user_division) != 0) {
					while ($row_user_division = mysqli_fetch_assoc($result_user_division)) {
						$_SESSION['department'][] = $row_user_division['department_id'];
					}
				}

				$time = localtime();
				$time = $time[2] . ':' . $time[1] . ':' . $time[0];
				//echo $time;
				$_SESSION['starttime'] = $time;

				$sql = "insert into session (user, s_id, s_start_date, s_start_time, last_active) values ('" . $_SESSION['username'] . "','" . $_SESSION['session_id'] . "','" . $_SESSION['startdate'] . "','" . $_SESSION['starttime'] . "', '" . time() . "')";
				execute_query($sql);
				$id = mysqli_insert_id($db);

				$_SESSION['session_insert_id'] = $id;

				// print_r($_SESSION);
				$otp_verify = mysqli_fetch_array(execute_query("select * from general_settings where `desc`='otp_verification'"));
				$otp_verify = $otp_verify['rate'];
				$_SESSION['otp_verify'] = $otp_verify;
				if ($otp_verify == 1) {
					$mobile;
					$otp = randomnumber();
					$sql = 'update session set otp_verification="' . $id . '_' . $otp . '" where sno=' . $id;
					execute_query($sql);
					$get_msg = "Dear, " . $_SESSION['username'] . " one time verification code for your ERP Login is $otp. The code is valid for 30 mins only.";
					send_sms($mobile, $get_msg);

				}

				$msg = '<h1>Welcome ' . $_SESSION['username'] . '</h1>';


				$response = 2;
			} else {
				$msg .= '<h4 class="header text-center alert alert-danger">Please Enter Valid User Password</h4>';
				$response = 1;
			}
		} else {
			$msg .= '<h4 class="header text-center alert alert-danger">Please Enter Valid User Password</h4>';
			$response = 1;
		}
	} else {
		$msg .= '<h4 class="header text-center alert alert-danger">Please Enter User Detail</h4>';
		$response = 1;
	}
}
?>
<?php


if (!isset($_SESSION['session_id'])) {
	?>
	<style>
		/* Main Container */
		.full-screen-container {
			display: flex;
			flex: 1;
			height: calc(100vh - 50px);
			/* Adjust for footer height */
			width: 100%;
			overflow: hidden;
		}

		/* Left Section (Image) */
		.left-section {
			flex: 1;
			background: url('images/bulding_uprnss.jpg') no-repeat center center/cover;
			position: relative;
			display: flex;
			align-items: center;
			justify-content: center;
			// opacity: 0.9;
		}


		.title {
			font-size: 2.5rem;
			font-weight: bold;
			margin-top: 360px;
			margin-bottom: 10px;
		}

		.description {
			font-size: 1.2rem;
			line-height: 1.5;
		}

		/* Right Section (Login) */
		.right-section {
			flex: 1;
			background: url('images/right_section_bg3.jpeg') no-repeat center center/cover;

			display: flex;
			justify-content: center;
			align-items: center;
			opacity: 0.9;
		}

		.form-container {
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			width: 100%;
			max-width: 400px;
			text-align: center;
		}

		.login-header {
			font-size: 1.8rem;
			margin-bottom: 10px;
			color: #333;
		}

		.login-subtitle {
			font-size: 1.2rem;
			color: #666;
			margin-bottom: 20px;
			font-weight: bold;
		}

		.form-group {
			text-align: left;
			margin-bottom: 15px;
		}

		.form-group label {
			font-size: 0.9rem;
			color: #555;
		}

		.form-control {
			width: 100%;
			padding: 10px;
			margin-top: 5px;
			border: 1px solid #ccc;
			border-radius: 5px;
			font-size: 1rem;
		}

		.form-control:focus {
			border-color: #007bff;
			outline: none;
			box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
		}

		.btn-primary {
			background: #007bff;
			color: white;
			border: none;
			padding: 10px 15px;
			width: 100%;
			border-radius: 5px;
			font-size: 1rem;
			cursor: pointer;
			transition: background 0.3s;
		}

		.btn-primary:hover {
			background: #0056b3;
		}

		/* Footer Styling */
		.footer {
			background-color: #93454d;
			color: white;
			padding: 10px 20px;
			font-size: 0.9rem;
			width: 100%;
		}

		.footer-container {
			display: flex;
			justify-content: space-between;
			align-items: center;
			max-width: 1200px;
			margin: 0 auto;
		}

		.footer-left,
		.footer-right {
			flex: 1;
		}

		.footer-left {
			text-align: left;
		}

		.footer-right {
			text-align: right;
		}

		.footer a {
			color: #007bff;
			text-decoration: none;
		}

		.footer a:hover {
			text-decoration: underline;
		}

		.footer img {
			vertical-align: middle;
			max-height: 20px;
			margin-left: 5px;
		}

		/* Responsive Design */
		@media (max-width: 768px) {
			.footer-container {
				flex-direction: column;
				text-align: center;
			}

			.footer-left,
			.footer-right {
				text-align: center;
				margin-bottom: 10px;
			}
		}



		/* Responsive Design */
		@media (max-width: 768px) {
			.full-screen-container {
				flex-direction: column;
			}

			.left-section,
			.right-section {
				flex: none;
				width: 100%;
				height: 50%;
			}

			.overlay {
				padding: 15px;
			}

			.form-container {
				padding: 20px;
			}

			.footer {
				font-size: 0.8rem;
			}
		}
	</style>
	<div class="full-screen-container">
		<!-- Left Section: Image -->
		<div class="left-section">
			<div class="overlay">
				<h6 class="title"></h6>
				<p class="description"></p>
			</div>
		</div>

		<!-- Right Section: Login Form -->
		<div class="right-section">
			<div class="form-container">
				<p class="login-subtitle">Login to manage your projects</p>
				<form id="loginform" method="post" action="index.php">
					<div class="form-group">
						<label for="username">User ID</label>
						<input type="text" id="username" name="username" placeholder="Enter User ID" class="form-control">
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" id="password" name="userpwd" placeholder="Enter Password"
							class="form-control">
					</div>
					<button type="submit" name="submit" class="btn btn-primary">Login</button>
				</form>
			</div>
		</div>
	</div>

	<!-- Footer (Full Section) -->
	<footer class="footer">
		<div class="footer-container">
			<!-- Left Section -->
			<div class="footer-left">
				<p>

					<a href="http://www.weknowtech.in" target="_blank">
						Home
					</a>
				</p>
			</div>

			<div class="footer-right">
				<p>
					<a href="http://www.weknowtech.in" target="_blank">
						©
						<script>
							document.write(new Date().getFullYear());
						</script>
						Weknow Technologies
						<img src="images/logo-15.png" class="img-rounded" alt="Weknow Technologies">
					</a>
				</p>
			</div>

			<!-- Right Section -->

		</div>
	</footer>








	<?php
} else {
	if ($_SESSION['otp_verify'] == 1) {
		$sql = 'select * from session where sno="' . $_SESSION['session_insert_id'] . '"';
		$session_row = mysqli_fetch_assoc(execute_query($sql));
		if ($session_row['otp_verification'] != 1) {
			?>
			<div class="wrapper wrapper-full-page">
				<!-- Navbar -->

				<!-- End Navbar -->
				<div class="full-page  section-image" data-color="black" data-image="images/login.jpg">
					<!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
					<div class="content">
						<div class="container">
							<div class="col-md-4 col-sm-6 ml-auto mr-auto login-page">
								<form id="loginform" name="login" class="wufoo page" autocomplete="off"
									enctype="multipart/form-data" method="post" action="index.php">
									<div class="card card-login">
										<div class="card-header card-header-rose text-center ">
											<h2 class="header text-center">Project&trade; Tracker (<span
													class="pe-7s-study"></span>)</h2>
											<h3 class="header text-center">Login</h3>
											<?php echo $msg; ?>
										</div>
										<div class="card-body ">
											<div class="card-body">
												<div class="form-group">
													<label>Enter OTP</label>
													<input type="mobile_otp" placeholder="Enter OTP" name="mobile_otp"
														class="form-control">
												</div>
											</div>
										</div>
										<div class="card-footer ml-auto mr-auto">
											<button type="submit" name="submit" class="btn btn-warning btn-wd">Verify</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="full-page-background" style="background-image: url(images/login.jpg) "></div>
				</div>
				<footer class="footer">
					<div class="container">
						<nav>
							<ul class="footer-menu">
								<li>
									<a href="#">
										<?php echo $company_name; ?>
									</a>
								</li>
							</ul>
							<p class="copyright text-center">
								©
								<script>
									document.write(new Date().getFullYear())
								</script>
								<a href="http://www.webprotechnologies.com" target="_blank"><img src="images/APPICA.png"
										class="img-rounded"> WebPro Technologies</a>
							</p>
						</nav>
					</div>
				</footer>
			</div>
			<?php
		} else {
			goto login;
		}
	} else {

		login:
		page_sidebar();

		// --- Accountant Ledger Check ---
		if (isset($_SESSION['usertype']) && in_array($_SESSION['usertype'], ['6', '9']) && !empty($_SESSION['divisions'])) {
			$missing_ledgers = false;
			foreach ($_SESSION['divisions'] as $d_id) {
			    if ($_SESSION['usertype'] == '6') {
                    $d_id = '53';
                }
				$chk = execute_query("SELECT `desc`, `rate` FROM general_settings WHERE unit_id='$d_id'");
				$found = [];
				while ($r = mysqli_fetch_assoc($chk)) {
					if (trim($r['rate']) != '') {
						$found[strtoupper(trim($r['desc']))] = true;
					}
				}

				$isMapped = function ($k) use ($found) {
					return isset($found[$k]);
				};

				$receive = (($isMapped('CGST') && $isMapped('SGST'))) &&
					(($isMapped('CGSTTDS') && $isMapped('SGSTTDS'))) &&
					$isMapped('ITTDS') && $isMapped('LABORCESS') && $isMapped('OTHER_CHARGES');

				$transfer = (($isMapped('CGSTW') && $isMapped('SGSTW'))) &&
					(($isMapped('CGSTTDSW') && $isMapped('SGSTTDSW'))) &&
					$isMapped('ITTDSW') && $isMapped('LABOURCESSW') && $isMapped('ADVCEN');

				$bill = (($isMapped('BILL_CGST') && $isMapped('BILL_SGST'))) &&
					(($isMapped('BILL_CGST_TDS') && $isMapped('BILL_SGST_TDS'))) &&
					$isMapped('BILL_IT') && $isMapped('BILL_LABOUR_CESS') && $isMapped('BILL_SECURITY') &&
					$isMapped('BILL_OTHER_ADD') && $isMapped('BILL_OTHER_DED') && $isMapped('BILL_DED_CGST');

				if ($d_id == '53') {
					$bill = true;
				} else {
                    $transfer = true;
                }
				if (!$receive || !$transfer || !$bill) {
					$missing_ledgers = true;
					break;
				}
			}
			echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
			if ($missing_ledgers) {
				echo "<script>
				document.addEventListener('DOMContentLoaded', function() {
				    Swal.fire({
					  title: 'Action Required',
					  text: 'Kindly map your ledger first',
					  icon: 'warning',
					  confirmButtonText: 'Go to Settings',
					  allowOutsideClick: false,
					  allowEscapeKey: false
					}).then((result) => {
					  if (result.isConfirmed) {
					    window.location.href = 'unit_settings.php';
					  }
					});
				});
				</script>";
			} else {
				if (!isset($_SESSION['mapping_success_shown'])) {
					echo "<script>
					document.addEventListener('DOMContentLoaded', function() {
						Swal.fire({
						  title: 'Mapping Completed!',
						  text: 'All ledgers are mapped.',
						  icon: 'success',
						  showConfirmButton: false,
						  timer: 2000
						});
					});
					</script>";
					$_SESSION['mapping_success_shown'] = true;
				}
			}
		}
		// -------------------------------

		$sql = 'select * from master_zone';
		$result_division = execute_query($sql);
		$zone = mysqli_num_rows($result_division);

		$sql = 'select * from uprnss_division where s_no!=53';
		$result_division = execute_query($sql);
		$divisions = mysqli_num_rows($result_division);

		$sql = 'select * from uprnss_district';
		$result_division = execute_query($sql);
		$districts = mysqli_num_rows($result_division);

		$sql = 'select * from uprnss_department_name';
		$result_division = execute_query($sql);
		$departments = mysqli_num_rows($result_division);

		$sql = 'select * from uprnss_project_temp where status!="5"';
		$result_division = execute_query($sql);
		$projects = mysqli_num_rows($result_division);


		?>
		<style>
			.stat-icon {
				height: 80px;
			}

			.box {
				width: 95%;
				border-radius: 6px;
				overflow: hidden;
				transition: all 0.5s ease;
			}

			.box:hover {
				transform: scale(1.03);
			}

			.heads {
				font-size: 2.5rem;
				color: whitesmoke;
				display: flex;
				justify-content: space-between;
				padding: 1rem 1rem 2rem;
			}

			.right-heads {
				color: #333;
				opacity: 0.4;
				padding-top: 0.5rem;
			}

			.foot {
				background-color: #3333339e;
				color: white;
				text-align: center;
				padding: 0.5rem;

			}

			.box1 {
				background-color: #E84C3D;
			}

			.box2 {
				background-color: #F39C11;
			}

			.box3 {
				background-color: #1ABB9C;
			}

			.box4 {
				background-color: #3598DB;
			}

			.box5 {
				background-color: red;
			}

			.box6 {
				background-color: red;
			}

			.box7 {
				background-color: red;
			}
		</style>
		<div class="row">
			<div class="col-md-12">
				<div class="card"
					style="border: none; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden; ">
					<div class="header"
						style="background-color: #ec414a; color: white; padding: 1px; display: flex; align-items: center; padding:10px 0px">
						<!-- Logo on the left -->
						<img src="images/icon.png" alt="logo"
							style="width: 90px; height: auto; margin-right: 2px; margin-left: 40px; filter: brightness(1.1) contrast(1.1);">
						<!-- Centered Text -->
						<div style="flex: 1; text-align: center;">
							<h4 class="title" style="font-size: 1.5rem; font-weight: bold; margin: 0;">
								उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)
							</h4>
						</div>
						<img src="images/aippc_wite.png" alt="logo"
							style="width: 200px; height: auto; margin-right: 40px; margin-left: 0px; filter: brightness(1.1) contrast(1.1);">
					</div>
					<!---<div class="content" style="padding: 10px; text-align: center; background-color: #f8f9fa;">
						<p style="font-size: 1.2rem; font-weight: bold; color: #156a5b; margin: 0;">
							UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH LTD.(UPRNSS).
						</p>
					</div>-->
				</div>
			</div>
		</div>
		<?php
		switch ($_SESSION['usertype']) {
			case 'sadmin': {
				?>

					<div class="container" id="sadmin_body">

						<div class="row">
							<div class="col-md-3 mb-2">
								<a href="project_details.php" target="_blank">
									<div class="box box1">
										<div class="heads">
											<div class="left-heads" id="count1">
											<?php //echo $projects; ?>
											</div>
											<div class="right-heads">
												<i class="fas fa-users"></i>
											</div>
										</div>
										<div class="foot">
											TOTAL PROJECTS
										</div>
									</div>
								</a>
							</div>

							<div class="col-md-3 mb-2">
								<a href="index_department.php" target="_blank">
									<div class="box box4">
										<div class="heads">
											<div class="left-heads" id="count4">
											<?php //echo $departments; ?>
											</div>
											<div class="right-heads">
												<i class="fas fa-users"></i>
												<i class="fa-solid fa-diagram-project"></i>
											</div>
										</div>
										<div class="foot">
											TOTAL DEPARTMENTS
										</div>
									</div>
								</a>
							</div>
							<div class="col-md-3 mb-2">
								<a href="master_division.php" target="_blank">
									<div class="box box2">
										<div class="heads">
											<div class="left-heads" id="count2">
											<?php //echo $divisions; ?>
											</div>
											<div class="right-heads">
												<i class="fas fa-users"></i>
											</div>
										</div>
										<div class="foot">
											TOTAL DIVISIONS
										</div>
									</div>
								</a>
							</div>
							<div class="col-md-3 mb-2">
								<a href="master_district.php" target="_blank">
									<div class="box box3">
										<div class="heads">
											<div class="left-heads" id="count3">
											<?php //echo $districts; ?>
											</div>
											<div class="right-heads">
												<i class="fas fa-users"></i>
											</div>
										</div>
										<div class="foot">
											TOTAL DISTRICT
										</div>
									</div>
								</a>
							</div>

						</div>
						<?php
						////////////////////////////////1. ग्राहक विभाग मॉड्यूल///////////////////
						// 1. Count total unique departments
						$sql = 'SELECT COUNT(DISTINCT uprnss_project_temp.department_id) AS unique_department_count
								FROM uprnss_project_temp
								LEFT JOIN invoice_civil 
								ON uprnss_project_temp.invoice_civil_sno = invoice_civil.sno
								WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status=0';
						$count = execute_query($sql);
						$depaertment_current = mysqli_fetch_assoc($count);


						// 2. Get top 10 departments with the most projects
						$top_department = 'SELECT uprnss_department_name.department_name_hindi, 
						SUM(uprnss_project_temp.sanction_cost) AS max_sanction_cost,
						COUNT(*) AS project_count FROM uprnss_project_temp 
						LEFT JOIN invoice_civil ON uprnss_project_temp.invoice_civil_sno=invoice_civil.sno
						LEFT JOIN uprnss_department_name ON uprnss_project_temp.department_id = uprnss_department_name.sno
						WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = 0
						GROUP BY uprnss_project_temp.department_id
						ORDER BY project_count DESC
						LIMIT 1';
						$top_department_result = execute_query($top_department);
						$top_department = mysqli_fetch_assoc($top_department_result);

						// 3. Get bottom 10 departments with the least projects
						$bottom_department = 'SELECT uprnss_department_name.department_name_hindi, 
							SUM(uprnss_project_temp.sanction_cost) AS max_sanction_cost,
							COUNT(*) AS project_count FROM uprnss_project_temp 
						LEFT JOIN invoice_civil ON uprnss_project_temp.invoice_civil_sno=invoice_civil.sno
						LEFT JOIN uprnss_department_name ON uprnss_project_temp.department_id = uprnss_department_name.sno
						WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = 0
						GROUP BY uprnss_project_temp.department_id
						ORDER BY project_count ASC
						LIMIT 1';
						$bottom_department_result = execute_query($bottom_department);
						$bottom_department = mysqli_fetch_assoc($bottom_department_result);

						////////////////////////////////2. प्रखण्ड मॉड्यूल///////////////////
						// 1. Count total unique departments
						$sql = 'SELECT COUNT(DISTINCT uprnss_project_temp.division_id) AS unique_department_count
								FROM uprnss_project_temp
								LEFT JOIN invoice_civil 
								ON uprnss_project_temp.invoice_civil_sno = invoice_civil.sno
								WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status=0';
						$counts = execute_query($sql);
						$division_current = mysqli_fetch_assoc($counts);


						// 2. Get top 10 departments with the most projects
						$top_division = 'SELECT uprnss_division.division_name, 
						SUM(uprnss_project_temp.sanction_cost) AS max_sanction_cost,
						COUNT(*) AS project_count FROM uprnss_project_temp 
						LEFT JOIN invoice_civil ON uprnss_project_temp.invoice_civil_sno=invoice_civil.sno
						LEFT JOIN uprnss_division ON uprnss_project_temp.division_id = uprnss_division.s_no
						WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = 0
						GROUP BY uprnss_project_temp.division_id
						ORDER BY project_count DESC
						LIMIT 1';
						$top_division_result = execute_query($top_division);
						$top_division = mysqli_fetch_assoc($top_division_result);

						$bottom_divisioncost = 'SELECT uprnss_division.division_name, 
						SUM(uprnss_project_temp.sanction_cost) AS max_sanction_cost,
						COUNT(*) AS project_count FROM uprnss_project_temp 
						LEFT JOIN invoice_civil ON uprnss_project_temp.invoice_civil_sno=invoice_civil.sno
						LEFT JOIN uprnss_division ON uprnss_project_temp.division_id = uprnss_division.s_no
						WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = 0
						GROUP BY uprnss_project_temp.division_id
						ORDER BY max_sanction_cost ASC
						LIMIT 1';
						$bottom_division_results = execute_query($bottom_divisioncost);
						$bottom_division_cost = mysqli_fetch_assoc($bottom_division_results);


						$top_divisioncost = 'SELECT uprnss_division.division_name, 
						SUM(uprnss_project_temp.sanction_cost) AS max_sanction_cost,
						COUNT(*) AS project_count FROM uprnss_project_temp 
						LEFT JOIN invoice_civil ON uprnss_project_temp.invoice_civil_sno=invoice_civil.sno
						LEFT JOIN uprnss_division ON uprnss_project_temp.division_id = uprnss_division.s_no
						WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = 0
						GROUP BY uprnss_project_temp.division_id
						ORDER BY max_sanction_cost DESC
						LIMIT 1';
						$top_division_results = execute_query($top_divisioncost);
						$top_division_cost = mysqli_fetch_assoc($top_division_results);


						// 3. Get bottom 10 departments with the least projects
						$bottom_division = 'SELECT uprnss_division.division_name, 
							SUM(uprnss_project_temp.sanction_cost) AS max_sanction_cost,
							COUNT(*) AS project_count FROM uprnss_project_temp 
						LEFT JOIN invoice_civil ON uprnss_project_temp.invoice_civil_sno=invoice_civil.sno
						LEFT JOIN uprnss_division ON uprnss_project_temp.division_id = uprnss_division.s_no
						WHERE uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = 0
						GROUP BY uprnss_project_temp.division_id
						ORDER BY project_count ASC
						LIMIT 1';
						$bottom_division_result = execute_query($bottom_division);
						$bottom_division = mysqli_fetch_assoc($bottom_division_result);

						////////////////////////////////3.  परियोजना मॉड्यूल///////////////////
						$sql = 'SELECT  COUNT(*) AS total_projects,
								SUM(invoice_civil.sanction_cost) AS total_sanction_cost,
								
								SUM(CASE WHEN invoice_civil.project_status_1 = 8 THEN 1 ELSE 0 END) AS handed_over_count,
								SUM(CASE WHEN invoice_civil.project_status_1 = 8 THEN invoice_civil.sanction_cost ELSE 0 END) AS handed_over_amount,
								
								SUM(CASE WHEN invoice_civil.project_status_1 IN (10, 12) THEN 1 ELSE 0 END) AS in_progress_count,
								SUM(CASE WHEN invoice_civil.project_status_1 IN (10, 12) THEN invoice_civil.sanction_cost ELSE 0 END) AS in_progress_amount,
								
								SUM(CASE WHEN invoice_civil.project_status_1 IN (7, 11) THEN 1 ELSE 0 END) AS in_complete_count,
								SUM(CASE WHEN invoice_civil.project_status_1 IN (7, 11) THEN invoice_civil.sanction_cost ELSE 0 END) AS in_complete_amount,
								
								SUM(CASE WHEN invoice_civil.project_status_1 IN (4, 6, 13) THEN 1 ELSE 0 END) AS in_interpted_count,
								SUM(CASE WHEN invoice_civil.project_status_1 IN (4, 6, 13) THEN invoice_civil.sanction_cost ELSE 0 END) AS in_interpted_amount,
								
								SUM(CASE WHEN invoice_civil.project_status_1 = 5  THEN 1 ELSE 0 END) AS unstarted_count,
								SUM(CASE WHEN invoice_civil.project_status_1 = 5  THEN invoice_civil.sanction_cost ELSE 0 END) AS unstarted_amount

							FROM 
								uprnss_project_temp 
							LEFT JOIN 
								invoice_civil 
							ON 
								uprnss_project_temp.invoice_civil_sno = invoice_civil.sno 
							WHERE 
								uprnss_project_temp.status != "5" 
								AND uprnss_project_temp.reporting_status = 0';
						$allotted = mysqli_fetch_assoc(execute_query($sql));

						////////////////////////////////3.  परियोजना मॉड्यूल///////////////////
		
						$sql_finance = 'SELECT
								SUM(sb_details_trans.closing_amt) AS total_closing_amt FROM sb_details_trans
								LEFT JOIN  sb_fd_acount_details_invoice ON sb_fd_acount_details_invoice.sno = sb_details_trans.invoice_id';
						$saving_account = mysqli_fetch_assoc(execute_query($sql_finance));

						$sql_finance = 'SELECT
								SUM(fd_details_trans.principal_amt) AS total_principal_amt FROM fd_details_trans
								LEFT JOIN  sb_fd_acount_details_invoice ON sb_fd_acount_details_invoice.sno = fd_details_trans.invoice_id';
						$fd_account = mysqli_fetch_assoc(execute_query($sql_finance));

						$sql = 'SELECT  COUNT(*) AS total_projects,
								SUM(invoice_civil.current_fy_tot_exp) AS current_fy_tot_exp
							FROM 
								uprnss_project_temp 
							LEFT JOIN 
								invoice_civil 
							ON 
								uprnss_project_temp.invoice_civil_sno = invoice_civil.sno 
							WHERE 
								uprnss_project_temp.status != "5" 
								AND uprnss_project_temp.reporting_status = 0';
						$current_fy_tot_exp = mysqli_fetch_assoc(execute_query($sql));

						////////////////////////////////3.कार्मिक मॉड्यूल///////////////////
		
						$sql_emp = 'SELECT  COUNT(*) AS total_emp,
							SUM(CASE WHEN employee.employee_designation = 3 THEN 1 ELSE 0 END) AS ee_emp_count,
							SUM(CASE WHEN employee.employee_designation = 2 THEN 1 ELSE 0 END) AS ae_emp_count,
							SUM(CASE WHEN employee.employee_designation = 1 THEN 1 ELSE 0 END) AS je_emp_count,
							SUM(CASE WHEN employee.employee_designation = 9 THEN 1 ELSE 0 END) AS accountant_emp_count
							FROM 
								employee 
							WHERE employee.working_status = 0';
						$result_emp = mysqli_query($db_payroll, $sql_emp);
						$row_emp = mysqli_fetch_assoc($result_emp);

						/////////////////////पेरोल मॉड्यूल //////////////////////
		




						$array = array(
							"ग्राहक विभाग मॉड्यूल" => array(
								array("label" => "कुल ग्राहक विभाग:-", "file" => "civil_report_13.php", "value" => "" . $depaertment_current['unique_department_count'] . ""),
								array("label" => "सर्वाधिक परियोजना वाला विभाग:-", "file" => "civil_report_13.php", "value" => "" . $top_department['department_name_english'] . " (" . $top_department['department_name_hindi'] . ")" . " (" . $top_department['project_count'] . " परियोजनाएँ )"),
								array("label" => "निम्नतम परियोजना वाला विभाग:-", "file" => "civil_report_13.php", "value" => "" . $bottom_department['department_name_english'] . " (" . $bottom_department['department_name_hindi'] . ")" . " (" . $bottom_department['project_count'] . " परियोजनाएँ )"),
								array("label" => "सर्वाधिक परियोजना लागत वाला विभाग:-", "file" => "customer_department.php", "value" => "" . $top_department['department_name_english'] . " (" . $top_department['department_name_hindi'] . ")" . " (" . round($top_department['max_sanction_cost'], 2) . " लाख)"),
								array("label" => "निम्नतम परियोजना लागत वाला विभाग:-", "file" => "customer_department.php", "value" => "" . $bottom_department['department_name_english'] . " (" . $bottom_department['department_name_hindi'] . ")" . " (" . round($bottom_department['max_sanction_cost'], 2) . " लाख )"),
							),

							"प्रखण्ड मॉड्यूल" => array(
								array("label" => "कुल प्रखण्ड ", "file" => "master_division.php", "value" => "$divisions"),
								array("label" => "सर्वाधिक परियोजना वाला प्रखण्ड ", "file" => "Project_status_report.php?id=max", "value" => "" . $top_division['division_name'] . " (" . $top_division['project_count'] . " परियोजनाएँ )"),
								array("label" => "निम्नतम परियोजना वाला प्रखण्ड", "file" => "Project_status_report.php?id=min", "value" => "" . $bottom_division['division_name'] . " (" . $bottom_division['project_count'] . " परियोजनाएँ )"),
								array("label" => "सर्वाधिक परियोजना लागत वाला प्रखण्ड", "file" => "customer_department.php", "value" => "" . $top_division_cost['division_name'] . "(" . $top_division_cost['project_count'] . "&nbsp;परियोजनाएँ &nbsp;" . round($top_division_cost['max_sanction_cost'], 2) . " लाख )"),
								array("label" => "निम्नतम परियोजना लागत वाला प्रखण्ड", "file" => "customer_department.php", "value" => "" . $bottom_division_cost['division_name'] . "(" . $bottom_division_cost['project_count'] . "&nbsp;परियोजनाएँ &nbsp;" . round($bottom_division_cost['max_sanction_cost'], 2) . " लाख )"),
							),

							"परियोजना मॉड्यूल" => array(
								array("label" => "कुल परियोजना और उनकी लागत ", "file" => "civil_report_10.php", "value" => "" . $allotted['total_projects'] . "&nbsp;परियोजना &nbsp;लागत&nbsp;" . round($allotted['total_sanction_cost'], 2) . "&nbsp; लाख "),
								array("label" => "हस्तगत परियोजना और उनकी लागत", "file" => "project_module.php", "value" => "" . $allotted['handed_over_count'] . "&nbsp;परियोजना &nbsp;लागत&nbsp;" . round($allotted['handed_over_amount'], 2) . "&nbsp; लाख "),
								array("label" => "पूर्ण परियोजनाओं की संख्या और उनकी लागत", "file" => "project_module.php", "value" => "" . $allotted['in_complete_count'] . "&nbsp;परियोजना &nbsp;लागत&nbsp;" . round($allotted['in_complete_amount'], 2) . "&nbsp; लाख "),
								array("label" => "प्रगतिपर परियोजनाओं की संख्या और उनकी लागत", "file" => "project_module.php", "value" => "" . $allotted['in_progress_count'] . "&nbsp;परियोजना &nbsp;लागत&nbsp;" . round($allotted['in_progress_amount'], 2) . "&nbsp; लाख "),

								array("label" => "बाधित परियोजनाओं की संख्या और उनकी लागत", "file" => "project_module.php", "value" => "" . $allotted['in_interpted_count'] . "&nbsp;परियोजना &nbsp;लागत&nbsp;" . round($allotted['in_interpted_amount'], 2) . "&nbsp; लाख "),
								array("label" => "अनारंभ परियोजनाओं की संख्या और उनकी लागत", "file" => "project_module.php", "value" => "" . $allotted['unstarted_count'] . "&nbsp;परियोजना &nbsp;लागत&nbsp;" . round($allotted['unstarted_amount'], 2) . "&nbsp; लाख "),

							),

							"मानचित्र मॉड्यूल" => array(
								array("label" => "ऐसी परियोजनाये जिनका सर्वे हो चुका है ", "file" => "map_module.php", "value" => ""),
								array("label" => "ऐसी परियोजना जिनका मानचित्र प्राप्त हो चुका है ", "file" => "map_module.php", "value" => ""),
								array("label" => "ऐसी परियोजना जिनपे वास्तुकार आवंटित हो चुके है", "file" => "map_module.php", "value" => ""),
							),

							"वित्तीय मॉड्यूल" => array(
								array("label" => "बचत खाते मे मौजूद कुल राशि", "file" => "financial_module.php", "value" => "" . round($saving_account['total_closing_amt'], 2) . "&nbsp; रुपये "),
								array("label" => "सावधि खाते मे मौजूद कुल राशि", "file" => "financial_module.php", "value" => "" . round($fd_account['total_principal_amt'], 2) . "&nbsp; रुपये "),
								array("label" => "वर्तमान वित्तीय वर्ष में परियोजनाओ पर व्यय", "file" => "financial_module.php", "value" => "" . round($current_fy_tot_exp['current_fy_tot_exp'], 2) . "&nbsp; लाख "),

							),

							"कार्मिक मॉड्यूल" => array(
								array("label" => "कुल कर्मचारी की संख्या ", "file" => "personnel_module.php", "value" => "" . round($row_emp['total_emp'], 2) . "&nbsp; कर्मचारी "),
								array("label" => "नियमित कर्मचारी की संख्या", "file" => "personnel_module.php", "value" => "" . round($row_emp['total_emp'], 2) . "&nbsp; कर्मचारी "),
								array("label" => "प्रतिनियुक्ति कर्मचारी की संख्या", "file" => "personnel_module.php", "value" => "" . round($row_emp['total_emp'], 2) . "&nbsp; कर्मचारी "),
								array("label" => "अधिशाषी अभियंता(EE)  की संख्या", "file" => "personnel_module.php", "value" => "" . round($row_emp['ee_emp_count'], 2) . "&nbsp; कर्मचारी "),
								array("label" => "सहायक अभियंता(AE) की संख्या", "file" => "personnel_module.php", "value" => "" . round($row_emp['ae_emp_count'], 2) . "&nbsp; कर्मचारी "),
								array("label" => "अवर अभियंता(JE) की संख्या", "file" => "personnel_module.php", "value" => "" . round($row_emp['je_emp_count'], 2) . "&nbsp; कर्मचारी "),
								array("label" => "लेखाकार की संख्या", "file" => "personnel_module.php", "value" => "" . round($row_emp['accountant_emp_count'], 2) . "&nbsp; कर्मचारी "),
							),
							"पेरोल मॉड्यूल" => array(
								array("label" => "माह मे वेतन खर्च ", "file" => "payroll_module.php", "value" => ""),
								array("label" => "वर्तमान वित्तीय वर्ष में वेतन खर्च", "file" => "payroll_module.php", "value" => ""),
								array("label" => "माह मे आयकर कटौती ", "file" => "payroll_module.php", "value" => ""),
								array("label" => "माह मे EPF कटौती ", "file" => "payroll_module.php", "value" => ""),


							),
							"E-MB मॉड्यूल" => array(
								array("label" => "कुल ई-Measurement", "file" => "e-mb_module.php", "value" => "24133"),
								array("label" => "कुल ई-Bill", "file" => "e-mb_module.php", "value" => "22356"),
							),

							"गैलरी मॉड्यूल" => array(
								array("label" => "कुल उपलब्ध फोटो", "file" => "mis_albums.php", "value" => ""),
								array("label" => "फीचर्ड फोटो", "file" => "mis_albums.php?spcl", "value" => ""),
							),
						);
						?>
						<div class="row">
							<div class="col-md-12">
								<div class="">
									<div class="content">
										<div class="row mt-4">
											<?php
											$i = 1;
											foreach ($array as $module => $items) {
												echo '
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="module-card" style="border-radius: 12px; border: 5px solid #3598db; background: #ec414a; padding: 20px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                                <img src="images/logo/' . sprintf('%02d', $i) . '.png" height="80" style="margin-bottom: 15px;">
                                <div class="module-name" style="font-size: 1.2rem; color: white; font-weight: bold; margin-bottom: 10px;">' . $module . '</div>
                                <button class="btn btn-light" data-toggle="modal" data-target="#exampleModal' . sprintf('%02d', $i) . '" style="border-radius: 30px; font-size: 0.9rem; padding: 8px 20px; background-color: #ff8a00; color: white; border: none;">View Details</button>
                            </div>
                        </div>';
												$i++;
											}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>

						<?php
						$i = 1;
						foreach ($array as $module => $items) {
							echo '
    <div class="modal fade" id="exampleModal' . sprintf('%02d', $i) . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #197262; color: white;">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <img src="images/logo/' . sprintf('%02d', $i) . '.png" height="40" class="mr-3">' . $module . '&nbsp;(वर्तमान की प्रगति के अनुसार)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">';
							$a = 1;
							foreach ($items as $item) {
								echo '
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <div class="module-item-card" style="border-radius: 10px; background: #9f393e; text-align: center; padding: 15px; transition: transform 0.3s ease;">
                                    <a href="' . $item['file'] . '" target="_blank" style="text-decoration: none; color: #333;">
                                        <img src="images/icons/' . sprintf('%02d', $i) . '-' . $a . '.png" height="50" style="margin-bottom: 10px;">
                                        <div style="font-size: 1rem; font-weight: bold; color: white;">' . $item['label'] . '</div>
                                        <div class="badge text-wrap text-center shadow-md" style="font-size: 0.9rem; font-weight: 600; word-break: break-word; max-width: 100%; line-height: normal; color: #1b0507; background-color: #FFF;">' . $item['value'] . '</div>
                                    </a>
                                </div>
                            </div>';
								$a++;
							}
							echo '</div>
                </div>
            </div>
        </div>
    </div>';
							$i++;
						}
						?>
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="content">
										<!------------------------------Unit wise ----------------------------->
					<div class="row">
						<table class="table table-striped table-bordered table-hover">
							<div class="col-md-12 text-right">
								<form method="post" action="project_data_xlxs.php">
									<button type="submit" name="export_excel"
										class="btn btn-success text-right">Download as Excel</button>
								</form>
							</div>
							<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th rowspan="3">क्र.सं. </th>
									<th rowspan="3">प्रखण्ड का नाम </th>
									<th rowspan="3">कुल परियोजनाये</th>


									<th colspan="7">वर्तमान में संचालित परियोजनाये</th>
									<th rowspan="3">Running</th>
									<th colspan="2">MPR Report as on</th>
								</tr>
								<tr>
									<th rowspan="2">Close</th>
									<th rowspan="2">हस्तगत परियोजनाये</th>
									<th rowspan="2">Not Updated Project</th>
									<th rowspan="2">अनारम्भ परियोजनाये</th>
									<th rowspan="2">बाधित परियोजनाये</th>
									<th rowspan="2">प्रगति पर परियोजनाये</th>
									<th rowspan="2">पूर्ण परियोजनाये</th>

									<th rowspan="2">4th</th>
									<th rowspan="2">16th</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$sql = 'select * from uprnss_division where s_no!=53 order by division_name ASC';
								$result_div = execute_query($sql);
								$i = 1;
								$tot_allotted = 0;
								$tot_close_allotted = 0;
								$tot_running_allotted = 0;
								$tot_running_project = 0;
								$tot_interrupted_project = 0;
								$tot_unstarted_project = 0;
								$tot_complete_project = 0;
								$tot_handover_project = 0;
								$tot_allotted_not_update = 0;


								$tot_master = 0;
								$tot_mpr4 = 0;
								$tot_mpr16 = 0;
								while ($row_div = mysqli_fetch_assoc($result_div)) {
									$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5"  ';
									$allotted = mysqli_num_rows(execute_query($sql));

									//////Close  Projects /////////////			
				
									$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5" and reporting_status=1';
									$close_allotted = mysqli_num_rows(execute_query($sql));

									//////Running Projects /////////////	
				
									$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5" and reporting_status=0';
									$running_allotted = mysqli_num_rows(execute_query($sql));

									//////Running Projects Brekup/////////////		
				
									$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.division_id="' . $row_div['s_no'] . '" and  uprnss_project_temp.status!="5" and( invoice_civil.project_status_1="" or invoice_civil.project_status_1 IS NUll ) and uprnss_project_temp.reporting_status=0';
									$allotted_not_update = mysqli_num_rows(execute_query($sql));


									$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and invoice_civil.project_status_1="5" and uprnss_project_temp.reporting_status=0';

									$unstarted_project = mysqli_num_rows(execute_query($sql));

									$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="4" or invoice_civil.project_status_1="6"or invoice_civil.project_status_1="13") and uprnss_project_temp.reporting_status=0';
									$interrupted_project = mysqli_num_rows(execute_query($sql));

									$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5"  and (invoice_civil.project_status_1="10" or invoice_civil.project_status_1="12") and uprnss_project_temp.reporting_status=0';
									$running_project = mysqli_num_rows(execute_query($sql));


									$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="11") and uprnss_project_temp.reporting_status=0';
									$complete_project = mysqli_num_rows(execute_query($sql));

									$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="8") and uprnss_project_temp.reporting_status=0';
									$handover_project = mysqli_num_rows(execute_query($sql));


									$sql = 'SELECT * FROM `invoice_civil` where division_id="' . $row_div['s_no'] . '"  and last_update>="' . date("Y-m-01") . '" and last_update<="' . date("Y-m-12") . '"';
									$mpr_4 = mysqli_num_rows(execute_query($sql));

									$sql = 'SELECT * FROM `invoice_civil` where division_id="' . $row_div['s_no'] . '"  and last_update>="' . date("Y-m-13") . '" and last_update<="' . date("Y-m-t") . '"';
									$mpr_16 = mysqli_num_rows(execute_query($sql));

									$tot_allotted += $allotted;
									$tot_close_allotted += $close_allotted;
									$tot_running_allotted += $running_allotted;
									$tot_running_project += $running_project;
									$tot_interrupted_project += $interrupted_project;
									$tot_unstarted_project += $unstarted_project;
									$tot_complete_project += $complete_project;
									$tot_handover_project += $handover_project;
									$tot_allotted_not_update += $allotted_not_update;

									$tot_mpr4 += $mpr_4;
									$tot_mpr16 += $mpr_16;
									echo '<tr>
												<td>' . $i++ . '</td>
												<td><a href="dashboard_division.php?id=' . $row_div['s_no'] . '" target="_blank">' . $row_div['division_name'] . '</a></td>
												<td>' . $allotted . '</td>
												<td>' . $close_allotted . '</td>
												<td>' . $handover_project . '</td>
												<td>' . $allotted_not_update . '</td>
												<td>' . $unstarted_project . '</td>
												<td>' . $interrupted_project . '</td>
												<td>' . $running_project . '</td>
												<td>' . $complete_project . '</td>
												<td>' . ($running_allotted - $handover_project) . '</td>
												<td>' . $mpr_4 . '</td>
												<td>' . $mpr_16 . '</td>
												</tr>';
								}
								echo '
											</tbody>
											<tfoot><tr>
											<th>&nbsp;</th>
											<th>Total</th>
											<th>' . $tot_allotted . '</th>
											<th>' . $tot_close_allotted . '</th>
											
											<th>' . $tot_handover_project . '</th>
											<th>' . $tot_allotted_not_update . '</th>
											<th>' . $tot_unstarted_project . '</th>
											<th>' . $tot_interrupted_project . '</th>
											<th>' . $tot_running_project . '</th>
											<th>' . $tot_complete_project . '</th>
											<th>' . ($tot_running_allotted - $tot_handover_project) . '</th>
											<th>' . $tot_mpr4 . '</th>
											<th>' . $tot_mpr16 . '</th>
											</tr>';
								?>
								</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	document.addEventListener("DOMContentLoaded", () => {
		function counter(id, start, end, duration) {
			let obj = document.getElementById(id),
				current = start,
				range = end - start,
				increment = end > start ? 1 : -1,
				step = Math.abs(Math.floor(duration / range)),
				timer = setInterval(() => {
					current += increment;
					obj.textContent = current;
					if (current == end) {
						clearInterval(timer);
					}
				}, step);
		}

		function counter1(id, start, end, duration) {
			let obj = document.getElementById(id),
				current = start,
				range = end - start,
				increment = end > start ? 100 : -100,
				step = Math.abs(Math.floor(duration / range)),
				timer = setInterval(() => {
					current += increment;
					obj.textContent = current;
					if (current >= end) {
						clearInterval(timer);
						obj.textContent = end;
					}
				}, step);
		}

		counter1("count1", 0, <?php echo $projects; ?>, 1000);
	counter("count2", 0, <?php echo $divisions; ?>, 2000);
	counter("count3", 0, <?php echo $districts; ?>, 2000);
	counter("count4", 0, <?php echo $departments; ?>, 2000);
			}, { once: true });

</script>

<?php

								break;
			}
			case '1': {
				?>
<!------------------------------Unit Dashboard ----------------------------->

<?php

								$sql = 'select * from uprnss_department_name';
								$result_division = execute_query($sql);
								$departments = mysqli_num_rows($result_division);

								$sql = 'select * from uprnss_project_temp where division_id in (' . implode(",", $_SESSION['divisions']) . ') and (status="0" or status is null or status="1")   ';
								// echo $sql;
								$result_division = execute_query($sql);
								$unit_projects = mysqli_num_rows($result_division);



								?>
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="content">
				<div class="row">
					<div class="col-2"></div>
					<div class="col-2 text-center">
						<img src="images/ic_project.png" class="img-fluid stat-icon">
						<br />

						<a target="_blank" href="work_progress_info.php">Work Progreess </a>
						<br />
						<h4 class="m-0 p-0 text-danger">
							<?php echo $unit_projects; ?>Project
						</h4>
					</div>
					<div class="col-2"></div>
					<div class="col-2"></div>
					<style>
						/* Add keyframe animation for a bouncing effect */
						@keyframes bounce {

							0%,
							100% {
								transform: translateY(0);
							}

							50% {
								transform: translateY(10px);
							}
						}

						/* Styling for the animated icon */
						.animated-arrow {
							animation: bounce 1s infinite;
							/* Bounces every 1 second */
						}
					</style>
					<div class="col-2 text-center">
						<a target="_blank" href="sb_fd_account_details.php">
							<img src="images/down_arrow.png" class="img-fluid stat-icon animated-arrow"
								alt="Down Arrow">
							<h5 class="m-2 p-2 text-danger">
								Click here and fill details Urgent
							</h5>
						</a>
					</div>

				</div>
				<div class="row">
					<table class="table table-striped table-bordered table-hover">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="3">क्र.सं. </th>
								<th rowspan="3">प्रखण्ड का नाम </th>
								<th rowspan="3">कुल परियोजनाये</th>
								<th rowspan="3">Close</th>
								<th rowspan="3">Running</th>
								<th colspan="6">वर्तमान में संचालित परियोजनाये</th>
								<th colspan="2">MPR Report as on</th>
							</tr>
							<tr>
								<th rowspan="2">Not Updated Project</th>
								<th rowspan="2">अनारम्भ परियोजनाये</th>
								<th rowspan="2">बाधित परियोजनाये</th>
								<th rowspan="2">प्रगति पर परियोजनाये</th>
								<th rowspan="2">पूर्ण परियोजनाये</th>
								<th rowspan="2">हस्तगत परियोजनाये</th>
								<th>4th</th>
								<th>16th</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql = 'select * from uprnss_division where s_no in (' . implode(",", $_SESSION['divisions']) . ') order by division_name ASC';
							$result_div = execute_query($sql);
							$i = 1;
							$tot_allotted = 0;
							$tot_close_allotted = 0;
							$tot_running_allotted = 0;
							$tot_running_project = 0;
							$tot_interrupted_project = 0;
							$tot_unstarted_project = 0;
							$tot_complete_project = 0;
							$tot_handover_project = 0;
							$tot_allotted_not_update = 0;


							$tot_master = 0;
							$tot_mpr4 = 0;
							$tot_mpr16 = 0;
							while ($row_div = mysqli_fetch_assoc($result_div)) {
								$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5"  ';
								$allotted = mysqli_num_rows(execute_query($sql));

								//////Close  Projects /////////////			
			
								$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5" and reporting_status=1';
								$close_allotted = mysqli_num_rows(execute_query($sql));

								//////Running Projects /////////////	
			
								$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5" and reporting_status=0';
								$running_allotted = mysqli_num_rows(execute_query($sql));

								//////Running Projects Brekup/////////////		
			
								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.division_id="' . $row_div['s_no'] . '" and  uprnss_project_temp.status!="5" and( invoice_civil.project_status_1="" or invoice_civil.project_status_1 IS NUll ) and uprnss_project_temp.reporting_status=0';
								$allotted_not_update = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and invoice_civil.project_status_1="5" and uprnss_project_temp.reporting_status=0';

								$unstarted_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="4" or invoice_civil.project_status_1="6"or invoice_civil.project_status_1="13") and uprnss_project_temp.reporting_status=0';
								$interrupted_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5"  and (invoice_civil.project_status_1="10" or invoice_civil.project_status_1="12") and uprnss_project_temp.reporting_status=0';
								$running_project = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="11") and uprnss_project_temp.reporting_status=0';
								$complete_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="8") and uprnss_project_temp.reporting_status=0';
								$handover_project = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT * FROM `invoice_civil` where division_id="' . $row_div['s_no'] . '"  and last_update>="' . date("Y-m-01") . '" and last_update<="' . date("Y-m-12") . '"';
								$mpr_4 = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT * FROM `invoice_civil` where division_id="' . $row_div['s_no'] . '"  and last_update>="' . date("Y-m-13") . '" and last_update<="' . date("Y-m-t") . '"';
								$mpr_16 = mysqli_num_rows(execute_query($sql));

								$tot_allotted += $allotted;
								$tot_close_allotted += $close_allotted;
								$tot_running_allotted += $running_allotted;
								$tot_running_project += $running_project;
								$tot_interrupted_project += $interrupted_project;
								$tot_unstarted_project += $unstarted_project;
								$tot_complete_project += $complete_project;
								$tot_handover_project += $handover_project;
								$tot_allotted_not_update += $allotted_not_update;

								$tot_mpr4 += $mpr_4;
								$tot_mpr16 += $mpr_16;
								echo '<tr>
								<td>' . $i++ . '</td>
								<td><a href="dashboard_division.php?id=' . $row_div['s_no'] . '" target="_blank">' . $row_div['division_name'] . '</a></td>
								<td>' . $allotted . '</td>
								<td>' . $close_allotted . '</td>
								<td>' . $running_allotted . '</td>
								
								<td>' . $allotted_not_update . '</td>
								<td>' . $unstarted_project . '</td>
								<td>' . $interrupted_project . '</td>
								<td>' . $running_project . '</td>
								<td>' . $complete_project . '</td>
								<td>' . $handover_project . '</td>
								<td>' . $mpr_4 . '</td>
								<td>' . $mpr_16 . '</td>
								</tr>';
							}
							echo '
							</tbody>
							<tfoot><tr>
							<th>&nbsp;</th>
							<th>Total</th>
							<th>' . $tot_allotted . '</th>
							<th>' . $tot_close_allotted . '</th>
							<th>' . $tot_running_allotted . '</th>
							<th>' . $tot_allotted_not_update . '</th>
							<th>' . $tot_unstarted_project . '</th>
							<th>' . $tot_interrupted_project . '</th>
							<th>' . $tot_running_project . '</th>
							<th>' . $tot_complete_project . '</th>
							<th>' . $tot_handover_project . '</th>
							<th>' . $tot_mpr4 . '</th>
							<th>' . $tot_mpr16 . '</th>
							</tr>';
							?>
							</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="card-deck">
	<div class="card">
		<div class="card-header bg-danger text-white mt-3">
			<h4 class="card-title text-center text-white ">Running Projects List</h4></br>
		</div>
		<div class="card-body">
			<div class="card-text">
				<div class="card-body table-full-width table-responsive">
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th rowspan="2">S.No.</th>
								<th rowspan="2">District Name</th>
								<th rowspan="2">Project Name</th>
								<th rowspan="2">Status</th>
								<th colspan="2">Reports as On</th>
							</tr>
							<tr>
								<th>4th</th>
								<th>16th</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql = 'SELECT uprnss_project_temp.sno as sno, project_name_hindi, master_freeze, district_name_hindi, admin_go_no  FROM `uprnss_project_temp` left join uprnss_district on uprnss_district.sno = district_id where uprnss_project_temp.division_id in (' . implode(",", $_SESSION['divisions']) . ') and (status="0" or status is null or status="1") and uprnss_project_temp.reporting_status=0 ORDER BY sno DESC';
							// echo $sql;
							$result = execute_query($sql);
							$i = 1;
							while ($row = mysqli_fetch_assoc($result)) {
								$sql = 'SELECT * FROM `invoice_civil` where project_name="' . $row['sno'] . '"  and last_update>="' . date("Y-m-01") . '" and last_update<="' . date("Y-m-12") . '"';
								$result_invoice_4 = execute_query($sql);
								if (mysqli_num_rows($result_invoice_4) != 0) {
									$report_4 = '<p class="text text-success">Yes</p>';
								} else {
									$report_4 = '<p class="text text-danger">No</p>';
								}

								$sql = 'SELECT * FROM `invoice_civil` where project_name="' . $row['sno'] . '"  and last_update>="' . date("Y-m-13") . '" and last_update<="' . date("Y-m-t") . '"';
								//echo $sql.'<br>';
								$result_invoice_16 = execute_query($sql);
								if (mysqli_num_rows($result_invoice_16) != 0) {
									$report_16 = '<p class="text text-success">Yes</p>';
								} else {
									$report_16 = '<p class="text text-danger">No</p>';
								}

								$sql = 'select * from invoice_civil where project_name="' . $row['sno'] . '" and division_id!="" order by sno desc limit 1';

								$status = mysqli_fetch_assoc(execute_query($sql));
								$sql = 'select * from master_projoect_status_1 where sno="' . $status['project_status_1'] . '"';
								// echo $sql;
								// $status1 = mysqli_fetch_assoc(execute_query($sql));
								$status1 = execute_query($sql);
								if (mysqli_num_rows($status1) != 0) {
									$status1 = mysqli_fetch_assoc($status1);
								} else {
									unset($status1);
									$status1['status_1'] = '';
								}

								echo '<tr>
								<td>' . $i++ . '</td>
								<td>' . $row['district_name_hindi'] . '</td>
								<td>' . $row['project_name_hindi'] . '<h6> ' . $row['admin_go_no'] . '</h6></td>
								<td>' . $status1['status_1'] . '</td>
								<td>' . $report_4 . '</td>
								<td>' . $report_16 . '</td>
								</tr>
								';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-header bg-danger text-white mt-3">
			<h4 class="card-title text-center text-white ">Close Projects List</h4></br>
		</div>
		<div class="card-body">
			<div class="card-text">
				<div class="card-body table-full-width table-responsive">
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>District Name</th>
								<th>Project Name</th>
								<th>Status</th>
								<th>Closing Remark</th>
								<th>Closing Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql = 'SELECT uprnss_project_temp.sno as sno, project_name_hindi,closing_date, closing_remark, master_freeze, district_name_hindi, admin_go_no  FROM `uprnss_project_temp` left join uprnss_district on uprnss_district.sno = district_id where uprnss_project_temp.division_id in (' . implode(",", $_SESSION['divisions']) . ') and (status="0" or status is null or status="1") and uprnss_project_temp.reporting_status=1 ORDER BY `uprnss_project_temp`.`sno` DESC';
							// echo $sql;
							$result = execute_query($sql);
							$i = 1;
							while ($row = mysqli_fetch_assoc($result)) {

								$sql = 'select * from invoice_civil where project_name="' . $row['sno'] . '" and division_id!="" order by sno desc limit 1';

								$status = mysqli_fetch_assoc(execute_query($sql));
								$sql = 'select * from master_projoect_status_1 where sno="' . $status['project_status_1'] . '"';
								// echo $sql;
								// $status1 = mysqli_fetch_assoc(execute_query($sql));
								$status1 = execute_query($sql);
								if (mysqli_num_rows($status1) != 0) {
									$status1 = mysqli_fetch_assoc($status1);
								} else {
									unset($status1);
									$status1['status_1'] = '';
								}

								echo '<tr>
								<td>' . $i++ . '</td>
								<td>' . $row['district_name_hindi'] . '</td>
								<td>' . $row['project_name_hindi'] . '<h6> ' . $row['admin_go_no'] . '</h6></td>
								<td>' . $status1['status_1'] . '</td>
								<td>' . $row['closing_remark'] . '</td>
								<td>' . date('d-M-Y', strtotime($row['closing_date'])) . '</td>
								</tr>
								';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
								break;
			}
			case '2': {
				?>
<!------------------------------Civil Dashboard ----------------------------->

<!------------------------------new Project  ----------------------------->

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<h4>&nbsp; New Project</h4>
			<div class="content">
				<div class="row">
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>ERP code</th>
								<th>Project Type</th>
								<th>Division Name</th>
								<th>District Name</th>
								<th>Department/Sub-Department Name</th>
								<th>Project Name</th>
								<th>Project Name Hindi</th>
								<th>Sanction Date &amp; Cost</th>
								<th>Admin G.O. Details</th>
								<th>Financial G.O. Details</th>
								<th class="no-print text-center">Action</th>
							</tr>
							<tr>
								<?php
								for ($i = 1; $i <= 12; $i++) {
									echo '<th>' . $i . '</th>';
								}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
							$sql = 'select uprnss_project_temp.sno as sno,uprnss_department_name.department_sort_name as dpsortname,new_project_trans_id, division_name,sub_department_id, district_name_hindi,sub_department_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, uprnss_project_temp.status, master_freeze, uprnss_project_temp.creation_time as ctime
							from uprnss_project_temp 
							left join uprnss_district on uprnss_district.sno = district_id
							left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
							left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
							left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
							where uprnss_project_temp.department_id in (' . implode(",", $_SESSION['department']) . ') and (uprnss_project_temp.status ="2")';
							$sql .= ' ORDER BY `uprnss_project_temp`.`sno` DESC';
							// echo $sql;
							$result = execute_query($sql);
							while ($row = mysqli_fetch_assoc($result)) {
								$sql = 'select * from master_projoect_status_1 where sno="' . $row['project_status_1'] . '"';
								// echo $sql;
								// $status1 = mysqli_fetch_assoc(execute_query($sql));
								$status1 = execute_query($sql);
								if (mysqli_num_rows($status1) != 0) {
									$status1 = mysqli_fetch_assoc($status1);
								} else {
									unset($status1);
									$status1['status_1'] = '';
								}

								if ($row['ctime'] != null) {
									$dateString = date("d-m-Y H:i:s", strtotime($row['ctime']));
								} else {
									$dateString = "";
								}

								// $dateString = $row['creation_time'];
								// if ($dateString !== null) {
								// $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);
								// 	if ($dateTime !== false) {
								// 		$year = $dateTime->format("Y");
			
								// 	} else {
								// 		// echo 'Invalid date format';
								// 	}
								// }else{ echo 'Invalid date format';}
			
								// $erptempsno=$row_temp['sno'];$erpdepname=$department['department_sort_name'];
								if (!isset($row['dpsortname'])) {
									$row['dpsortname'] = "--";
								}
								if (!isset($row['ctime'])) {
									$row['ctime'] = "--";
								} else {
									$row['ctime'] = date("Y", strtotime($row['ctime']));
								}

								$serialnum = $row['dpsortname'] . "/" . $row['ctime'] . "/" . $row['sno'];


								echo '<tr>
									<td>' . $i++ . '</td>
									<td>' . $serialnum . '</td>
									<td>';
								if ($row['project_type'] != '') {
									if ($row['project_type'] == '1') {
										echo '<span class="">Ho level</span>';
									} elseif ($row['project_type'] == '2') {
										echo '<span class="">Division Level</span>';
									}
								}
								echo '
									</td>
									<td>' . $row['division_name'] . '</td>
									<td>' . $row['district_name_hindi'] . '</td>
									<td>' . $row['department_name_english'] . " (" . $row['department_name_hindi'] . ")" . '</br><h6>' . $row['sub_department_hindi'] . '</h6></td>
									<td>' . $row['project_name'] . '</td>
									<td>' . $row['project_name_hindi'] . '</td>'; ?>
							<td>
								<?php

								echo $row['sanction_cost'];
								echo '</br>';
								if (!empty($row['sanction_date'])) {
									echo date('d-m-Y', strtotime($row['sanction_date']));
								} else {
									echo '';
								}
								?>
							</td>
							<td>
								<?php
								echo $row['admin_go_no'];
								echo '</br>';
								if (!empty($row['admin_go_date'])) {
									echo date('d-m-Y', strtotime($row['admin_go_date']));
								} else {
									echo '';
								}
								?>
							</td>
							<td>
								<?php
								echo $row['financial_go_no'];
								echo '</br>';
								if (!empty($row['financial_go_date'])) {
									echo date('d-m-Y', strtotime($row['financial_go_date']));
								} else {
									echo '';
								}
								?>
							</td>
							<?php echo '
									
									<td class="no-print text-center">';
							if ($row['new_project_trans_id'] != '') {
								if ($row['status'] == "2") {
									echo '<a href="new_project_edit.php?eid=' . $row['new_project_trans_id'] . '" onClick="return confirm(\'Are you sure you?\');" target="_blank" class="btn btn-success" >Change And verfy</a>';
								} else {
									echo '<div class="alert alert-" style="background-color:red">Approved!</div>';
								}
							}
							echo '</td>
									</tr>';
							}

							?>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!------------------------------Department wise report ----------------------------->
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<h4>&nbsp; Alloted Department</h4>
			<div class="content">
				<div class="row">
					<table class="table table-striped table-bordered table-hover">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="3">क्र.सं. </th>
								<th rowspan="3">विभाग का नाम </th>
								<th rowspan="3">कुल परियोजनाये</th>
								<th rowspan="3">Close</th>
								<th rowspan="3">Running</th>
								<th colspan="6">वर्तमान में संचालित परियोजनाये</th>
								<th colspan="2">MPR Report as on</th>
							</tr>
							<tr>
								<th rowspan="2">Not Updated Project</th>
								<th rowspan="2">अनारम्भ परियोजनाये</th>
								<th rowspan="2">बाधित परियोजनाये</th>
								<th rowspan="2">प्रगति पर परियोजनाये</th>
								<th rowspan="2">पूर्ण परियोजनाये</th>
								<th rowspan="2">हस्तगत परियोजनाये</th>
								<th>4th</th>
								<th>16th</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql = 'select * from uprnss_department_name where sno in (' . implode(",", $_SESSION['department']) . ')';
							$result_dep = execute_query($sql);
							$i = 1;
							$tot_allotted = 0;
							$tot_close_allotted = 0;
							$tot_running_allotted = 0;
							$tot_running_project = 0;
							$tot_interrupted_project = 0;
							$tot_unstarted_project = 0;
							$tot_complete_project = 0;
							$tot_handover_project = 0;
							$tot_allotted_not_update = 0;


							$tot_master = 0;
							$tot_mpr4 = 0;
							$tot_mpr16 = 0;
							while ($row_dep = mysqli_fetch_assoc($result_dep)) {
								$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and status!="5"  ';
								$allotted = mysqli_num_rows(execute_query($sql));

								//////Close  Projects /////////////			
			
								$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and status!="5" and reporting_status=1';
								$close_allotted = mysqli_num_rows(execute_query($sql));

								//////Running Projects /////////////	
			
								$sql = 'select * from uprnss_project_temp where department_id="' . $row_dep['sno'] . '" and status!="5" and reporting_status=0';
								$running_allotted = mysqli_num_rows(execute_query($sql));

								//////Running Projects Brekup/////////////		
			
								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="' . $row_dep['sno'] . '" and  uprnss_project_temp.status!="5" and( invoice_civil.project_status_1="" or invoice_civil.project_status_1 IS NUll ) and uprnss_project_temp.reporting_status=0';
								$allotted_not_update = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="' . $row_dep['sno'] . '" and uprnss_project_temp.status!="5" and invoice_civil.project_status_1="5" and uprnss_project_temp.reporting_status=0';

								$unstarted_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="' . $row_dep['sno'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="4" or invoice_civil.project_status_1="6"or invoice_civil.project_status_1="13") and uprnss_project_temp.reporting_status=0';
								$interrupted_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="' . $row_dep['sno'] . '" and uprnss_project_temp.status!="5"  and (invoice_civil.project_status_1="10" or invoice_civil.project_status_1="12") and uprnss_project_temp.reporting_status=0';
								$running_project = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="' . $row_dep['sno'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="11") and uprnss_project_temp.reporting_status=0';
								$complete_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="' . $row_dep['sno'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="8") and uprnss_project_temp.reporting_status=0';
								$handover_project = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT * FROM `invoice_civil` where department_id="' . $row_dep['sno'] . '"  and last_update>="' . date("Y-m-01") . '" and last_update<="' . date("Y-m-12") . '"';
								$mpr_4 = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT * FROM `invoice_civil` where department_id="' . $row_dep['sno'] . '"  and last_update>="' . date("Y-m-13") . '" and last_update<="' . date("Y-m-t") . '"';
								$mpr_16 = mysqli_num_rows(execute_query($sql));

								$tot_allotted += $allotted;
								$tot_close_allotted += $close_allotted;
								$tot_running_allotted += $running_allotted;
								$tot_running_project += $running_project;
								$tot_interrupted_project += $interrupted_project;
								$tot_unstarted_project += $unstarted_project;
								$tot_complete_project += $complete_project;
								$tot_handover_project += $handover_project;
								$tot_allotted_not_update += $allotted_not_update;

								$tot_mpr4 += $mpr_4;
								$tot_mpr16 += $mpr_16;
								echo '<tr>
								<td>' . $i++ . '</td>
								<td><a href="dashboard_department.php?id=' . $row_dep['sno'] . '" target="_blank">' . $row_dep['department_name_english'] . " (" . $row_dep['department_name_hindi'] . ")" . '</a></td>
								<td>' . $allotted . '</td>
								<td>' . $close_allotted . '</td>
								<td>' . $running_allotted . '</td>
								
								<td>' . $allotted_not_update . '</td>
								<td>' . $unstarted_project . '</td>
								<td>' . $interrupted_project . '</td>
								<td>' . $running_project . '</td>
								<td>' . $complete_project . '</td>
								<td>' . $handover_project . '</td>
								<td>' . $mpr_4 . '</td>
								<td>' . $mpr_16 . '</td>
								</tr>';
							}
							echo '
							</tbody>
							<tfoot><tr>
							<th>&nbsp;</th>
							<th>Total</th>
							<th>' . $tot_allotted . '</th>
							<th>' . $tot_close_allotted . '</th>
							<th>' . $tot_running_allotted . '</th>
							<th>' . $tot_allotted_not_update . '</th>
							<th>' . $tot_unstarted_project . '</th>
							<th>' . $tot_interrupted_project . '</th>
							<th>' . $tot_running_project . '</th>
							<th>' . $tot_complete_project . '</th>
							<th>' . $tot_handover_project . '</th>
							<th>' . $tot_mpr4 . '</th>
							<th>' . $tot_mpr16 . '</th>
							</tr>';
							?>
							</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!------------------------------Unit wise ----------------------------->
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="content">
				<div class="row">
					<table class="table table-striped table-bordered table-hover">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="3">क्र.सं. </th>
								<th rowspan="3">प्रखण्ड का नाम </th>
								<th rowspan="3">कुल परियोजनाये</th>
								<th rowspan="3">Close</th>
								<th rowspan="3">Running</th>
								<th colspan="6">वर्तमान में संचालित परियोजनाये</th>
								<th colspan="2">MPR Report as on</th>
							</tr>
							<tr>
								<th rowspan="2">Not Updated Project</th>
								<th rowspan="2">अनारम्भ परियोजनाये</th>
								<th rowspan="2">बाधित परियोजनाये</th>
								<th rowspan="2">प्रगति पर परियोजनाये</th>
								<th rowspan="2">पूर्ण परियोजनाये</th>
								<th rowspan="2">हस्तगत परियोजनाये</th>
								<th>4th</th>
								<th>16th</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql = 'select * from uprnss_division where s_no!=53 order by division_name ASC';
							$result_div = execute_query($sql);
							$i = 1;
							$tot_allotted = 0;
							$tot_close_allotted = 0;
							$tot_running_allotted = 0;
							$tot_running_project = 0;
							$tot_interrupted_project = 0;
							$tot_unstarted_project = 0;
							$tot_complete_project = 0;
							$tot_handover_project = 0;
							$tot_allotted_not_update = 0;


							$tot_master = 0;
							$tot_mpr4 = 0;
							$tot_mpr16 = 0;
							while ($row_div = mysqli_fetch_assoc($result_div)) {
								$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5"  ';
								$allotted = mysqli_num_rows(execute_query($sql));

								//////Close  Projects /////////////			
			
								$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5" and reporting_status=1';
								$close_allotted = mysqli_num_rows(execute_query($sql));

								//////Running Projects /////////////	
			
								$sql = 'select * from uprnss_project_temp where division_id="' . $row_div['s_no'] . '" and status!="5" and reporting_status=0';
								$running_allotted = mysqli_num_rows(execute_query($sql));

								//////Running Projects Brekup/////////////		
			
								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.division_id="' . $row_div['s_no'] . '" and  uprnss_project_temp.status!="5" and( invoice_civil.project_status_1="" or invoice_civil.project_status_1 IS NUll ) and uprnss_project_temp.reporting_status=0';
								$allotted_not_update = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and invoice_civil.project_status_1="5" and uprnss_project_temp.reporting_status=0';

								$unstarted_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="4" or invoice_civil.project_status_1="6"or invoice_civil.project_status_1="13") and uprnss_project_temp.reporting_status=0';
								$interrupted_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5"  and (invoice_civil.project_status_1="10" or invoice_civil.project_status_1="12") and uprnss_project_temp.reporting_status=0';
								$running_project = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="11") and uprnss_project_temp.reporting_status=0';
								$complete_project = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.division_id="' . $row_div['s_no'] . '" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="8") and uprnss_project_temp.reporting_status=0';
								$handover_project = mysqli_num_rows(execute_query($sql));


								$sql = 'SELECT * FROM `invoice_civil` where division_id="' . $row_div['s_no'] . '"  and last_update>="' . date("Y-m-01") . '" and last_update<="' . date("Y-m-12") . '"';
								$mpr_4 = mysqli_num_rows(execute_query($sql));

								$sql = 'SELECT * FROM `invoice_civil` where division_id="' . $row_div['s_no'] . '"  and last_update>="' . date("Y-m-13") . '" and last_update<="' . date("Y-m-t") . '"';
								$mpr_16 = mysqli_num_rows(execute_query($sql));

								$tot_allotted += $allotted;
								$tot_close_allotted += $close_allotted;
								$tot_running_allotted += $running_allotted;
								$tot_running_project += $running_project;
								$tot_interrupted_project += $interrupted_project;
								$tot_unstarted_project += $unstarted_project;
								$tot_complete_project += $complete_project;
								$tot_handover_project += $handover_project;
								$tot_allotted_not_update += $allotted_not_update;

								$tot_mpr4 += $mpr_4;
								$tot_mpr16 += $mpr_16;
								echo '<tr>
									<td>' . $i++ . '</td>
									<td><a href="dashboard_division.php?id=' . $row_div['s_no'] . '" target="_blank">' . $row_div['division_name'] . '</a></td>
									<td>' . $allotted . '</td>
									<td>' . $close_allotted . '</td>
									<td>' . $running_allotted . '</td>
									
									<td>' . $allotted_not_update . '</td>
									<td>' . $unstarted_project . '</td>
									<td>' . $interrupted_project . '</td>
									<td>' . $running_project . '</td>
									<td>' . $complete_project . '</td>
									<td>' . $handover_project . '</td>
									<td>' . $mpr_4 . '</td>
									<td>' . $mpr_16 . '</td>
									</tr>';
							}
							echo '
								</tbody>
								<tfoot><tr>
								<th>&nbsp;</th>
								<th>Total</th>
								<th>' . $tot_allotted . '</th>
								<th>' . $tot_close_allotted . '</th>
								<th>' . $tot_running_allotted . '</th>
								<th>' . $tot_allotted_not_update . '</th>
								<th>' . $tot_unstarted_project . '</th>
								<th>' . $tot_interrupted_project . '</th>
								<th>' . $tot_running_project . '</th>
								<th>' . $tot_complete_project . '</th>
								<th>' . $tot_handover_project . '</th>
								<th>' . $tot_mpr4 . '</th>
								<th>' . $tot_mpr16 . '</th>
								</tr>';
							?>
							</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


<!------------------------------New project  ----------------------------->




<?php

								break;
			}
			case '3': {
				?>
<!------------------------------Ho Dashboard ----------------------------->

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="content">
				<div class="row">
					<div class="col-2"></div>
					<div class="col-2 text-center">
						<img src="images/ic_state.png" class="img-fluid stat-icon">
						<br />
						<a target="_blank" href="master_division.php">Divisions</a>
						<br />
						<h4 class="m-0 p-0 text-danger">
							<?php echo $divisions; ?>
						</h4>
					</div>
					<div class="col-2 text-center">
						<img src="images/ic_district.png" class="img-fluid stat-icon">
						<br />
						<a target="_blank" href="master_district.php">District</a>
						<br />
						<h4 class="m-0 p-0 text-danger">
							<?php echo $districts; ?>
						</h4>
					</div>
					<div class="col-2 text-center">
						<img src="images/ic_department.png" class="img-fluid stat-icon">
						<br />
						<a target="_blank" href="index_department.php">Departments</a>
						<br />
						<h4 class="m-0 p-0 text-danger">
							<?php echo $departments; ?>
						</h4>
					</div>
					<div class="col-2 text-center">
						<img src="images/ic_project.png" class="img-fluid stat-icon">
						<br />

						<a target="_blank" href="master_project_temp.php">Projects</a>
						<br />
						<h4 class="m-0 p-0 text-danger">
							<?php echo $projects; ?>
						</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
								$sql = 'SELECT * FROM `invoice_raise_ticket` where status="0" ORDER BY sno desc';
								$result = execute_query($sql);
								$raise_ticket = mysqli_num_rows($result);
								$i = 1;
								if ($raise_ticket != "0") {

									echo '<div class="row">
		<div class="col-md-12">
			<div class="card strpied-tabled-with-hover">
				<div class="card-header ">Raise Ticket</div>
				<div class="card-body table-full-width table-responsive">
					
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Department Name</th>
								<th>Division Name</th>
								<th>District Name</th>
								<th>Project Name</th>
								<th>Title</th>
								<th>Kind Off Issue</th>
								<th>Remark</th>
								<th>Resulation Date</th>
								<th >Action</th>
							</tr>	
						</thead>
						<tbody>'; ?>
<?php

										while ($row = mysqli_fetch_assoc($result)) {

											$sql = 'select department_id,district_id,division_id, project_name_hindi from uprnss_project_temp where sno="' . $row['project_id'] . '"';
											$project = mysqli_fetch_assoc(execute_query($sql));
											$sql = 'select * from uprnss_department_name where sno="' . $project['department_id'] . '"';
											$department = mysqli_fetch_assoc(execute_query($sql));

											$sql = 'select * from uprnss_district where sno="' . $project['district_id'] . '"';
											$district = mysqli_fetch_assoc(execute_query($sql));

											$sql = 'select * from uprnss_division where s_no="' . $project['division_id'] . '"';
											$division = mysqli_fetch_assoc(execute_query($sql));


											echo '<tr>
								<td>' . $i++ . '</td>
								<td>' . $department['department_name_english'] . " (" . $department['department_name_hindi'] . ")" . '</td>
								<td>' . $division['division_name'] . '</td>
								<td>' . $district['district_name_hindi'] . '</td>
								<td>' . $project['project_name_hindi'] . '</td>
								<td>' . $row['title'] . '</td>
								<td>' . $row['type_issue'] . '</td>
								<td>' . $row['remark'] . '</td>
								<td>' . $row['resulation_date'] . '</td>
								<td></td>
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
								}
								?>
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="content">
				<div> <img src="images/Dashboard.png" style="height:550px; width:1000; "> </div>
				<div class="footer">
					</hr>
					<div class="stats">
						<i class="fa fa-history"></i> Updated 3 minutes ago
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php

								break;
			}
			case '4': {
				?>
<!------------------------------Karmik Dashboard   4 ----------------------------->


<div class="row">

	<?php
							$sql = 'select * from dp_category  ';
							$result_div = execute_query($sql);
							$i = 1;
							$details = '';
							$tot_employees = 0;
							while ($row_des = mysqli_fetch_assoc($result_div)) {
								$sql = 'select * from dp_personal_info where employee_category_id="' . $row_des['sno'] . '"';
								$employees = mysqli_num_rows(execute_query($sql));
								$tot_employees += $employees;

								$details .= '<div class="col-md-3 mb-2">
						<div class="box box' . $i++ . '">
							<div class="heads">
									<div class="left-heads">
										' . $employees . '
									</div>
									<div class="right-heads">
										<i class="fas fa-users"></i>
									</div>
								</div>
								<div class="foot" style="font-size:0.8rem;">
									' . strtoupper($row_des['category_name']) . ' EMPLOYEE
								</div>
							</div>
					</div>';

							}

							?>
	<div class="col-md-3 mb-2">
		<div class="box box<?php echo $i++; ?>">
			<a href="karmik_division.php" target="_blank"> <!-- Link to the output page -->
									<div class="heads">
										<div class="left-heads">
										<?php echo $tot_employees; ?>
										</div>
										<div class="right-heads">
											<i class="fas fa-users"></i>
										</div>
									</div>
									<div class="foot" style="font-size:0.8rem;">
										TOTAL EMPLOYEE
									</div>
								</a>
							</div>
						</div>



						<script>
							function showEmployees() {
								document.getElementById('employeeTable').style.display = 'block';
							}
						</script>
						<?php

						echo $details;
						?>

					</div>

					<div class="row" id="employeeTable" style="display: none;">
						<div class="col-md-12">
							<div class="card">
								<div class="content">
									<div class="row">
										<table class="table table-striped table-bordered table-hover">
											<thead>
												<tr>
													<th>S.No.</th>
													<th>Employee Division</th>
													<th>Number of Employees</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$sql = 'SELECT * FROM `uprnss_division` ORDER BY `division_name` ASC';
												$result_div = execute_query($sql);
												$i = 1;
												$tot_employees = 0;

												while ($row_des = mysqli_fetch_assoc($result_div)) {

													$sql = 'select * from dp_personal_info where division_id="' . $row_des['s_no'] . '"';
													$employees = mysqli_num_rows(execute_query($sql));


													$tot_employees += $employees;

													echo '<tr>
                                        <td>' . $i++ . '</td>
                                        <td>' . strtoupper($row_des['division_name']) . '</td>
                                        <td>' . $employees . '</td>
                                        <td>&nbsp;</td>
                                        
                                        </tr>';
												}
												echo '
                                    </tbody>
                                    <tfoot><tr>
                                    <th>&nbsp;</th>
                                    <th>Total</th>
                                    <th>' . $tot_employees . '</th>
                                    <th>&nbsp;</th>
                                    </tr>';
												?>
												</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<div class="content">
									<div class="row">
										<table class="table table-striped table-bordered table-hover">
											<thead>
												<tr>
													<th>S.No.</th>
													<th>Employee Designation</th>
													<th>Number of Employees</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$sql = 'select * from dp_designation order by abs(sort_no) ';
												$result_div = execute_query($sql);
												$i = 1;
												$tot_employees = 0;

												while ($row_des = mysqli_fetch_assoc($result_div)) {

													$sql = 'select * from dp_personal_info where employee_designation_id="' . $row_des['sno'] . '"';
													$employees = mysqli_num_rows(execute_query($sql));


													$tot_employees += $employees;

													echo '<tr>
										<td>' . $i++ . '</td>
										<td>' . strtoupper($row_des['designation']) . '</td>
										<td>' . $employees . '</td>
										<td>&nbsp;</td>
										
										</tr>';
												}
												echo '
									</tbody>
									<tfoot><tr>
									<th>&nbsp;</th>
									<th>Total</th>
									<th>' . $tot_employees . '</th>
									<th>&nbsp;</th>
									</tr>';
												?>
												</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>

					<?php

					break;
			}
			case '6': {
				?>
					<!------------------------------Account Dashboard   6 ----------------------------->

<div class="card">
	<div class="card-header bg-danger text-white mt-3">
		<h4 class="card-title text-center text-white">Recent Fund Received by Units</h4>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr>
						<th>S.No.</th>
						<th>Sender Unit</th>
						<th>Message</th>
						<th>Date & Time</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sql_notif = 'SELECT n.*, d.division_name FROM ho_notifications n LEFT JOIN uprnss_division d ON n.sender_unit_id = d.s_no ORDER BY n.sno DESC LIMIT 10';
					$result_notif = execute_query($sql_notif);
					$i_notif = 1;
					if ($result_notif && mysqli_num_rows($result_notif) > 0) {
						while ($row_notif = mysqli_fetch_assoc($result_notif)) {
							echo '<tr>';
							echo '<td>' . $i_notif++ . '</td>';
							echo '<td>' . ($row_notif['division_name'] ? $row_notif['division_name'] : $row_notif['sender_unit_id']) . '</td>';
							echo '<td>' . $row_notif['message'] . '</td>';
							echo '<td>' . date('d-M-Y h:i A', strtotime($row_notif['created_time'])) . '</td>';
							echo '</tr>';
						}
					} else {
						echo '<tr><td colspan="4" class="text-center">No recent fund received notifications.</td></tr>';
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-header ">
		<h4 class="card-title">Pending Financial</h4>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover table-striped table-bordered">
				<thead>
					<tr>
						<th>S.No.</th>
						<th>Department</th>
						<th>G.O.Details</th>
						<th>Division</th>
						<th>District</th>
						<th>Project Name</th>
						<th>Project Sub Name</th>
						<th>Scheme</th>
						<th>Sub scheme</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sql = 'SELECT * FROM `invoice_new_project`';
					// echo $sql;
					$result = execute_query($sql);
					$i = 1;
					while ($row = mysqli_fetch_assoc($result)) {
						$sql = 'select * from transaction_new_project where invoice_id="' . $row['sno'] . '"';
						$result_invoice_details = execute_query($sql);
						while ($row_invoice_details = mysqli_fetch_assoc($result_invoice_details)) {
							$sql = 'select * from uprnss_department_name where sno="' . $row['department'] . '"';
							$department = mysqli_fetch_assoc(execute_query($sql));

							$sql = 'select * from uprnss_project_scheme where sno="' . $row['scheme'] . '"';
							// echo $sql;
							$scheme = mysqli_fetch_assoc(execute_query($sql));
							$scheme = execute_query($sql);
							if (mysqli_num_rows($scheme) != 0) {
								$scheme = mysqli_fetch_assoc($scheme);
							} else {
								unset($scheme);
								$scheme['scheme_name_english'] = '';
							}


							$sql = 'select * from uprnss_division where s_no="' . $row_invoice_details['division_id'] . '"';
							// echo $sql;
							$div = execute_query($sql);
							if (mysqli_num_rows($div) != 0) {
								$div = mysqli_fetch_assoc($div);
							} else {
								unset($div);
								$div['division_name'] = '';
							}

							$sql = 'select * from uprnss_district where sno="' . $row_invoice_details['district_id'] . '"';
							$district = execute_query($sql);
							if (mysqli_num_rows($district) != 0) {
								$district = mysqli_fetch_assoc($district);
							} else {
								unset($district);
								$district['district_name_english'] = '';
							}


							echo '<tr>
									<td>' . $i++ . '</td>
									<td>' . $department['department_name_english'] . '</td>
									<td>';
							if ($row['go_type'] != '') {
								if ($row['go_type'] == '1') {
									echo '<span class="">Administrative</span>';
								} elseif ($row['go_type'] == '2') {
									echo '<span class="">Financial</span>';
								} elseif ($row['go_type'] == '3') {
									echo '<span class="">DAdministrative + Financial</span>';
								}
							}
							echo '
									</td>
									<td>' . $div['division_name'] . '</td>
									<td>' . $district['district_name_english'] . '</td>
									<td>' . $row['project_name_english'] . '</td>
									<td>' . $row_invoice_details['sub_project_name'] . '</td>
									<td>' . $scheme['scheme_name_english'] . '</td>
									<td>' . $row['sub_scheme'] . '</td>
									<td></td>
									</tr>';
						}


					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card-deck">
	<div class="card">
		<div class="card-header ">
			<h4 class="card-title">Pending Financial</h4>
		</div>
		<div class="card-body">
			<div class="card-text">
				<div class="card-body table-full-width table-responsive">
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Division </th>
								<th>District</th>
								<th>Unit Name</th>
								<th>Project Name</th>
								<th>View</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-header ">
			<h4 class="card-title">Approved Financial</h4>
		</div>
		<div class="card-body">
			<div class="card-text">
				<div class="card-body table-full-width table-responsive">
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Division </th>
								<th>District</th>
								<th>Unit Name</th>
								<th>Project Name</th>
								<th>Date Of Appovaral</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<?php
				break;
			}
			case '9': {
				?>
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header bg-primary fw-bold text-white">
								<h4 class="card-title text-white">Incoming Fund Transfers</h4>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-hover table-striped table-bordered">
										<thead>
											<tr>
												<th class="text-center">S.No.</th>
												<th>Voucher Details</th>
												<th>Source Account</th>
												<th class="text-right">Amount</th>
												<th class="text-center">Status</th>
												<th class="text-center">Action</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$divIds = implode(',', array_map('intval', $_SESSION['divisions']));
											$sql = "SELECT h.*, bc.cus_name AS from_acc 
													FROM invoice_fund_transfer h 
													LEFT JOIN billit_customer bc2 ON bc2.sno = h.fund_transfer_to
													LEFT JOIN billit_customer bc ON bc.sno = h.from_account_no
													WHERE h.status != '5' AND bc2.unit_id IN ($divIds) AND h.unit_confirmation_status = 0
													ORDER BY h.sno DESC LIMIT 100";
											$result = execute_query($sql);
											$i = 1;
											if ($result && mysqli_num_rows($result) > 0) {
												while ($row = mysqli_fetch_assoc($result)) {
													echo '<tr>';
													echo '<td class="text-center">' . $i++ . '</td>';
													echo '<td>
															<div class="font-weight-bold">' . htmlspecialchars($row['order_no']) . '</div>
															<div class="text-muted small">' . date('d-M-Y', strtotime($row['transfer_date'])) . '</div>
														  </td>';
													echo '<td>' . htmlspecialchars($row['from_acc']) . '</td>';
													echo '<td class="text-right font-weight-bold">₹' . number_format($row['total_transfer_amount'], 2) . '</td>';
													echo '<td class="text-center"><span class="badge badge-warning">Pending</span></td>';
													echo '<td class="text-center" style="min-width: 200px;">';
													echo '<a href="fund_transfer_view.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> View</a>';
													echo '</td>';
													echo '</tr>';
												}
											} else {
												echo '<tr><td colspan="6" class="text-center py-4">No incoming fund transfers found.</td></tr>';

											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<script>
					function confirmFund(headerId, status) {
						const actionText = status == 1 ? "Accept" : "Reject";
						const promptMsg = status == 1 ? "Are you sure you want to Accept this fund? Please enter a remark:" : "Are you sure you want to Reject this fund? Please enter the reason/remark:";

						const remark = prompt(promptMsg);

						if (remark === null) return;

						if (remark.trim() === "") {
							alert("Remark is mandatory for " + actionText);
							return;
						}
						var data = { "term": "b", "header_id": headerId, "status": status, "remark": remark };
						$.ajax({
							url: 'scripts/ajax.php?id=confirm_fund',
							type: 'POST',
							data: data,
							dataType: 'json',
							success: function (response) {
								if (response.success) {
									location.reload();
								} else {
									alert("Error: " + response.message);
								}
							},
							error: function () {
								alert("AJAX error occurred.");
							}
						});
					}
				</script>
				<?php
				break;
			}
		}
		?>
				<?php
				page_footer_start();
				?>


				<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
						<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

						<script>
							function open_dropdown(id) {
								var upto_dropdown = document.getElementById('upto_dropdown').value;
								for (var i = 1; i < upto_dropdown; i++) {
									if (id == i) {
										if ($("#drop_" + i).css("display") == "none") {
											$("#drop_" + i).show();
										}
										else {
											$("#drop_" + i).hide();
										}
									}
									else {
										$("#drop_" + i).hide();
									}

								}
							}

						</script>
						<!--  Charts Plugin -->
						<script src="js/chartist.min.js"></script>

						<!-- Light Bootstrap Table DEMO methods, don't include it in your project! -->



						<script>
							type = ['', 'info', 'success', 'warning', 'danger'];


							demo = {
								initPickColor: function () {
									$('.pick-class-label').click(function () {
										var new_class = $(this).attr('new-class');
										var old_class = $('#display-buttons').attr('data-class');
										var display_div = $('#display-buttons');
										if (display_div.length) {
											var display_buttons = display_div.find('.btn');
											display_buttons.removeClass(old_class);
											display_buttons.addClass(new_class);
											display_div.attr('data-class', new_class);
										}
									});
								},

								checkScrollForTransparentNavbar: debounce(function () {
									$navbar = $('.navbar[color-on-scroll]');
									scroll_distance = $navbar.attr('color-on-scroll') || 500;

									if ($(document).scrollTop() > scroll_distance) {
										if (transparent) {
											transparent = false;
											$('.navbar[color-on-scroll]').removeClass('navbar-transparent');
											$('.navbar[color-on-scroll]').addClass('navbar-default');
										}
									} else {
										if (!transparent) {
											transparent = true;
											$('.navbar[color-on-scroll]').addClass('navbar-transparent');
											$('.navbar[color-on-scroll]').removeClass('navbar-default');
										}
									}
								}, 17),

								initDocChartist: function () {
									var dataSales = {
										labels: ['9:00AM', '12:00AM', '3:00PM', '6:00PM', '9:00PM', '12:00PM', '3:00AM', '6:00AM'],
										series: [
											[287, 385, 490, 492, 554, 586, 698, 695, 752, 788, 846, 944],
											[67, 152, 143, 240, 287, 335, 435, 437, 539, 542, 544, 647],
											[23, 113, 67, 108, 190, 239, 307, 308, 439, 410, 410, 509]
										]
									};

									var optionsSales = {
										lineSmooth: false,
										low: 0,
										high: 800,
										showArea: true,
										height: "245px",
										axisX: {
											showGrid: false,
										},
										lineSmooth: Chartist.Interpolation.simple({
											divisor: 3
										}),
										showLine: false,
										showPoint: false,
									};

									var responsiveSales = [
										['screen and (max-width: 640px)', {
											axisX: {
												labelInterpolationFnc: function (value) {
													return value[0];
												}
											}
										}]
									];

									Chartist.Line('#chartHours', dataSales, optionsSales, responsiveSales);

									<?php
									$monthly_sales = array();
									$date_start = "2019-01-01";
									$date_end = "2019-01-31";
									for ($i = 1; $i <= 12; $i++) {
										$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="' . $date_start . '" and dateofdispatch<="' . $date_end . '"';
										//echo $sql.'<br>';
										$date_start = date("Y-m-d", strtotime("+1 month", strtotime($date_start)));
										$date_end = date("Y-m-d", strtotime("+1 month", strtotime($date_end)));
										$q1 = mysqli_fetch_assoc(execute_query($sql));
										$monthly_sales[$i] = $q1['total'];
									}

									?>

									var data = {
										labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
										series: [
											[542, 443, 320, 780, 553, 453, 326, 434, 568, 610, 756, 895],
											[412, 243, 280, 580, 453, 353, 300, 364, 368, 410, 636, 695]
										]
									};

									var options = {
										seriesBarDistance: 10,
										axisX: {
											showGrid: false
										},
										height: "245px"
									};

									var responsiveOptions = [
										['screen and (max-width: 640px)', {
											seriesBarDistance: 5,
											axisX: {
												labelInterpolationFnc: function (value) {
													return value[0];
												}
											}
										}]
									];

									Chartist.Bar('#chartActivity', data, options, responsiveOptions);

									var dataPreferences = {
										series: [
											[25, 30, 20, 25]
										]
									};


									var optionsPreferences = {
										donut: true,
										donutWidth: 40,
										startAngle: 0,
										total: 100,
										showLabel: false,
										axisX: {
											showGrid: false
										}
									};

									Chartist.Pie('#chartPreferences', dataPreferences, optionsPreferences);

									Chartist.Pie('#chartPreferences', {
										labels: ['62%', '32%', '6%'],
										series: [62, 32, 6]
									});
								},

								initChartist: function () {

									var dataSales = {
										labels: ['9:00AM', '12:00AM', '3:00PM', '6:00PM', '9:00PM', '12:00PM', '3:00AM', '6:00AM'],
										series: [
											[287, 385, 490, 492, 554, 586, 698, 695, 752, 788, 846, 944],
											[67, 152, 143, 240, 287, 335, 435, 437, 539, 542, 544, 647],
											[23, 113, 67, 108, 190, 239, 307, 308, 439, 410, 410, 509]
										]
									};

									var optionsSales = {
										lineSmooth: false,
										low: 0,
										high: 800,
										showArea: true,
										height: "245px",
										axisX: {
											showGrid: false,
										},
										lineSmooth: Chartist.Interpolation.simple({
											divisor: 3
										}),
										showLine: false,
										showPoint: false,
									};

									var responsiveSales = [
										['screen and (max-width: 640px)', {
											axisX: {
												labelInterpolationFnc: function (value) {
													return value[0];
												}
											}
										}]
									];

									Chartist.Line('#chartHours', dataSales, optionsSales, responsiveSales);


									var dataPreferences = {
										series: [
											[25, 30, 20, 25]
										]
									};

									var optionsPreferences = {
										donut: true,
										donutWidth: 40,
										startAngle: 0,
										total: 100,
										showLabel: false,
										axisX: {
											showGrid: false
										}
									};

									Chartist.Pie('#chartPreferences', dataPreferences, optionsPreferences);
									<?php
									$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="2019-04-01" and dateofdispatch<="2019-06-30"';
									$q1 = mysqli_fetch_assoc(execute_query($sql));
									$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="2019-07-01" and dateofdispatch<="2019-09-30"';
									$q2 = mysqli_fetch_assoc(execute_query($sql));
									$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="2019-10-01" and dateofdispatch<="2019-12-31"';
									$q3 = mysqli_fetch_assoc(execute_query($sql));
									$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="2020-01-01" and dateofdispatch<="2020-03-31"';
									$q4 = mysqli_fetch_assoc(execute_query($sql));
									?>
									Chartist.Pie('#chartPreferences', {
										labels: ['Rs.<?php echo $q1['total']; ?>', 'Rs.<?php echo $q2['total']; ?>', 'Rs.<?php echo $q3['total']; ?>', 'Rs.<?php echo $q4['total']; ?>'],
										series: [<?php echo $q1['total']; ?>, <?php echo $q2['total']; ?>, <?php echo $q3['total']; ?>, <?php echo $q4['total']; ?>]
									});
								},

								initGoogleMaps: function () {
									var myLatlng = new google.maps.LatLng(40.748817, -73.985428);
									var mapOptions = {
										zoom: 13,
										center: myLatlng,
										scrollwheel: false, //we disable de scroll over the map, it is a really annoing when you scroll through page
										styles: [{ "featureType": "water", "stylers": [{ "saturation": 43 }, { "lightness": -11 }, { "hue": "#0088ff" }] }, { "featureType": "road", "elementType": "geometry.fill", "stylers": [{ "hue": "#ff0000" }, { "saturation": -100 }, { "lightness": 99 }] }, { "featureType": "road", "elementType": "geometry.stroke", "stylers": [{ "color": "#808080" }, { "lightness": 54 }] }, { "featureType": "landscape.man_made", "elementType": "geometry.fill", "stylers": [{ "color": "#ece2d9" }] }, { "featureType": "poi.park", "elementType": "geometry.fill", "stylers": [{ "color": "#ccdca1" }] }, { "featureType": "road", "elementType": "labels.text.fill", "stylers": [{ "color": "#767676" }] }, { "featureType": "road", "elementType": "labels.text.stroke", "stylers": [{ "color": "#ffffff" }] }, { "featureType": "poi", "stylers": [{ "visibility": "off" }] }, { "featureType": "landscape.natural", "elementType": "geometry.fill", "stylers": [{ "visibility": "on" }, { "color": "#b8cb93" }] }, { "featureType": "poi.park", "stylers": [{ "visibility": "on" }] }, { "featureType": "poi.sports_complex", "stylers": [{ "visibility": "on" }] }, { "featureType": "poi.medical", "stylers": [{ "visibility": "on" }] }, { "featureType": "poi.business", "stylers": [{ "visibility": "simplified" }] }]

									}
									var map = new google.maps.Map(document.getElementById("map"), mapOptions);

									var marker = new google.maps.Marker({
										position: myLatlng,
										title: "Hello World!"
									});

									// To add the marker to the map, call setMap();
									marker.setMap(map);
								},



							}

							<?php
							$monthly_sales = array();
							$monthly_receipts = array();
							$date_start = "2019-01-01";
							$date_end = "2019-01-31";
							for ($i = 1; $i <= 12; $i++) {
								$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="' . $date_start . '" and dateofdispatch<="' . $date_end . '"';
								$q1 = mysqli_fetch_assoc(execute_query($sql));
								$sql = 'SELECT sum(amount) as total FROM `customer_transactions` where timestamp>="' . $date_start . '" and timestamp<="' . $date_end . '" and type in ("RECEIPT", "RECIEPT")';
								//echo $sql.'###<br>';
								$receipts = mysqli_fetch_assoc(execute_query($sql));
								$date_start = date("Y-m-d", strtotime("+1 month", strtotime($date_start)));
								$date_end = date("Y-m-d", strtotime("+1 month", strtotime($date_end)));
								$monthly_sales[$i] = $q1['total'];
								$monthly_receipts[$i] = $receipts['total'];
							}

							?>

							var data = {
								labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
								series: [
									[<?php echo implode(", ", $monthly_sales); ?>],
									[<?php echo implode(", ", $monthly_receipts); ?>]
								]
							};

							var options = {
								seriesBarDistance: 10,
								axisX: {
									showGrid: false
								},
								height: "245px"
							};

							var responsiveOptions = [
								['screen and (max-width: 640px)', {
									seriesBarDistance: 5,
									axisX: {
										labelInterpolationFnc: function (value) {
											return value[0];
										}
									}
								}]
							];

							Chartist.Bar('#chartActivity', data, options, responsiveOptions);

							var dataPreferences = {
								series: [
									[25, 30, 20, 25]
								]
							};
						</script>
						<script type="text/javascript">
							$(document).ready(function () {

								demo.initChartist();

							});
						</script>
						<?php
						page_footer_end();
	}
}
?>