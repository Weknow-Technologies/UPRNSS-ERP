<?php
include("scripts/settings.php");
$msg='';
$tab=1;
page_header_start();
page_header_end();
?>
<style>
	body {
			font-family: Arial, sans-serif;
		}
	table, th, td {
		border: 1px solid black;
		border-collapse: collapse;
	}

	.heads{
		display:flex;
		justify-content:center;
		align-items:center;
		padding-inline:2rem;
		position:relative;
        margin-left:2rem;
	}
	.cont-info{
        font-size:1em;
        font-weight:600;
    }
    #overlays{
        z-index:1;
        opacity:0.15;
        position: fixed;
        top: 50%;
        left: 50%;
        -ms-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%); 
        display:block!important;
    }
	@media print {
        .box{
            height:99vh!important;
        }
        table, figure {
            page-break-inside: avoid!important;
        }
		.heads{
			text-align:center;
		}
		table{
			width:90%;
            margin:auto;
            border-radius:5px;
		}
        @page{
            /* size:a4 landscape; */
            margin:0.4in;
        }
        #overlays{
            display:block;
            opacity:0.2;
            width:45%!important;
            top: 50%!important;
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
	}	
    
   
   
	</style>
<div class="container box" style="border:1px solid #333;border-radius:10px;height:auto;">
    <div class="">
        <img src="images/icon.png"  id="overlays" style=" " alt="overlay image" >
    </div>
    <div class="heads" style="border-bottom:1px solid #222;" >
        <div class="cont-img" style="position:absolute;left:1rem;">
            <img src="images/icon.png" height="90">
        </div>
        <div class="cont-info text-center">
            <h4 class="title" style="text-decoration:underline;"> 
                उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.) 
            </h4> 
            <h5>
                (पूर्ववर्ती नाम पैकफेड)
                <br>
                राजकीय निर्माण एजेन्सी
            </h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card strpied-tabled-with-hover">
                <?php 
                    $creation_time="";
                    $departmemt="";
                    $subdepartment="";
                    $godetial="";
                    $project_name="";

                    $invoicesql="SELECT * FROM invoice_new_project  WHERE sno='".$_GET['id']."'";
                    $invoiceres=mysqli_query($db,$invoicesql);
                    while($invoicerow=mysqli_fetch_assoc($invoiceres)){
                        $sql = 'select * from uprnss_department_name where sno="'.$invoicerow['department'].'"';
                        $department = mysqli_fetch_assoc(execute_query($sql));

                        if($invoicerow['sub_department_id']!=""){
                            $sql = 'select * from uprnss_sub_department where sno="'.$invoicerow['sub_department_id'].'"';
                            $subdepartment = mysqli_fetch_assoc(execute_query($sql));
                            $subdepartment=$subdepartment['sub_department'];
                        }
                        
                        $creation_time=$invoicerow['creation_time'];
                        $departmemt=$department['department_name_hindi'];
                        $gonum=$invoicerow['go_number'];
                        $godate=$invoicerow['go_date'];
                        $project_name_hindi=$invoicerow['project_name_hindi'];
                        // $project_name_eng=$invoicerow['project_name_english'];
                    }

                ?>
                <div class="" style="display:flex;justify-content:space-between;margin:1rem;">
                    <div class="date">ENTRY DATE: <span style="font-weight:600;"><?php echo date("d-m-Y",strtotime($creation_time));?></span> </div>
                    <div class="presentdate">DATE: <span style="font-weight:600;"><?php echo date("d-m-Y");?></span>  </div>
                </div>
                <div class="department" style="display:flex;justify-content:space-between;margin:1rem;">
                    <div class="dep">DEPARTMENT : <span style="font-weight:600;"><?php echo $departmemt;?></span> </div>
                    <?php 
                    //if subdepartment exist then show subdepartment 
                        if($subdepartment!=""){
                            ?>
                                <div class="subdep ">Sub Department: <span style="font-weight:700;"><?php echo  $subdepartment;?></span> </div>
                            <?php
                        }
                    ?>
                </div>
                <div class="godetail" style="margin:1rem;" >
                    <p>GO DETAILS : <span style="font-weight:600;"><?php echo  $gonum." / ".$godate?></span> </p>
                </div>
                <div class="projectname" style="margin:1rem;">PROJECT Name: <span style="font-weight:700;"><?php echo $project_name_hindi ?></span></div>
                <div class="card-body table-full-width table-responsive m-auto">
                    <table class="table table-hover table-striped table-bordered" cellpadding="6">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>ERP Code</th>
                                <th>Division</th>
                                <th>District</th>
                                <th>Project Sub Name</th>
                                <th>Project Sub Name Hindi</th>
                                <th>Scheme</th>
                            </tr>	
                        </thead>
                        <tbody>
                            <?php
                            $sql = 'SELECT *,transaction_new_project.sno as tsno ,transaction_new_project.edition_time as etime FROM `transaction_new_project` left join invoice_new_project on transaction_new_project.invoice_id =invoice_new_project.sno where invoice_new_project.sno="'.$_GET['id'].'"';
                            // echo $sql;
                            $result_invoice_details = execute_query($sql);
                            $i=1;
                                while($row_invoice_details = mysqli_fetch_assoc($result_invoice_details)){
                                    // print_r($row_invoice_details);
                                    // $sql= 'select * from uprnss_project_temp LEFT JOIN transaction_new_project on transaction_new_project.sno=uprnss_project_temp.new_project_trans_id';
                                    $sql= "select * from uprnss_project_temp where new_project_trans_id='".$row_invoice_details['tsno']."' ";
                                    // echo $sql;
                                    $row_temp = mysqli_fetch_assoc(mysqli_query($db, $sql));
                                    
                                    //For ERP NUMBER we are getting uprnss_project_temp sno
                                    $erptempsno=$row_temp['sno'];

                                    $sql = 'select * from uprnss_department_name where sno="'.$row_invoice_details['department'].'"';
                                    $department = mysqli_fetch_assoc(execute_query($sql));

                                    //For ERP NUMBER we are getting uprnss_deparment Sort Number                                    
                                    $erpdepname=$department['department_sort_name'];
                                    $erptime=$row_temp['creation_time'];
                                    /////////////


                                    //creating ERP Number 
                                    // department short number then creation date of project temp creation than prject temp sno
                                    $erpnum=$erpdepname."/".date("Y",strtotime($erptime))."/".$erptempsno;
                                    //////////


                                    $sql = 'select * from uprnss_project_scheme where sno="'.$row_invoice_details['scheme'].'"';
                                    // echo $sql;
                                    $scheme = mysqli_fetch_assoc(execute_query($sql));
                                    $scheme = execute_query($sql);
                                    if(mysqli_num_rows($scheme)!=0){
                                    $scheme = mysqli_fetch_assoc($scheme);
                                    }
                                    else{
                                        unset($scheme);
                                        $scheme['scheme_name_english'] = '';
                                    }
                                    
                                    
                                    $sql = 'select * from uprnss_division where s_no="'.$row_invoice_details['division_id'].'"';
                                    // echo $sql;
                                    $div = execute_query($sql);
                                    if(mysqli_num_rows($div)!=0){
                                    $div = mysqli_fetch_assoc($div);
                                    }
                                    else{
                                        unset($div);
                                        $div['division_name'] = '';
                                    }
                                
                                    $sql = 'select * from uprnss_district where sno="'.$row_invoice_details['district_id'].'"';
                                    $district = execute_query($sql);
                                    if(mysqli_num_rows($district)!=0){
                                    $district = mysqli_fetch_assoc($district);
                                    }
                                    else{
                                        unset($district);
                                        $district['district_name_english'] = '';
                                    }
                                    
                                    $dateString = $row_invoice_details['creation_time'];
                                    $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);
                                    if ($dateTime !== false) {
                                        $year = $dateTime->format("Y");
                                            // echo $year;
                                        } else {
                                            // echo 'Invalid date format';
                                    }
                                    echo '<tr>

                                    <td>'.$i++.'</td>
                                    <td>'.$erpnum.'</td>
                                    <td>'.$div['division_name'].'</td>
                                    <td>'.$district['district_name_english'].'</td>
                                    <td>'.$row_invoice_details['sub_project_name'].'</td>
                                    <td>'.$row_invoice_details['sub_project_name_hindi'].'</td>
                                    <td>'.$scheme['scheme_name_english'].'</td>
                                    </tr>';
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
page_footer_start();
?>


    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

    <script>
		function update_locations(){
			var tehsil = $("#tehsil").val();
			$.ajax({
				url: "scripts/ajax.php?id=villages&term="+tehsil,
				dataType:"json"
			})
			.done(function( data ) {

				var txt = '<label>Villages</label><select name="villages" id="villages" class="form-control">';
				$.each(data, function(k, value){
					txt += '<option value="'+value.id+'">'+value.location_name+'</option>';
				});
				txt += '</select>';
				console.log(txt);
				$("#villages_group").html(txt);
				$("#villages_group").show();
			});
		}
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

<?php		
page_footer_end();
?>
