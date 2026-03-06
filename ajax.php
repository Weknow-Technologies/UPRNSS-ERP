<?php
date_default_timezone_set('Asia/Calcutta');
$time = mktime(true);
include("settings.php");

$q = htmlspecialchars(urldecode(strtoupper($_REQUEST["term"])), ENT_QUOTES);
if (!$q) return;

if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}
else {
	$id='';
}
$data = array();

if($id=='villages'){
	$sql = 'select * from location_village where parent='.$q;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "location_name"=>$row['location_name']);
	}
}
elseif($id=='villages_selected'){
	$sql = 'select location_village.sno as sno, location_village.location_name as location_name from plv_users_villages left join location_village on location_village.sno = village_id where user_id='.$_GET['plv_id'];
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "location_name"=>$row['location_name']);
	}
}
elseif($id=='dist'){
	$sql = 'SELECT uprnss_district.sno as sno, uprnss_district.district_name_english as district_name FROM `uprnss_project_temp` left join uprnss_district on uprnss_district .sno = district_id where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and department_id="'.$_POST['val'].'" group by district_id';
	//echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "district_name"=>$row['district_name']);
	}
}

elseif($id=='proj'){
	$sql = 'SELECT uprnss_project_temp.sno as sno, uprnss_project_temp.project_name_hindi as project_name_hindi FROM `uprnss_project_temp` left join uprnss_district on uprnss_district .sno = district_id where (status="0" or status is null) and uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and department_id="'.$_POST['dept'].'" and district_id="'.$_POST['val'].'"';
	//echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "project_name_hindi"=>$row['project_name_hindi']);
	}
}
elseif($id=='proj_detail'){
	$sql = 'select * from uprnss_project_temp where sno="'.$_POST['val'].'"';
	$project = mysqli_fetch_assoc(execute_query($sql));
	foreach($project as $k=>$v){
		$data[$k] = $v;
	}
	
}
elseif($id=='sub_dep'){
	$sql = 'SELECT * FROM uprnss_sub_department where department_id="'.$_POST['val'].'" ';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "sub_department_hindi"=>$row['sub_department_hindi']);
	}
}
elseif($id=='scheme'){
	$sql = 'SELECT * FROM uprnss_project_scheme where department_id="'.$_POST['val'].'" ';
	if(isset($_POST['subval'])){
		$sql .= ' and sub_department_id="'.$_POST['subval'].'"';
	}
	//echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "scheme_name_hindi"=>$row['scheme_name_hindi']);
	}
}
elseif($id=='sub_scheme'){
	$sql = 'SELECT * FROM uprnss_project_sub_scheme where scheme_id="'.$_POST['val'].'" ';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "sub_scheme_hindi"=>$row['sub_scheme_hindi']);
	}
}
elseif($id=='designation'){
	$sql = 'SELECT * FROM dp_designation where grade_id="'.$_POST['val'].'" order by abs(sort_no)';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "designation"=>$row['designation']);
	}
}
elseif($id=='employee'){
	$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "full_name"=>$row['full_name']);
	}
}
elseif($id=='payband'){
	//$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	$sql = 'select * from master_pay_band where pay_scale ="'.$_POST['val'].'"';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "band_name"=>$row['band_name']);
	}
}
elseif($id=='paylevel'){
	//$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	$sql = 'select * from master_pay_level where pay_band_id ="'.$_POST['val'].'"';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "level_name"=>$row['level_name']);
	}
}
elseif($id=='basicpay'){
	//$sql = 'SELECT * FROM dp_personal_info where employee_designation_id="'.$_POST['val'].'" order by full_name ASC';
	$sql = 'select * from master_pay_level_grade where level_name ="'.$_POST['val'].'"';
	// echo $sql;
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$data[] = array("id"=>$row['sno'], "basic_pay"=>$row['basic_pay']);
	}
}
elseif($id=='location'){
	if($_POST['val']=='office'){
		$sql = 'select * from uprnss_division where s_no in ("'.implode(",", $_SESSION['divisions']).'")';
		$result = execute_query($sql);
		$row = mysqli_fetch_assoc($result);
		$txt = '<div class="col-md-2">
				<label>Latitude</label>
				<input type="text" id="lat" disabled="disabled" value="'.$row['latitude'].'" class="form-control">
				<label>Longitude</label>
				<input type="text" id="long" disabled="disabled" value="'.$row['longitude'].'" class="form-control">
				<button type="button" class="btn btn-info" onClick="getLocation();">लोकेशन रिफ्रेश करें</button>
			</div>
			<div class="col-md-10" id="map_container">
				<iframe id="googlemap" src="https://maps.google.com/maps?q='.$row['latitude'].','.$row['longitude'].'&hl=en&z=13&amp;output=embed" width="100%" height="100%" style="border:1px solid; border-radius:10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>';
		$data[] = array("latitude"=>$row['latitude'], "longitude"=>$row['longitude']);
	}
}


if(empty($data)!=true){
	echo json_encode($data);
}
?>