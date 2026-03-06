<?php
include("scripts/settings.php");
$msg='';
if(isset($_POST['submit'])) {
	if(isset($_POST['mobile_otp'])){
		$sql = 'select * from session where sno="'.$_SESSION['session_insert_id'].'"';
		$session_row = mysqli_fetch_assoc(execute_query($sql));
		$compare_otp = $session_row['sno'].'_'.$_POST['mobile_otp'];
		//echo $compare_otp.'>>'.$session_row['otp_verification'];
		$msg='<h1>Welcome '.$_SESSION['username'].'</h1>';
		if($compare_otp==$session_row['otp_verification']){
			$sql = 'update session set otp_verification="1" where sno='.$_SESSION['session_insert_id'];
			execute_query($sql);
			$get_msg = "Welcome ".$_SESSION['username'].", your OTP is verified.";
			send_sms($mobile,$get_msg);
		}
		else{
			$msg.='<h3>Invalid OTP.</h3>';
		}

	}
	elseif($_POST['username']!='' && $_POST['userpwd']!='') {
		 
		$sql = 'select * from users where userid="'.$_POST['username'].'"';
		//echo $sql;
		$result = execute_query($sql);
		if(mysqli_num_rows($result)!=0) {			
			
			$row = mysqli_fetch_array(execute_query($sql));
			if($_POST['userpwd']==$row['pwd']) {
				$sql='select * from user_access_detail where user_id = "'.$row['sno'].'"';
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
				if(!isset($_SESSION['authcode'])){
					$_SESSION['authcode']='';
				}
				
				$sql = 'select * from user_division where user_id="'.$row['sno'].'"';
				$result_user_division = execute_query($sql);
				if(mysqli_num_rows($result_user_division)!=0){
					while($row_user_division = mysqli_fetch_assoc($result_user_division)){
						$_SESSION['divisions'][] = $row_user_division['division_id'];
					}
				}
				
				
				$time = localtime();
		        $time = $time[2].':'.$time[1].':'.$time[0];
				//echo $time;
		        $_SESSION['starttime']=$time;
				
				$sql = "insert into session (user, s_id, s_start_date, s_start_time, last_active) values ('".$_SESSION['username']."','".$_SESSION['session_id']."','".$_SESSION['startdate']."','".$_SESSION['starttime']."', '".time()."')";
				execute_query($sql);
				$id = mysqli_insert_id($db);

				$_SESSION['session_insert_id'] = $id;
				
				
				$otp_verify = mysqli_fetch_array(execute_query("select * from general_settings where `desc`='otp_verification'"));
				$otp_verify = $otp_verify['rate'];
				$_SESSION['otp_verify'] = $otp_verify;
				if($otp_verify==1){
					$mobile;
					$otp = randomnumber();
					$sql = 'update session set otp_verification="'.$id.'_'.$otp.'" where sno='.$id;
					execute_query($sql);
					$get_msg = "Dear, ".$_SESSION['username']." one time verification code for your ERP Login is $otp. The code is valid for 30 mins only.";
					send_sms($mobile,$get_msg);
					
				}

				$msg='<h1>Welcome '.$_SESSION['username'].'</h1>';
				
				
				$response=2;
			}
			else {
				$msg .= '<h4 class="header text-center alert alert-danger">Please Enter Valid User Password</h4>';
				$response=1;
			}
		}
		else {
			 $msg .= '<h4 class="header text-center alert alert-danger">Please Enter Valid User Password</h4>';
				$response=1;
		}		 
	 }
	 else {
		 $msg .= '<h4 class="header text-center alert alert-danger">Please Enter User Detail</h4>';
		 $response=1;
	 }
 }
?>

<?php
page_header_start();
page_header_end();
if(!isset($_SESSION['session_id'])) {
?>	
<div class="wrapper wrapper-full-page">
        <!-- Navbar -->
        
        <!-- End Navbar -->
        <div class="full-page  section-image" data-color="black" data-image="images/login.jpg">
            <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
            <div class="content">
                <div class="container">
                    <div class="col-md-4 col-sm-6 ml-auto mr-auto login-page">
                        <form id="loginform" name="login" class="wufoo page" autocomplete="off" enctype="multipart/form-data" method="post" action="index.php">
                            <div class="card card-login">
                                <div class="card-header card-header-rose text-center ">
                                   	<h2 class="header text-center" style="font-size: 1.6rem">Project Tracker&trade; (<span class="pe-7s-study"></span>)</h2>
                                    <h6 class="header text-center">Login</h3>
                                    <?php echo $msg; ?>
                                </div>
                                <div class="card-body ">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>User ID</label>
                                            <input type="text" placeholder="Enter User ID" name="username" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" placeholder="Password" name="userpwd" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer ml-auto mr-auto">
                                    <button type="submit" name="submit" class="btn btn-warning btn-wd">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <div class="full-page-background" style="background-image: url(images/login.jpg) "></div></div>
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
                        <a href="http://www.weknowtech.in" target="_blank"><img src="images/logo-15.png" class="img-rounded"> Weknow Technologies</a>
                    </p>
                </nav>
            </div>
        </footer>
    </div>
<?php 
}
else {
	if($_SESSION['otp_verify']==1){
		$sql = 'select * from session where sno="'.$_SESSION['session_insert_id'].'"';
		$session_row = mysqli_fetch_assoc(execute_query($sql));
		if($session_row['otp_verification']!=1){
?>
<div class="wrapper wrapper-full-page">
        <!-- Navbar -->
        
        <!-- End Navbar -->
        <div class="full-page  section-image" data-color="black" data-image="images/login.jpg">
            <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
            <div class="content">
                <div class="container">
                    <div class="col-md-4 col-sm-6 ml-auto mr-auto login-page">
                        <form id="loginform" name="login" class="wufoo page" autocomplete="off" enctype="multipart/form-data" method="post" action="index.php">
                            <div class="card card-login">
                                <div class="card-header card-header-rose text-center ">
                                   	<h2 class="header text-center">Project&trade; Tracker (<span class="pe-7s-study"></span>)</h2>
                                    <h3 class="header text-center">Login</h3>
                                    <?php echo $msg; ?>
                                </div>
                                <div class="card-body ">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Enter OTP</label>
                                            <input type="mobile_otp" placeholder="Enter OTP" name="mobile_otp" class="form-control">
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
        <div class="full-page-background" style="background-image: url(images/login.jpg) "></div></div>
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
                        <a href="http://www.webprotechnologies.com" target="_blank"><img src="images/logo-15.png" class="img-rounded"> WebPro Technologies</a>
                    </p>
                </nav>
            </div>
        </footer>
    </div>
<?php
		}
		else{
			goto login;	
		}
	}
	else{
		login:
		page_sidebar();
		
		$sql = 'select * from uprnss_division';
		$result_division = execute_query($sql);
		$divisions =  mysqli_num_rows($result_division);
		
		$sql = 'select * from uprnss_district';
		$result_division = execute_query($sql);
		$districts =  mysqli_num_rows($result_division);
		
		$sql = 'select * from uprnss_department_name';
		$result_division = execute_query($sql);
		$departments =  mysqli_num_rows($result_division);
		
		$sql = 'select * from uprnss_project_temp';
		$result_division = execute_query($sql);
		$projects =  mysqli_num_rows($result_division);
		
		

?>
<style>
	.stat-icon{
		height:80px;
	}
</style>
   				<div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                            	<div class="row">
									<div class="col-md-3 text-right">
										<img src="images/icon.png" height="130">
									</div>
									<div class="col-md-9" >
										<h4 class="title"> &nbsp;उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.) <br/> UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH LTD.(UPRNSS).</h4>
									</div>                                
								</div>
                            </div>
                            <div class="content">
								<div class="row">
									<div class="col-2"></div>
									<div class="col-2 text-center">
										<img src="images/ic_state.png" class="img-fluid stat-icon">
										<br/>
										Divisions
										<br/>
										<h4 class="m-0 p-0 text-danger"><?php echo $divisions; ?></h4>
									</div>
									<div class="col-2 text-center">
										<img src="images/ic_district.png" class="img-fluid stat-icon">
										<br/>
										District
										<br/>
										<h4 class="m-0 p-0 text-danger"><?php echo $districts; ?></h4>
									</div>
									<div class="col-2 text-center">
										<img src="images/ic_department.png" class="img-fluid stat-icon">
										<br/>
										Departments
										<br/>
										<h4 class="m-0 p-0 text-danger"><?php echo $departments; ?></h4>
									</div>
									<div class="col-2 text-center">
										<img src="images/ic_project.png" class="img-fluid stat-icon">
										<br/>
										Projects
										<br/>
										<h4 class="m-0 p-0 text-danger"><?php echo $projects; ?></h4>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
				</div>
    			<div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="content" >
                                <div> <img src="images/Dashboard.png" style="height:550px; width:850px; "> </div>
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
page_footer_start();
?>


    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

    <script>
        function open_dropdown(id){
            var upto_dropdown = document.getElementById('upto_dropdown').value;
            for (var i = 1; i < upto_dropdown; i++) {
                if(id == i){
                    if($("#drop_"+i).css("display") == "none"){
                        $("#drop_"+i).show();
                    }
                    else{
                        $("#drop_"+i).hide();
                    }
                }
                else{
                     $("#drop_"+i).hide();
                }
                
            }
        }

    </script>
	<!--  Charts Plugin -->
	<script src="js/chartist.min.js"></script>

	<!-- Light Bootstrap Table DEMO methods, don't include it in your project! -->
	<script>
type = ['','info','success','warning','danger'];


	demo = {
		initPickColor: function(){
			$('.pick-class-label').click(function(){
				var new_class = $(this).attr('new-class');
				var old_class = $('#display-buttons').attr('data-class');
				var display_div = $('#display-buttons');
				if(display_div.length) {
				var display_buttons = display_div.find('.btn');
				display_buttons.removeClass(old_class);
				display_buttons.addClass(new_class);
				display_div.attr('data-class', new_class);
				}
			});
		},

		checkScrollForTransparentNavbar: debounce(function() {
				$navbar = $('.navbar[color-on-scroll]');
				scroll_distance = $navbar.attr('color-on-scroll') || 500;

				if($(document).scrollTop() > scroll_distance ) {
					if(transparent) {
						transparent = false;
						$('.navbar[color-on-scroll]').removeClass('navbar-transparent');
						$('.navbar[color-on-scroll]').addClass('navbar-default');
					}
				} else {
					if( !transparent ) {
						transparent = true;
						$('.navbar[color-on-scroll]').addClass('navbar-transparent');
						$('.navbar[color-on-scroll]').removeClass('navbar-default');
					}
				}
		}, 17),

		initDocChartist: function(){
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
			for($i=1; $i<=12; $i++){
				$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="'.$date_start.'" and dateofdispatch<="'.$date_end.'"';
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
			  labels: ['62%','32%','6%'],
			  series: [62, 32, 6]
			});
		},

		initChartist: function(){

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
			  labels: ['Rs.<?php echo $q1['total'];?>','Rs.<?php echo $q2['total'];?>','Rs.<?php echo $q3['total'];?>', 'Rs.<?php echo $q4['total'];?>'],
			  series: [<?php echo $q1['total'];?>, <?php echo $q2['total'];?>, <?php echo $q3['total'];?>, <?php echo $q4['total'];?>]
			});
		},

		initGoogleMaps: function(){
			var myLatlng = new google.maps.LatLng(40.748817, -73.985428);
			var mapOptions = {
			  zoom: 13,
			  center: myLatlng,
			  scrollwheel: false, //we disable de scroll over the map, it is a really annoing when you scroll through page
			  styles: [{"featureType":"water","stylers":[{"saturation":43},{"lightness":-11},{"hue":"#0088ff"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"hue":"#ff0000"},{"saturation":-100},{"lightness":99}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#808080"},{"lightness":54}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#ece2d9"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#ccdca1"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#767676"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"color":"#ffffff"}]},{"featureType":"poi","stylers":[{"visibility":"off"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#b8cb93"}]},{"featureType":"poi.park","stylers":[{"visibility":"on"}]},{"featureType":"poi.sports_complex","stylers":[{"visibility":"on"}]},{"featureType":"poi.medical","stylers":[{"visibility":"on"}]},{"featureType":"poi.business","stylers":[{"visibility":"simplified"}]}]

			}
			var map = new google.maps.Map(document.getElementById("map"), mapOptions);

			var marker = new google.maps.Marker({
				position: myLatlng,
				title:"Hello World!"
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
			for($i=1; $i<=12; $i++){
				$sql = 'SELECT sum(total_amount1) as total FROM `invoice_sale` where dateofdispatch>="'.$date_start.'" and dateofdispatch<="'.$date_end.'"';
				$q1 = mysqli_fetch_assoc(execute_query($sql));
				$sql = 'SELECT sum(amount) as total FROM `customer_transactions` where timestamp>="'.$date_start.'" and timestamp<="'.$date_end.'" and type in ("RECEIPT", "RECIEPT")';
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
    	$(document).ready(function(){

        	demo.initChartist();

    	});
	</script>
<?php		
		page_footer_end();
	}
}
?>
