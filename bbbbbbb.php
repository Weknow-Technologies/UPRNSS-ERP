<?php
include("scripts/settings.php");
$msg='';
$tab=1;

?>

<?php
page_header_start();
?>

<link href="css/multistepform.css" rel="stylesheet" type="text/css" media="all" />
<?php
page_header_end();
page_sidebar();

?>		



	
			
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">


<!--  (D)  Sundry Creditors                 -->

			<div class="row"  class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-full-width table-responsive">
							<table class="table table-hover table-striped">
								<thead>
									<tr>
										<th>Sr.No.</th>
										<th>Particulars Liabilties</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<td>i</td>
										<td>Balance Of Nirman Work</td>
										<td>
											<input type="text" class="form-control" id="blance_of_nirman_work" name="blance_of_nirman_work" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>D</td>
										<th>Sundry Creditors</th>
										<td></td>
									</tr>
									<tr>
										<td>i</td>
										<td>Firm</td>
										<td>
											<input type="text" class="form-control" id="sundry_creditors" name="sundry_creditors" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>ii</td>
										<td>Societies</td>
										<td>
											<input type="text" class="form-control" id="societies" name="societies" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>iii</td>
										<td>Other</td>
										<td>
											<input type="text" class="form-control" id="other" name="other" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
								</div>
						</div>
					</div>
				</div>
			</div>
			
<!-- E(1)  Other Liabilties 1(satutory Liabilties)    -->
			
			<div class="row" class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-responsive">
							<table class="table table- table-hover table-striped">
								<thead>
									<tr>
										<th>Sr.No.</th>
										<th>Other Liabilties</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<td>E (1)</td>
										<th>Satutory Liabilties</th>
										<td></td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>Trade Tax/VAT/Commercial Tax Payable</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>Income Tax Firm Payable</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>Entry Tax</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iv)</td>
										<td>Sales Tax</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(v)</td>
										<td>Stamp Duty</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vi)</td>
										<td>Labour Cess</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vii)</td>
										<td>GST(CGST+SGST)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(viii)</td>
										<td>GST TDS(CGST+SGST)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ix)</td>
										<td>IGST</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(x)</td>
										<td>Service Tax(Pariyojana)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
								</div>
						</div>
					</div>
				</div>
			</div>
			
<!-- E(2)  Other Liabilties 2(satutory Liabilties)    -->			
			
			<div class="row" class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-responsive">
							<table class="table table- table-hover table-striped">
								<thead>
									<tr>
										<th>Sr.No.</th>
										<th>Other Liabilties</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<td>E (2)</td>
										<th>Other Liabilties & Provision</th>
										<td></td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>Employee Security</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>Earnest Money</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>Payment Withheld/Retention(10%, 20% & 40%)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iv)</td>
										<td>Inter Division Adv. (Payable)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(v)</td>
										<td>OSL (OSl management/awaruddh a/c/o)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vi)</td>
										<td>TAC Recovery</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vii)</td>
										<td>Bag Sales & Purchases</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(viii)</td>
										<td>Recovery From Employees or Advance from Employees</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ix)</td>
										<td>Recovery</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(x)</td>
										<td>HO Other a/c</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xi)</td>
										<td>HO Nirman Karya a/c</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xii)</td>
										<td>HO P&L a/c Payable</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xiii)</td>
										<td>Intrest Payable to Tourism Department</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xiv)</td>
										<td>Royalty Payable to contractor</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xv)</td>
										<td>Intrest Payable to BRGF</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xvi)</td>
										<td>Intrest ACA</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xvii)</td>
										<td>Intrest(Saving)Payable to Niti Ayog</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xviii)</td>
										<td>Supplier/Contractor/Architect Securities</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xix)</td>
										<td>Tender Security/Tender Sale</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xx)</td>
										<td>Regsitration Security(Division)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxi)</td>
										<td>Amount Under Reconcillation</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxii)</td>
										<td>Project Fund (Blance Amount)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxiii)</td>
										<td>Reserve Fund</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxiv)</td>
										<td>DRDA Blance</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxv)</td>
										<td>Saansad Nidhi</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxvi)</td>
										<td>Intrest Payable to HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxvii)</td>
										<td>Centage Payable to HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxviii)</td>
										<td>Tender Fees Payable to HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xxix)</td>
										<td>Ruban Mission Intrest</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
								</div>
						</div>
					</div>
				</div>
			</div>
			
			
<!-- (F)  (Particulars Assets )    -->
			
			<div class="row" class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-responsive">
							<table class="table table- table-hover table-striped">
								<thead>
									<tr>
										<th>Sr.No.</th>
										<th>Particulars Assets</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<th>(f)</th>
										<th>Fixed Assets</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>Dead Stock</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>Furniture & Fixtures</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>Electrical/Electronic Goods</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iv)</td>
										<td>Office Equipments</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(v)</td>
										<td>Biometrci Machine</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vi)</td>
										<td>Plants & Machinary</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vii)</td>
										<td>Road Roller</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(viii)</td>
										<td>Vehicle</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ix)</td>
										<td>Loose Tools</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(x)</td>
										<td>Generator</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xi)</td>
										<td>Computer</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xii)</td>
										<td>Software Development</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
								</div>
						</div>
					</div>
				</div>
			</div>
			
			
<!-- (G,H,I,J)  (Particulars Assets )    -->
			
			<div class="row"class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-responsive">
							<table class="table table- table-hover  table-striped">
								<thead >
									<tr>
										<th>Sr.No.</th>
										<th>Particulars Assets</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<th>(G)</th>
										<th>Investment/FDR</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>FDR</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>Bank Guarantee</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>Accured Intrest on FDR</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<th>(H)</th>
										<th>Cash & Cash Equivalents</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>Cash in Hand</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>Cash At Bank</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<th>(I)</th>
										<th>Stock at Site</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<th>(J)</th>
										<th>Trade Receivable/Advabces Against Works</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>With Firms</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>With Societies</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>With Others</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iv)</td>
										<td>With Employees</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
							<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			
<!-- (K)  (Particulars Assets )    -->
			
			<div class="row" class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-responsive">
							<table class="table table- table-hover table-striped">
								<thead>
									<tr>
										<th>Sr.No.</th>
										<th>Particulars Assets</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<th>(K)</th>
										<th>Bkance With Revenue Authorities</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>From 16 A Regarding Bank Intrest</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>From 16 A Regarding Construction Work</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>Trade Tax</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iv)</td>
										<td>Trade Tax Security</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(v)</td>
										<td>Service Tax (Div)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vi)</td>
										<td>Entry Tax</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vii)</td>
										<td>Sales Tax</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(viii)</td>
										<td>IGST a/c</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ix)</td>
										<td>LabourCess</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(x)</td>
										<td>Royalty</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xi)</td>
										<td>GST (CGST+SGST)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xii)</td>
										<td>TDS GST (CGST+SGST)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xiii)</td>
										<td>IGST</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
								</div>
						</div>
					</div>
				</div>
			</div>
			
			
<!-- (L)  (Other Assets & Advance )    -->
			
			<div class="row" class="step">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<div class="card-header ">
							<h4 class="card-title"></h4>
						</div>
						<div class="card-body table-responsive">
							<table class="table table- table-hover table-striped">
								<thead>
									<tr>
										<th>Sr.No.</th>
										<th>Particulars Assets</th>
										<th>Amount</th>
									</tr>	
								</thead>
								<tbody>
									<tr>
										<th>(L)</th>
										<th>Other Assets & Advance</th>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(i)</td>
										<td>THDC Work</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ii)</td>
										<td>Funds In transit a/c</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iii)</td>
										<td>Risk & Cost</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(iv)</td>
										<td>Amount Recoverable Civil</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(v)</td>
										<td>Advances between divisions</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vi)</td>
										<td>HO others(Miscelinious Heads)</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(vii)</td>
										<td>Loss recoverable from HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(viii)</td>
										<td>Excess Centage/Intrest paid to HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(ix)</td>
										<td>Earnest money/security</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(x)</td>
										<td>Sansad Nidhi</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xi)</td>
										<td>Amount Under Reconcillation</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xii)</td>
										<td>Office/Telephone/Other Rent,Electric Security</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xiii)</td>
										<td>Advance Rent</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xiv)</td>
										<td>Advance centage deducted by HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xv)</td>
										<td>Contigency Withheild by HO</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xvi)</td>
										<td>Adv. GST deducted by client Deptt</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xvii)</td>
										<td>Access GST </td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xviii)</td>
										<td>VAT Penality</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
									<tr>
										<td>(xviv)</td>
										<td>Adv.GST (TDS) deducted by client department</td>
										<td>
											<input type="text" class="form-control" id="" name="" placeholder="Amount (In Rupees)">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
								</div>
						</div>
					</div>
				</div>
			</div>
			
		</form>	
			
		


<script type="text/javascript" src="js/multistepform.js">

		
<?php
page_footer_start();
?>


    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

   
	<!--  Charts Plugin -->
	<script src="js/chartist.min.js"></script>

<?php		
page_footer_end();
?>
