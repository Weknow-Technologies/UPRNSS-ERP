<?php
date_default_timezone_set('Asia/Calcutta');
$time = mktime(true);
include("settings.php");
include("billit_settings.php");

$q = htmlspecialchars(urldecode(strtoupper($_GET["term"])), ENT_QUOTES);
if (!$q) return;

if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}
else {
	$id='';
}
$result = array();

if($id=='cust_name') {
	$sql = 'select * from billit_customer where (cus_name like "%'.$q.'%" or sno like "'.$q.'%" or mobile like "%'.$q.'%" or tin like "%'.$q.'%" or account_no like "%'.$q.'%") limit 20'; 
	//echo $sql;
	$res = execute_query($sql);
	while($row = mysqli_fetch_array($res)) {
		$row['balance']=get_cust_balace('1970-01-01',date("Y-m-d"),$row['sno']);
		$sql = 'select * from billit_invoice_sale where supplier_id="'.$row['sno'].'" order by sno desc limit 1';
		$last_invoice = execute_query($sql);
		if(mysqli_num_rows($last_invoice)!=0){
			$last_invoice = mysqli_fetch_assoc($last_invoice);
			$last_invoice_sno = $last_invoice['sno'];
			$last_invoice = 'Rs.'.$last_invoice['total_amount1'].'. Dt: '.date("d-m-Y", strtotime($last_invoice['dateofdispatch'])).' Inv#: '.$last_invoice['invoice_no'];
		}
		else{
			$last_invoice = '';
			$last_invoice_sno = '';
		}
		array_push($result, array("id"=>$row['sno'], "label"=>$row['cus_name'], "cust_name"=>$row['cus_name'], "address" => $row['address'], "address2" => $row['add_2'], "city" => $row['city'], "mobile" => $row['mobile'], "tin" => $row['tin'], "aadhar" => $row['adhar_no'], "state" => $row['state'], "state_name" => get_state($row['state']), "opening" => $row['opening_balance'], "category"=>$row['parent'], "balance"=>$row['balance'],"zipcode"=>$row['zipcode'], "last_invoice"=>$last_invoice, "last_invoice_sno"=>$last_invoice_sno));
	}
}
elseif($id=='create_type'){
	if($_GET['type_name']!=''){
		$sql='select * from billit_cust_type where type="'.$_GET['type_name'].'"';
		$result = execute_query($sql);
		if(mysqli_num_rows($result)==0){
			if($_GET['edit_sno_type']!=''){
				$sql = 'update billit_cust_type set type="'.$_GET['type_name'].'", edited_by="'.$_SESSION['username'].'", edition_time="'.date("Y-m-d H:i:s").'" where sno='.$_GET['edit_sno_type'];
			}
			else{
				$sql = 'insert into billit_cust_type(type, created_by, creation_time) values("'.$_GET['type_name'].'" , "'.$_SESSION['username'].'","'.date("Y-m-d H:i:s").'")';	
			}
			execute_query($sql);
		}
	}
	$sql='select * from billit_cust_type';
	$row_type=execute_query($sql);
	echo '<option value=""></option>';
	while($type_details=mysqli_fetch_array($row_type)){
		echo '<option value="'.$type_details['sno'].'">'.$type_details['type'].'</option>';
	}
	return 0;
}
elseif($id=='delete_type'){
	if($_GET['type']!=''){
		$sql='delete from billit_cust_type where sno="'.$_GET['type'].'"';
		$result = execute_query($sql);
	}
	$sql='select * from billit_cust_type';
	$row_type=execute_query($sql);
	echo '<option value=""></option>';
	while($type_details=mysqli_fetch_array($row_type)){
		echo '<option value="'.$type_details['sno'].'">'.$type_details['type'].'</option>';
	}
	return 0;
}
elseif($id=='create_category'){
	if($_GET['category_name']!=''){
		$sql='select * from billit_cust_category where category="'.$_GET['category_name'].'"';
		$result = execute_query($sql);
		if(mysqli_num_rows($result)==0){
			if($_GET['edit_sno_category']!=''){
				$sql = 'update billit_cust_category set category="'.$_GET['category_name'].'", edited_by="'.$_SESSION['username'].'", edition_time="'.date("Y-m-d H:i:s").'" where sno='.$_GET['edit_sno_category'];
			}
			else{
				$sql = 'insert into billit_cust_category(category, created_by, creation_time) values("'.$_GET['category_name'].'" , "'.$_SESSION['username'].'","'.date("Y-m-d H:i:s").'")';	
			}
			execute_query($sql);
		}
	}
	$sql='select * from billit_cust_category';
	$row_category=execute_query($sql);
	echo '<option value=""></option>';
	while($category_details=mysqli_fetch_array($row_category)){
		echo '<option value="'.$category_details['sno'].'">'.$category_details['category'].'</option>';
	}
	return 0;
}
elseif($id=='delete_category'){
	if($_GET['category']!=''){
		$sql='delete from billit_cust_category where sno="'.$_GET['category'].'"';
		$result = execute_query($sql);
	}
	$sql='select * from billit_cust_category';
	$row_category=execute_query($sql);
	echo '<option value=""></option>';
	while($category_details=mysqli_fetch_array($row_category)){
		echo '<option value="'.$category_details['sno'].'">'.$category_details['category'].'</option>';
	}
	return 0;
}

elseif($id=='create_parent'){
	if($_GET['parent_name']!=''){
		$sql='select * from billit_pl_heads where description="'.$_GET['parent_name'].'"';
		$result = execute_query($sql);
		if(mysqli_num_rows($result)==0){
			if($_GET['edit_sno_parent']!=''){
				$sql = 'update billit_pl_heads set description="'.$_GET['parent_name'].'", edited_by="'.$_SESSION['username'].'", edition_time="'.date("Y-m-d H:i:s").'" where sno='.$_GET['edit_sno_parent'];
			}
			else{
				$sql = 'insert into billit_pl_heads(description, created_by, creation_time) values("'.$_GET['parent_name'].'" , "'.$_SESSION['username'].'","'.date("Y-m-d H:i:s").'")';	
			}
			execute_query($sql);
		}
	}
	$sql='select * from billit_pl_heads';
	$row_parent=execute_query($sql);
	echo '<option value=""></option>';
	while($parent_details=mysqli_fetch_array($row_parent)){
		echo '<option value="'.$parent_details['sno'].'">'.$parent_details['description'].'</option>';
	}
	return 0;
}
elseif($id=='delete_parent'){
	if($_GET['parent']!=''){
		$sql='delete from billit_pl_heads where sno="'.$_GET['parent'].'"';
		$result = execute_query($sql);
	}
	$sql='select * from billit_pl_heads';
	$row_parent=execute_query($sql);
	echo '<option value=""></option>';
	while($parent_details=mysqli_fetch_array($row_parent)){
		echo '<option value="'.$parent_details['sno'].'">'.$parent_details['description'].'</option>';
	}
	return 0;
}

// Handle POST request for adding parent
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_parent') {
	$name = mysqli_real_escape_string($db, $_POST['name']);
	$mobile = mysqli_real_escape_string($db, $_POST['mobile']);
	$address = mysqli_real_escape_string($db, $_POST['address']);
	$table_type = isset($_POST['table_type']) ? $_POST['table_type'] : 'billit_customer';
	
	if($table_type == 'billit_pl_heads') {
		// Add to billit_pl_heads table
		$sql = "INSERT INTO billit_pl_heads (description, created_by, creation_time) VALUES ('$name', '".$_SESSION['username']."', '".date('Y-m-d H:i:s')."')";
		execute_query($sql);
		$insert_id = mysqli_insert_id($db);
		
		if($insert_id) {
			echo json_encode(['success' => true, 'id' => $insert_id, 'name' => $name]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Failed to add parent head']);
		}
	} else {
		// Add to billit_customer table (original logic)
		$sql = "SELECT sno FROM billit_customer WHERE cus_name = '$name'";
		$result = execute_query($sql);
		
		if(mysqli_num_rows($result) > 0) {
			echo json_encode(['success' => false, 'message' => 'Parent with this name already exists']);
			exit;
		}
		
		$sql = "INSERT INTO billit_customer (cus_name, mobile, address, created_by, creation_time) VALUES ('$name', '$mobile', '$address', '".$_SESSION['username']."', '".date('Y-m-d H:i:s')."')";
		execute_query($sql);
		
		$insert_id = mysqli_insert_id($db);
		
		if($insert_id) {
			echo json_encode(['success' => true, 'id' => $insert_id, 'name' => $name]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Failed to add parent']);
		}
	}
	exit;
}


if(empty($result)!=true){
	echo json_encode($result);
}
?>