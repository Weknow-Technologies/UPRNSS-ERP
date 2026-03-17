<?php

function get_state($id){
	global $state;
	global $db;
	$id = $id!=''?$id:0;
	$sql = 'select * from billit_state_name where state_code="'.$id.'"';
	//echo $sql;
	$result = execute_query($sql);
	if(mysqli_num_rows($result)==0){
		$sql = 'SELECT * FROM `general_settings` where `desc`="state"';
		$state = mysqli_fetch_assoc(execute_query($sql));
		
		$sql = 'select * from billit_state_name where state_code="'.abs($state['rate']).'"';
		//echo $sql;
		$result = execute_query($sql);

	}
	if(mysqli_error($db)){
		return $state;
	}
	$row = mysqli_fetch_assoc($result);
	return $row['indian_states'];
}

function get_parent($id){
	if($id=='' || $id==0){
		$id = 30;
	}
	$sql='select * from billit_pl_heads where sno="'.$id.'"';
	//echo $sql;
	$parent=mysqli_fetch_array(execute_query($sql));
	return $parent['description'];
}

function add_customer($data_or_name, ...$other_args){
    global $db;
    
    // Check if we got an array (new style) or multiple arguments (old style)
    if (is_array($data_or_name)) {
        $data = $data_or_name;
    } else {
        // Old style: mapping positional arguments to keys expected by the function
        $data = [
            'cus_name'         => $data_or_name,
            'address'          => $other_args[0] ?? '',
            'add_2'            => $other_args[1] ?? '',
            'city'             => $other_args[2] ?? '',
            'state'            => $other_args[3] ?? '',
            'zipcode'          => $other_args[4] ?? '',
            'country'          => $other_args[5] ?? '',
            'mobile'           => $other_args[6] ?? '',
            'tin'              => $other_args[7] ?? '',
            'adhar_no'         => $other_args[8] ?? '',
            'fname'            => $other_args[9] ?? '',
            'mob_2'            => $other_args[10] ?? '',
            'mob_3'            => $other_args[11] ?? '',
            'mob_4'            => $other_args[12] ?? '',
            'cus_type'         => $other_args[13] ?? '',
            'cus_occupation'   => $other_args[14] ?? '',
            'dob'              => $other_args[15] ?? '',
            'opening_balance'  => $other_args[16] ?? 0,
            'category'         => $other_args[17] ?? '',
            'parent'           => $other_args[18] ?? '',
            'ifsc'             => $other_args[19] ?? '',
            'account_no'       => $other_args[20] ?? '',
            'visibility'       => $other_args[21] ?? '',
            'parent_ledger'    => $other_args[22] ?? '',
            'department_id'    => $other_args[23] ?? '',
            'unit_id'          => $other_args[24] ?? '',
            'opening_date'     => $other_args[25] ?? '',
            'pan'              => $other_args[26] ?? ''
        ];
    }

    $name            = trim($data['cus_name'] ?? '');
    $address         = trim($data['address'] ?? '');
    $address2        = trim($data['add_2'] ?? '');
    $city            = trim($data['city'] ?? '');
    $state           = trim($data['state'] ?? '');
    $zip             = trim($data['zipcode'] ?? '');
    $country         = trim($data['country'] ?? '');
    $mobile          = trim($data['mobile'] ?? '');
    $tin             = trim($data['tin'] ?? '');
    $aadhar          = trim($data['adhar_no'] ?? '');
    $type            = trim($data['cus_type'] ?? '');
    $opening_balance = trim($data['opening_balance'] ?? 0);
    $ifsc            = trim($data['ifsc'] ?? '');
    $account_no      = trim($data['account_no'] ?? '');
    $visibility      = trim($data['visibility'] ?? '');
    $parent_ledger   = trim($data['parent'] ?? '');
    $department_id   = trim($data['department_id'] ?? '');
    $unit_id         = trim($data['unit_id'] ?? '');
    $opening_date    = trim($data['opening_date'] ?? '');
    if($opening_date == ''){
        $opening_date = date("Y-m-d");
    }
    $pan             = trim($data['pan'] ?? '');

    /* duplicate mobile check */
    $sql = 'SELECT rate FROM general_settings WHERE `desc`="duplicate_mobile"';
    $duplicate_mobile = mysqli_fetch_array(execute_query($sql));
    $duplicate_mobile = $duplicate_mobile['rate'];
    if($duplicate_mobile == 0 && $mobile != ''){
        $sql = 'SELECT sno FROM billit_customer 
                WHERE mobile="'.$mobile.'" 
                OR mob_2="'.$mobile.'" 
                OR mob_3="'.$mobile.'" 
                OR mob_4="'.$mobile.'"';
        $result = execute_query($sql);
        if(mysqli_num_rows($result) != 0){
            $supplier = mysqli_fetch_array($result);
            return $supplier['sno'];
        }
    }

    /* duplicate GST check */
    if($tin != ''){
        $sql = 'SELECT sno FROM billit_customer WHERE tin="'.$tin.'"';
        $result = execute_query($sql);
        if(mysqli_num_rows($result) != 0){
            $supplier = mysqli_fetch_array($result);
            return $supplier['sno'];
        }
    }

    /* insert customer */
    $sql = 'INSERT INTO billit_customer 
    (cus_name, address, add_2, city, state, zipcode, country, mobile, tin, adhar_no, pan, cus_type, opening_balance, ifsc, account_no, created_by, creation_time, visibility, parent_ledger, department_id, unit_id, opening_date)
    VALUES
    ("'.$name.'", "'.$address.'", "'.$address2.'", "'.$city.'", "'.$state.'", "'.$zip.'", "'.$country.'", "'.$mobile.'", "'.$tin.'", "'.$aadhar.'", "'.$pan.'", "'.$type.'", "'.$opening_balance.'", "'.$ifsc.'", "'.$account_no.'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'", "'.$visibility.'", "'.$parent_ledger.'", "'.$department_id.'", "'.$unit_id.'", "'.$opening_date.'")';
    execute_query($sql);
    return insert_id();
}

function get_cust_balace($from,$to,$id, $in_out=''){
	$cust_balanace=0;
	$sql = 'select * from billit_customer where sno='.$id;
	$customer = mysqli_fetch_array(execute_query($sql));
	$pl_heads = array("DirectIncome"=>11, "DirectExpense"=>10, "IndirectIncome"=>19, "IndirectExpense"=>18);
		
	if($from!='' && $to!=''){
		$cust_opening = 0;
		if(!in_array($customer['parent'], $pl_heads)){
			$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type in ("PAYMENT", "sale", "purchase_revert") and cust_id='.$id;
			//echo $sql_trans.'<br>';
			$opening_dr_trans = mysqli_fetch_array(execute_query($sql_trans));

			$sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp<"'.$from.'" and `by`='.$id;
			$opening_journal_dr = mysqli_fetch_array(execute_query($sql_journal));

			$sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp<"'.$from.'" and `to`='.$id;
			$opening_contra_dr = mysqli_fetch_array(execute_query($sql_contra));

			$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type="debit_note" and cust_id='.$id;
			$opening_debit_note = mysqli_fetch_array(execute_query($sql_trans));

			$opening_tot_dr = $opening_dr_trans['trans'] + $opening_journal_dr['journal'] + $opening_contra_dr['contra'] + $opening_debit_note['trans'];

			$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type="credit_note" and cust_id='.$id;
			$opening_credit_note = mysqli_fetch_array(execute_query($sql_trans));

			$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type in ("RECIEPT","purchase", "RECEIPT", "sale_revert") and cust_id='.$id;
			$opening_cr_trans = mysqli_fetch_array(execute_query($sql_trans));

			$sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp<"'.$from.'" and `to`='.$id;
			$opening_journal_cr = mysqli_fetch_array(execute_query($sql_journal));

			$sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp<"'.$from.'" and `by`='.$id;
			$opening_contra_cr = mysqli_fetch_array(execute_query($sql_contra));

			$opening_tot_cr = $opening_cr_trans['trans'] + $opening_journal_cr['journal'] + $opening_contra_cr['contra'] + $opening_credit_note['trans'];

			$cust_opening = (float)$customer['opening_balance'] + ((float)$opening_tot_dr-(float)$opening_tot_cr);
			//echo $cust_opening.'@@.';
		}
		else{
			if($from=='1970-01-01'){
				return $cust_balanace;
			}
			//echo $customer['cus_name'].' >> '.$cust_opening.' >> '.$from.'<br>';
		}
		
		$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type in ("PAYMENT","sale", "purchase_revert") and cust_id='.$id;
		$dr_trans = mysqli_fetch_array(execute_query($sql_trans));

		$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type="debit_note" and cust_id='.$id;
		$debit_note=mysqli_fetch_array(execute_query($sql_trans));
		
		$sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.$id;
		$contra_dr = mysqli_fetch_array(execute_query($sql_contra));

		$sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.$id;
		$journal_dr = mysqli_fetch_array(execute_query($sql_journal));
		
		$tot_dr = $dr_trans['trans'] + $journal_dr['journal'] + $contra_dr['contra'] + $debit_note['trans'];
		
		$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type in ("RECIEPT","purchase", "RECEIPT", "sale_revert") and cust_id='.$id;
		$cr_trans = mysqli_fetch_array(execute_query($sql_trans));

		$sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type="credit_note" and cust_id='.$id;
		$credit_note=mysqli_fetch_array(execute_query($sql_trans));

		$sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.$id;
		$journal_cr = mysqli_fetch_array(execute_query($sql_journal));

		$sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.$id;
		$contra_cr = mysqli_fetch_array(execute_query($sql_contra));
		
		$tot_cr = $cr_trans['trans'] + $journal_cr['journal'] + $contra_cr['contra'] + $credit_note['trans'];
		
		$closing = $tot_dr-$tot_cr;
		
		if($id==4){
			//echo $cust_opening.'## Cr:'.$tot_cr.'##Dr:'.$tot_dr.'##'.$closing.'<br><br>';
		}
		
		if($in_out==''){

			$cust_balanace = $cust_opening + $closing;
		}
		elseif($in_out=='in'){
			$cust_balanace = $cr_trans['trans'] + $journal_dr['journal'] + $contra_dr['contra'] + $debit_note['trans'];
		}
		elseif($in_out=='out'){
			//echo $cr_trans['trans'].'@@'.$dr_trans['trans'];
			$cust_balanace = $dr_trans['trans'] + $journal_cr['journal'] + $contra_cr['contra'] + $credit_note['trans'];
		}
		
		if($cust_balanace<0){
			if($customer['parent']==11 || $customer['parent']==19){
				return abs($cust_balanace);
			}
		}
		return $cust_balanace;
	}
}

function get_ledger_category($id){
	$sql = 'select * from billit_cust_category where sno="'.$id.'"';
	$result = execute_query($sql);
	$row = mysqli_fetch_assoc($result);
	if(isset($row['category'])){
		return $row['category'];	
	}
	else{
		return '';
	}
	
}
function get_type_cust($id){
	$sql='select * from billit_cust_type where sno="'.$id.'"';
	//echo $sql.'<br>';
	$type=mysqli_fetch_array(execute_query($sql));
	return $type['type'];
	}
function get_ledger_type($id){
	$sql = 'select * from billit_cust_type where sno="'.$id.'"';
	$result = execute_query($sql);
	$row = mysqli_fetch_assoc($result);
	if(isset($row['type'])){
		return $row['type'];	
	}
	else{
		return '';
	}
	
}

function get_branch($id){
	if(!is_numeric($id)){
		return strtoupper($id);
	}
	$sql = 'select * from billit_branches where sno="'.$id.'"';
	$result = execute_query($sql);
	$row = mysqli_fetch_assoc($result);
	return $row['name'];
}

function get_ledger($sno){
	$sql = 'select * from billit_customer where sno="'.$sno.'"';
	$row = mysqli_fetch_array(execute_query($sql));
	if(isset($row['cus_name'])){
		return $row['cus_name']; 
	}
	return '';
}

?>