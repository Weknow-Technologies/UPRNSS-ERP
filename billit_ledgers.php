<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");
$msg='';
$tab=1;
if(isset($_POST['saveForm'])){
	foreach($_POST as $k=>$v){
		$_POST[$k] = strtoupper($v);
	}
	if($_POST['edit_sno']==''){
        add_customer($_POST);
        if(!mysqli_error($db)){
			$msg .= 'Successful Inserted.';
		}
		else{
			$msg .= 'Error LG-01 : '.mysqli_error($db);
		}
	} else{
		$sql = 'update billit_customer set 
		`cus_name` = "'.$_POST['cus_name'].'",		
		`address` = "'.$_POST['address'].'",		
		`add_2` = "'.$_POST['add_2'].'",		
		`state` = "'.$_POST['state'].'",	
		`city` = "'.$_POST['city'].'",
		`zipcode` = "'.$_POST['zipcode'].'",		
		`country` = "'.$_POST['country'].'",		
		`mobile` = "'.$_POST['mobile'].'",		
		`cus_type` = "'.$_POST['cus_type'].'",			
		`opening_balance` = "'.$_POST['opening_balance'].'",			
		`tin` = "'.$_POST['tin'].'",		
		`adhar_no` = "'.$_POST['adhar_no'].'",		
		`pan` = "'.$_POST['pan'].'",		
		`parent` = "'.$_POST['parent'].'",
		`ifsc` = "'.$_POST['ifsc'].'",
		`account_no` = "'.$_POST['account_no'].'",
		`unit_id` = "'.$_POST['unit_id'].'",
		`department_id` = "'.$_POST['department_id'].'",
		`edited_by` = "'.$_SESSION['usersno'].'",
		`edition_time` = "'.date("Y-m-d H:i:s").'",
		`opening_date` = "'.$_POST['opening_date'].'"
		 where sno='.$_POST['edit_sno'];
		execute_query($sql);
		if(mysqli_error($db)){
			$msg .= 'Error UP-01 : '.mysqli_error($db).' >> '.$sql;
		}
		else{
			$msg .= 'Update Successfull.';
		}
	}
}

if(isset($_GET['id'])){
	$sql = 'select * from billit_customer where sno='.$_GET['id'];
	$ledger = mysqli_fetch_assoc(execute_query($sql));
	if($ledger['state']=='' || $ledger['state']=='0'){
		$ledger['state'] = $state;
	}
	if($ledger['country']==''){
		$ledger['country']='India ';
	}
}

if(isset($_GET['delid'])){
	$sql = 'delete from billit_customer where sno='.$_GET['delid'];
	execute_query($sql);
	if(!mysqli_error($db)){
		$msg .= 'Deleted.';
	}
	else{
		$msg .= 'LG-03 : '.mysqli_error($db);
	}
}

if(isset($_GET['mid'])){
	$admin = ', `admin_remarks`=CONCAT(admin_remarks,"LedgerMerge: Original Ledger:'.get_ledger($_GET['alt']).' New Ledger:'.get_ledger($_GET['mid']).'. On: '.date("Y-m-d H:i:s").'. By: '.$_SESSION['username'].'#")';
	$array = array("billit_barcode_new" => "customer_id", "billit_contra_entry" => "by", "billit_contra_entry" => "to", "billit_customer_transactions" => "cust_id", "billit_invoice_estimate" => "supplier_id", "billit_invoice_issue" => "supplier_id", "billit_invoice_payment" => "cust_id", "billit_invoice_purchase" => "supplier_id", "billit_invoice_purchase_ply" => "supplier_id", "billit_invoice_purchase_revert" => "supplier_id", "billit_invoice_quotation" => "supplier_id", "billit_invoice_receipt" => "cust_id", "billit_invoice_receive" => "supplier_id", "billit_invoice_sale" => "supplier_id", "billit_invoice_sale_ply" => "supplier_id", "billit_invoice_sale_pos" => "supplier_id", "billit_invoice_sale_quotation" => "supplier_id", "billit_invoice_sale_revert" => "supplier_id", "billit_invoice_sale_temp" => "supplier_id", "billit_journal_entry" => "by", "billit_journal_entry" => "to", "billit_stock_estimate" => "supplier_id", "billit_stock_issue" => "supplier_id", "billit_stock_purchase" => "supplier_id", "billit_stock_purchase_ply" => "supplier_id", "billit_stock_purchase_revert" => "supplier_id", "billit_stock_quotation" => "supplier_id", "billit_stock_receive" => "supplier_id", "billit_stock_sale" => "supplier_id", "billit_stock_sale_ply" => "supplier_id", "billit_stock_sale_pos" => "supplier_id", "billit_stock_sale_quotation" => "supplier_id", "billit_stock_sale_revert" => "supplier_id", "billit_stock_sale_temp" => "supplier_id");
	foreach($array as $k=>$v){
		$sql = 'update '.$k.' set `'.$v.'`='.$_GET['mid'].' '.$admin.' where `'.$v.'`='.$_GET['alt'];
		execute_query($sql);
		if(mysqli_error($db)){
		    $msg .= 'Error : '.mysqli_error($db).' >> '.$sql;
		}
		else{
			$msg .= 'Done >> '.$k.'. Updated Rows : '.mysqli_affected_rows($db);
		}
	}
}
page_header_start();
page_header_end();
page_sidebar();
?>
<script type="text/javascript" language="javascript">
function alternate_value(id){
	var alternate = prompt("Please enter product id to merge with this id.","");
	if(!alternate){
		alert("Can not merge without product id.");
		return false;
	}
	else{
		window.open("billit_ledgers.php?mid="+id+"&alt="+alternate, '_self');
		return true;
	}
}

function update_parent(val){
	if(val=='12'){
		var txt = '<select name="tax_rates" id="tax_rates"></select>';
		$("#td_parent").append(txt);
		$.getJSON("scripts/billit_ajax.php?id=tax_rates&term=a", function(result){
			var txt1='';
			$.each(result, function(i, field){
				$("#tax_rates").append('<option value="'+field.id+'">GST @ '+field.id+'%</option>');
			});
		});
	}
}
</script>
        <div class="row">
			<div class="col-md-12">
                <?php
                    if($msg != ''){
                        echo '<h5>' . alert($msg) . '</h5>';
                    }
                ?>
				<form id="add_product" name="add_product" class="wufoo leftLabel page1" autocomplete="off" enctype="multipart/form-data" method="post" novalidate action="<?php echo $_SERVER['PHP_SELF']; ?>">
        			<table class=" table table-striped table-hover table-bordered">
        				<tr>
        					<td>Parent</td>
							<td id="td_parent"><select name="parent" tabindex="<?php echo $tab++ ;?>" onChange="update_parent(this.value)">
								<?php
								$sql='select * from billit_pl_heads';
								$row_heads=execute_query($sql);
								while($heads_details=mysqli_fetch_array($row_heads)){
									echo '<option value="'.$heads_details['sno'].'"';
									if(isset($_GET['id'])){
										if($ledger['parent']==$heads_details['sno']){
											echo ' selected="selected" ';
										}
									}
									else{
										if($heads_details['sno']==32){
											echo ' selected="selected"';
										}
									}
									echo '>'.$heads_details['description'].'</option>';
								}
								?>
								</select>
                                </select> <button type="button" class="btn btn-info" onclick="showParentModal();"><i class="far fa-plus-square"></i></button>
                            </td>
       						<td>GSTIN</td>
							<td><input id="tin" name="tin" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['tin'];} ?>" type="text"></td>
                            <td>Visibility</td>
                            <td>
                                <select name="visibility" id="visibility" class="form-control">
                                    <option value="public" <?php if(isset($_GET['id'])){if($ledger['visibility']=='PUBLIC'){ echo ' selected="selected" ';}}   ?>>Public</option>
                                    <option value="private" <?php if(isset($_GET['id'])){if($ledger['visibility']=='PRIVATE'){ echo ' selected="selected" ';}}   ?>>Private</option>
                            </td>
        				</tr>
						<tr>
                            <td>Ledger Name</td>
                            <td><input id="cus_name" name="cus_name" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['cus_name'];} ?>" type="text"></td>
                            <td>PAN</td>
                            <td><input id="pan" name="pan" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['pan'];} ?>" type="text"></td>
							<td>Mobile</td>
                			<td><input id="mobile" name="mobile" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['mobile'];} ?>" type="text"></td>
                		</tr>
       					<tr>
                            <td>Aadhaar No.</td>
                            <td><input id="adhar_no" name="adhar_no" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['adhar_no'];} ?>" type="text"></td>
       						<td>State</td>
							<td><select id="state" name="state" tabindex="<?php echo $tab++ ;?>">
							<?php
                                $sql = 'select * from general_settings where `desc`="state"';
                                $default_state = mysqli_fetch_assoc(execute_query($sql));
                                $sql = 'select * from billit_state_name';
                                $res_state = execute_query($sql);
                                while($row_state = mysqli_fetch_array($res_state)){
                                    echo '<option value="'.$row_state['state_code'].'" ';
                                    if(isset($_GET['id'])){
                                        if(strtoupper(trim($row_state['state_code']))==strtoupper(trim($ledger['state']))){
                                            echo ' selected="selected" ';
                                        }
                                    }
                                    else{
                                        if(strtoupper(trim($row_state['state_code']))==$default_state['rate']){
                                            echo ' selected="selected"';
                                        }
                                    }
                                    echo '>'.$row_state['indian_states'].'</option>';
                                }
							?>
							</select></td>
       						<td>Address</td>
							<td><input id="address" name="address" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['address'];} ?>" type="text"></td>
						</tr>
						<tr>
                            <td>Address 2</td>
                            <td><input id="add_2" name="add_2" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['add_2'];} ?>" type="text"></td>
							<td>City</td>
							<td><input id="city" name="city" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['city'];} ?>" type="text"></td>
							<td>PIN Code</td>
							<td><input id="zipcode" name="zipcode" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['zipcode'];} ?>" type="text"></td>
						</tr>
						<tr>
                            <td>Country</td>
                            <td><select id="country" name="country" tabindex="<?php echo $tab++ ;?>">
                                    <?php
                                    $sql = 'select * from billit_country_name';
                                    $res_state = execute_query($sql);
                                    while($row_state = mysqli_fetch_array($res_state)){
                                        echo strtoupper($row_state['countryname']).'>>'.strtoupper($ledger['country']);
                                        echo '<option value="'.$row_state['countryname'].'" ';
                                        if(isset($_GET['id'])){
                                            if(strtoupper(trim($row_state['countryname']))==strtoupper(trim($ledger['country']))){
                                                echo ' selected="selected" ';
                                            }
                                        }
                                        else{
                                            if(strtoupper(trim($row_state['countryname']))=='INDIA'){
                                                echo ' selected="selected" ';
                                            }
                                        }
                                        echo '>'.$row_state['countryname'].'</option>';
                                    }
                                    ?>
                                </select></td>
							<td>Type</td>
								<td><select name="cus_type" id="cus_type" tabindex="<?php echo $tab++ ;?>">
								<option></option>
								<?php
								$sql='select * from billit_cust_type';
								$row_type=execute_query($sql);
								while($type_details=mysqli_fetch_array($row_type)){
									echo '<option value="'.$type_details['sno'].'"';
									if(isset($_GET['id'])){
										if($ledger['cus_type']==$type_details['sno']){
											echo ' selected="selected" ';
										}
									}
									echo '>'.$type_details['type'].'</option>';
								}
								?>
								</select> <br/><button type="button" class="btn btn-info" data-toggle="modal" data-target="#createModalType" onClick="$('#edit_sno_type').val(''); $('#type_name').val('');"><i class="far fa-plus-square"></i></button> <button type="button" class="btn btn-warning" data-toggle="modal" data-target="" onClick="edit_type();"><i class="far fa-edit"></i></button> <button type="button" class="btn btn-danger" data-toggle="modal" data-target=""><i class="fa fa-trash" onClick="delete_type();"></i></button></td>
                            <td>IFSC</td>
                            <td><input id="ifsc" name="ifsc" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['ifsc'];} ?>" type="text"></td>
                        </tr>
						<tr>
							<td>Opening</td>
							<td><input id="opening_balance" name="opening_balance" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['opening_balance'];} ?>" type="text"></td>
                            <td>Opening Date</td>
                            <td>
                                <input type="date" name="opening_date" tabindex="<?php echo $tab++; ?>"
                                       value="<?php echo isset($_GET['id']) ? $ledger['opening_date'] : date('Y-m-d'); ?>">
                            </td>
                            <td>Account No</td>
                            <td><input id="account_no" name="account_no" tabindex="<?php echo $tab++ ;?>" value="<?php if(isset($_GET['id'])){echo $ledger['account_no'];} ?>" type="text"></td>
						</tr>
						<tr>
							<td>Link to Unit</td>
							<td>
								<select name="unit_id" id="unit_id" class="form-control">
								<option value=""></option>
								<?php
								$sql = 'SELECT * FROM `uprnss_division`';
								$result_division = execute_query($sql);
								while($row_division = mysqli_fetch_assoc($result_division)){
									echo '<option value="'.$row_division['s_no'].'"';
									if(isset($_GET['id'])){if($ledger['unit_id']==$row_division['s_no']){ echo ' selected="selected" ';}}
									echo '>'.$row_division['division_name'].'</option>';
								}
								?>
								</select>
							</td>
                            <td>Link to Department</td>
                            <td><select name="department_id" id="department_id" class="form-control">
                                    <option value=""></option>
                                    <?php
                                    $sql = 'SELECT * FROM `uprnss_department_name`';
                                    $result_dpt = execute_query($sql);
                                    while($row_dpt = mysqli_fetch_assoc($result_dpt)){
                                        echo '<option value="'.$row_dpt['sno'].'" ';
                                        if(isset($_GET['id'])){if($ledger['department_id']==$row_dpt['sno']){ echo ' selected="selected" ';}}
                                        echo '>'.$row_dpt['department_name_hindi'].'</option>';
                                    }
                                    ?>
                                </select></td>
						</tr>
						<tr>
							<td colspan="2">
							<input type="hidden" name="edit_sno" value="<?php if(isset($_GET['id'])){echo $_GET['id'];} ?>" />
							<input id="saveForm" name="saveForm" class="btn btn-success" type="submit" value="Add/Edit"></td>
						</tr>
					</table>
    <div style="width:100%;">
        <?php
            $sql = 'select * from billit_customer where 1=1';

            if($_SESSION['usertype']!='sadmin'){
                $sql .= ' and (visibility="public" or unit_id in ('.implode(",", $_SESSION['divisions']).'))';
            }
            $sql .= ' order by sno DESC';
            $result_data = execute_query($sql);
        ?>
        <table class="table table-striped table-hover table-bordered" id="ledgerTable">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Company Name</th>
                    <th>Other Info</th>
                    <th>Address</th>
                    <th>State</th>
                    <th>Mobile</th>
                    <th>Opening</th>
                    <th>GSTIN</th>
                    <th>Parent</th>
                    <th>Id</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th>Merge</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while($row = mysqli_fetch_array($result_data)){
                echo '<tr>
                      <td>'.$i++.'</td>
                      <td>'.$row['cus_name'].'</td>
                      <td>';
                if($row['fname']!=''){
                    echo 'Contact Person : '.$row['fname'];
                }
                if($row['adhar_no']!=''){
                    echo '<br>Aadhar : '.$row['adhar_no'];
                }
                if($row['pan']!=''){
                    echo '<br>PAN : '.$row['pan'];
                }
                if($row['cus_occupation']!=''){
                    echo '<br>Occupation : '.$row['cus_occupation'];
                }
                if($row['visibility']!=''){
                    echo '<br>Visibility : '.$row['visibility'];
                }
                if($row['department_id']!=''){
                    echo '<br>Department : '.get_department($row['department_id']);
                }
                if($row['unit_id']!=''){
                    echo '<br>Division : '.get_division($row['unit_id']);
                }
                if($row['parent_ledger']!=''){
                    echo '<br>Parent Ledger : '.get_ledger($row['parent_ledger']);
                }
                if($row['cus_type']!=''){
                    echo '<br>Type : '.get_type_cust($row['cus_type']);
                }
                if($row['category']!=''){
                    echo '<br>Category : '.get_category($row['category']);
                }
                if($row['account_no']!=''){
                    echo '<br>Account No : '.$row['account_no'].' ('.$row['ifsc'].')';
                }
                echo '</td>
                      <td>'.$row['address'];
                if($row['add_2']!=''){
                    echo '<br>'.$row['add_2'];
                }
                if($row['city']!=''){
                    echo '<br>'.$row['city'];
                }
                if($row['zipcode']!=''){
                    echo '<br>PIN : '.$row['zipcode'];
                }
                if($row['country']!=''){
                    echo '<br>'.$row['country'];
                }
                echo '</td>
                <td>'.get_state($row['state']).'</td>
                <td>'.$row['mobile'];
                if($row['mob_2']!=''){
                    echo '<br>'.$row['mob_2'];
                }
                if($row['mob_3']!=''){
                    echo '<br>'.$row['mob_3'];
                }
                if($row['mob_4']!=''){
                    echo '<br>'.$row['mob_4'];
                }
                echo '</td>
                       <td>'.$row['opening_balance'].'</td>
                       <td>'.$row['tin'].'</td>  
                       <td>'.get_parent($row['parent']).'</td>
                       <td>'.$row['sno'].'</td>
                       <td> <a href="billit_ledgers.php?id='.$row['sno'].'"><span class="far fa-edit" title="Edit"></span></a> </td>
                       <td> <a href="billit_ledgers.php?delid='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" title="Delete"></span></a></td>
                       <td><a href="#" onclick="return alternate_value('.$row['sno'].')"><span class="fa fa-compress-alt" title="Merge"></span></a> </td> 
                       </tr>';
            }
            ?>
            </tbody>
        </table>
       </form>
	</div>
	<div class="modal fade" id="createModalParent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Create New Parent</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form name="create_parent" id="create_parent" action="scripts/billit_ajax.php?id=create_parent" method="get"><p>Enter Details</p>
						<table class=" table table-striped table-hover table-bordered">
							<tr>
								<td>Parent</td>
								<td><input id="parent_name" name="parent_name" tabindex="<?php echo $tab++ ;?>" value="" type="text" class="form-control"><input type="hidden" name="edit_sno_parent" id="edit_sno_parent"></td>
							</tr>
						</table>
					</form>
				</div>
				<div class="modal-footer">
					<div class="col-md-12 text-center" id="ajax_loader_parent" style="display:none;"><img src="images/loading_transparent.gif"></div>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onClick="create_new_parent();">Save changes</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="createModalType" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
                <div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Create New Type</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form name="create_ledger" id="create_ledger" action="scripts/billit_ajax.php?id=create_type" method="get"><p>Enter Details</p>
						<table class=" table table-striped table-hover table-bordered">
							<tr>
								<td>Type</td>
								<td><input id="type_name" name="type_name" tabindex="<?php echo $tab++ ;?>" value="" type="text" class="form-control"><input type="hidden" name="edit_sno_type" id="edit_sno_type"></td>
							</tr>
						</table>
					</form>
				</div>
				<div class="modal-footer">
					<div class="col-md-12 text-center" id="ajax_loader" style="display:none;"><img src="images/loading_transparent.gif"></div>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onClick="create_new_type();">Save changes</button>
				</div>
			</div>
		</div>
	</div>
<?php page_footer_start();?>
<script>
    function showParentModal(){
        $('#edit_sno_parent').val('');
        $('#parent_name').val('');
        $('#createModalParent').modal('show');
    }

    function create_new_parent(){
		var parent_name = $('#parent_name').val();
		var edit_sno_parent = $('#edit_sno_parent').val();
		if(parent_name == ''){
			alert('Please enter parent name');
			return false;
		}
		var finaldata = 'term=t&id=create_parent&parent_name='+parent_name+'&edit_sno_parent='+edit_sno_parent;
		$('#ajax_loader_parent').show();
		$.ajax({
		  type: "GET",
		  url: "scripts/billit_ajax.php",
		  data: finaldata,
		  cache: false,
		  success: function(response){
			  $('#createModalParent').modal('hide');
			  $('#ajax_loader_parent').hide();
			  alert('Parent added successfully!');
			  location.reload();
		  },
		  error: function(xhr, status, error){
			  $('#ajax_loader_parent').hide();
			  alert('Error: ' + error);
		  }
		});
	}

	function create_new_type(){
		var type_name = $('#type_name').val();
		var edit_sno_type = $('#edit_sno_type').val();
		var finaldata = '';
		finaldata = 'term=t&id=create_type&type_name='+type_name+'&edit_sno_type='+edit_sno_type;
		var form = $('#create_ledger');
		document.getElementById('ajax_loader').style.display = 'block';
		$.ajax({
		  type: "GET",
		  url: form.attr('action'),
		  data: finaldata,
		  cache: false,
		  //success: result
		  complete: function(response){
			  $('#createModalType').modal('hide');
			  document.getElementById('ajax_loader').style.display = 'none';
			  $("#cus_type").html(response.responseText);
		  }
		});
	}
	
	function edit_type(){
		if($('#cus_type option:selected').html()==''){
			$('#createModaltype').modal('hide');
			alert('Please select a type to edit.');
		}
		else{
			$('#createModalType').modal('show');
			$('#edit_sno_type').val($('#cus_type').val()); 
			$('#type_name').val($('#cus_type option:selected').html());
		}
	}
	
	function delete_type(){
		var type = $('#cus_type').val();
		var conf = confirm('Are you sure you want to delete a type?');
		finaldata = 'term=t&id=delete_type&type='+type;
		if(conf==true){
			$.ajax({
			  type: "GET",
			  url: 'scripts/billit_ajax.php',
			  data: finaldata,
			  cache: false,
			  //success: result
			  complete: function(response){
				  $("#cus_type").html(response.responseText);
				  alert('Completed');
			  }
			});
		}
	}

    $(document).ready(function () {
        $('#ledgerTable').DataTable({
            paging: true,
            fixedHeader: true,
            pageLength: 25
        });
    });
	
$( function() {
	$("#tin123").autocomplete({
		source: function (request, response) {
			request.keyword = request.term;
			$.post("https://www.mastersindia.co/gst-number-search-and-gstin-verification/", request, response);
		},
		minLength: 3,
		select: function( event, ui ) {
			log( ui.item ?
				"Selected: " + ui.item.value + " aka " + ui.item.id :
				"Nothing selected, input was " + this.value );
		},
		select: function( event, ui ) {

		}
	});
	
	var options_supplier = {
		source: function (request, response){
			$.getJSON("scripts/billit_ajax.php?id=cust_name",request, response);
		},
		minLength: 1,
		select: function( event, ui ) {
		    $("[name='supplier']").val(ui.item.label);
			$('#supplier_sno').val(ui.item.id);
		}
	};

	$("input#supplier").on("keydown.autocomplete", function() {
		$(this).autocomplete(options_supplier);
		$("input#supplier").removeClass("ui-autocomplete-loading");
			
	});
});
</script>
<?php page_footer_end();?>