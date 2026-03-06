
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function save_barcode(){
	var id = document.getElementById('barcodeid').value;
	var count = document.getElementById('part_desc'+id+'_quantity').value;
	var product_id = document.getElementById('part_desc'+id+'_sno').value;
	var trans_date = document.getElementById('sale_date').value;
	document.getElementById('trans_date').value = trans_date;
	var finaldata = '';
	var comma=0;
	var elem = 'part_desc'+id;
	var elemid = 'barcode_'+id;
	for(var i=1; i<=count; i++){
		var value = document.getElementById(elemid+'_'+i).value
		if(value==''){
			alert("Barcode No."+i+" is blank");
			return false;
			value = 0;
			
		}
		else{
			for(var x=1; x<=count; x++){
				if(x!=i){
					var value_chk = document.getElementById(elemid+'_'+x).value
					if(value == value_chk){
						alert("Barcode No."+i+" is same as Barcode No."+x);
						return false;
					}
				}
			}
		}
		if(comma==0){
			finaldata += elem+'_'+i+'='+value;
			comma=1;
		}
		else {
			finaldata += '&'+elem+'_'+i+'='+value;
		}
	}
	var epin = document.getElementById('epin').value;
	var inv = document.getElementById('inv').value;
	finaldata += '&epin='+epin+'&elem='+elem+'&trans_date='+trans_date+'&in_out=0&inv='+inv+'&product_id='+product_id;
	var form = $('#barcodeform');
	document.getElementById('loader').style.display = 'block';
	$.ajax({
	  type: "GET",
	  url: form.attr( 'action' ),
	  data: finaldata,
	  cache: false,
	  //success: result
	  complete: function(response){
		  document.getElementById('loader').style.display = 'none';
		  if(response.responseText==''){
			  $("#element_to_pop_up").bPopup().close();
		  }
		  else{
			  alert(response.responseText);
		  }
	  }
	});
}

function get_barcode_data(epin,value){
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			var v = xmlhttp.responseText;
			document.getElementById('barcode_data').value = v;
			//alert(v);
			var marray = v.split('#');
			var len = marray.length;
			if(len==3) {
			}
		}
	}
	xmlhttp.open("GET","scripts/save_barcode.php?id=get&epin="+epin+"&elem=part_desc"+value,true);
	xmlhttp.send();	
}


function record_barcode(value){
	var ebarcode = document.getElementById('part_desc'+value+'_ebarcode').value;
	var count = document.getElementById('part_desc'+value+'_quantity').value;
	var type = document.getElementById('part_desc'+value+'_type').value;
	var epin = document.getElementById('epin').value;
	var barcode= document.getElementById('part_desc'+value+'_usertext').value;
	var barcodedata = '';
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	//alert('hello');
	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			var v = xmlhttp.responseText;
			barcodedata = v;
			var abc = 0;
			abc = parseFloat(abc);
			document.getElementById('barcode_data').value = v;
			var marray = v.split('~');
			if(ebarcode=='on' || ebarcode=='true'){
				for(var i=1; i<=count; i++){
					var test = marray[abc];
					if(!test){
						test = '';
					}
					var finaldata = test.split('#');
					if(!finaldata[1]){
						finaldata[1]='';
					}
					if(i==1 && finaldata[1]==''){
						finaldata[1]=barcode;
					}
					inputPOP += i+'. <input type="text" name="barcode_'+value+'_'+i+'" id="barcode_'+value+'_'+i+'" value="'+finaldata[1]+'"><br />';
					abc++;
				}
				document.getElementById('insertHere').innerHTML = '';
				$("div#insertHere").append(inputPOP);

				$('#element_to_pop_up').bPopup({
					modalClose: false,
					closeClass: 'saveForm',
					opacity: 0.6,
					positionStyle: 'fixed', //'fixed' or 'absolute'
					modalColor: 'greenYellow',
					onClose: function() {
						document.getElementById("part_desc"+value+"_unitprice").focus();
					}
				});
				$("#element_to_pop_up #unit").focus();
				
				//$('#element_to_pop_up').bPopup();
				document.getElementById('barcodeid').value = value;
				document.getElementById('epin').value = epin;
				document.getElementById("barcode_"+value+"_"+1).focus()
			}
		}
	}
	xmlhttp.open("GET","scripts/save_barcode.php?id=get&epin="+epin+"&elem=part_desc"+value,true);
	xmlhttp.send();	
	//get_barcode_data(epin,value);
	// Do something with content:
	var inputPOP = '';
	calculate_total(value);
}

function calculate_sqmt(value) {
	var i = value;
	var length= document.getElementById('part_desc'+i+'_length').value;
	if(length==''){
		length=1;
	}
	else{
		length = parseFloat(length);
		if(!length){
			length=1;
		}
	}
	var width= document.getElementById('part_desc'+i+'_width').value;
	if(width==''){
		width=1;
	}
	else{
		width = parseFloat(width);
		if(!width){
			width=1;
		}
	}
	var height= document.getElementById('part_desc'+i+'_height').value;
	if(height==''){
		height=1;
	}
	else{
		height = parseFloat(height);
		if(!height){
			height=1;
		}
	}
	var sqmt=0;
	var price = parseFloat(document.getElementById('part_desc'+i+'_unitprice').value);
	if(!price){
		price = 0;
	}
	var result_unit = document.getElementById('part_desc'+i+'_result_unit').value;
	var unit = $('.part_desc'+i+'_unit').html();
	sqmt = height*width*length;
	if(result_unit=='inch'){
		unit = 'Inch';
	}
	else if(result_unit=='sqft'){
		sqmt = sqmt/12;
		unit = 'Sq.Ft.';
	}
	else if(result_unit=='cuft'){
		sqmt = sqmt/144; 
		unit = 'Cu.Ft.';
	}
	sqmt = (Math.round(sqmt*100000))/100000;
	length = (Math.round(length*100000))/100000;
	width = (Math.round(width*100000))/100000;
	height = (Math.round(height*100000))/100000;
	$('#part_desc'+i+'_length').val(length);
	$('#part_desc'+i+'_width').val(width);
	$('#part_desc'+i+'_height').val(height);
	$('#part_desc'+i+'_result').val(sqmt);
	$('#part_desc'+i+'_unitprice').val(price);
	$('.part_desc'+i+'_unit').html(unit);
	calculate_total(value);
}


function calculate_total(value) {
	var id = document.getElementById('id').value;
	var tot_qty=0;
	var tot_cgst=0;
	var tot_sgst=0;
	var tot_igst=0;
	var tot_disc=0;
	var tot_taxable_amount = 0;
	var taxable_amount = 0;
	var total_amount=0;
	var total_amount1=0;
	var grand_total=0;
	var overall_total=0;
	for(i=1;i<=id;i++){
		var up = document.getElementById('part_desc'+i+'_unitprice').value;
		var disc = document.getElementById('part_desc'+i+'_discount').value;
		up = parseFloat(up);
		if(!up){
			up = 0;
		}
		if(disc.search('%')==-1){
			disc = parseFloat(disc);
			var disc_symbol=0;
		}
		else{
			disc = disc.replace('%','');
			disc = parseFloat(disc);
			var disc_symbol=1;
		}
		if(!disc){
			disc=0;
		}
		if(disc_symbol==1){
			disc = (up*disc/100);
			var price = up - disc;
		}
		else{
			var price = up-disc;
		}
		taxable_amount = price;
		var vat = $('.part_desc'+i+'_cgst_rate_hidden').val();
		vat = vat.replace("%","");
		var sat = $('.part_desc'+i+'_sgst_rate_hidden').val();
		sat = sat.replace("%","");
		vat = parseFloat(vat);
		if(!vat){
			vat = 0;
		}
		sat = parseFloat(sat);
		if(!sat){
			sat = 0;
		}
		vat = price*vat/100;
		sat = price*sat/100;
		price = price + vat + sat;
		//price = Math.round(price*100)/100;
	
		var qty = document.getElementById('part_desc'+i+'_quantity').value;
		qty = parseFloat(qty);
		if(!qty){
			qty = 0;
		}
		
		var sqmt = $('#part_desc'+i+'_result').val();
		sqmt = parseFloat(sqmt);
		if(!sqmt){
			sqmt = 1;
		}
		qty = qty * sqmt;

		var total = qty * price;
		vat = vat*qty;
		sat = sat*qty;
		disc = disc*qty;

		vat = Math.round(vat*100)/100;
		sat = Math.round(sat*100)/100;
		price = Math.round(price*100)/100;
		total = Math.round(total*100)/100;

		tot_qty+=qty;
		tot_disc+=disc;
		total_amount+=total;

		var state = $("#state").val();
		state = state.trim();
		if(state != 'Uttar Pradesh'){
			var gst = vat+sat;
			tot_igst += vat+sat;
		}
		else{
			tot_cgst+=vat;
			tot_sgst+=sat;
			var gst = vat+'<br/>'+sat;
		}

		
		$('.part_desc'+i+'_cgst_value').html(gst);
		$('.part_desc'+i+'_cgst_value_hidden').val(vat);
		$('.part_desc'+i+'_cgst_hidden').val(vat);
		//$('.part_desc'+i+'_sgst_value').html(sat);
		$('.part_desc'+i+'_sgst_value_hidden').val(sat);
		$('.part_desc'+i+'_sgst_hidden').val(sat);
		$('.part_desc'+i+'_taxable_price').html(taxable_amount);
		
		
		taxable_amount = taxable_amount*qty;
		tot_taxable_amount += taxable_amount;
		taxable_amount = Math.round(taxable_amount*100)/100
		$('.part_desc'+i+'_taxable').html(taxable_amount);
		$('.part_desc'+i+'_taxable_hidden').val(taxable_amount);
		$('#part_desc'+i+'_eprice').val(price);
		$('.part_desc'+i+'_total').html(total);
		$('.part_desc'+i+'_total_hidden').val(total);
	}
	tot_disc = Math.round(tot_disc*100)/100;
	tot_taxable_amount = Math.round(tot_taxable_amount*100)/100;
	tot_cgst = Math.round(tot_cgst*100)/100;
	tot_sgst = Math.round(tot_sgst*100)/100;
	total_amount = Math.round(total_amount*100)/100;
	$('#tot_qty').html(tot_qty);
	$('#tot_disc').html(tot_disc);
	$('#tot_taxable').html(tot_taxable_amount);
	/*$('#total_cgst').html(tot_vat);
	$('#total_sgst').html(tot_sat);*/
	
	var state = $("#state").val();
	state = state.trim();
	if(state != 'Uttar Pradesh'){
		var tot_gst = tot_igst;
		tot_cgst = tot_igst/2;
		tot_sgst = tot_igst/2;
	}
	else{
		var tot_gst = tot_cgst+'<br/>'+tot_sgst;
	}
	$('#total_cgst').html(tot_gst);
	$('#total_amount').html(total_amount);

	$('#tot_qty_hidden').val(tot_qty);
	$('#tot_disc_hidden').val(tot_disc);
	$('#tot_taxable_hidden').val(tot_taxable_amount);
	$('#total_cgst_hidden').val(tot_cgst);
	$('#total_sgst_hidden').val(tot_sgst);
	$('#total_amount_hidden').val(total_amount);
	
	total_amount1 = total_amount;
	
	var other = document.getElementById('other').value;
	var labor = document.getElementById('labor').value;
	var transport_charge = document.getElementById('transport_charge').value;
	var expenses = 0;
	other = parseFloat(other);
	if(!other){
		other=0;
	}
	labor = parseFloat(labor);
	if(!labor){
		labor=0;
	}
	transport_charge = parseFloat(transport_charge);
	if(!transport_charge){
		transport_charge=0;
	}
	expenses = other + labor + transport_charge;
	document.getElementById('total_expenses').value = expenses.toFixed(2);
	total_amount1 = total_amount1+expenses;
	var round_off = total_amount1;
	dummy_grand_total = total_amount1.toFixed(2);
	round_off = round_off.toFixed(0) - dummy_grand_total;
	
	if(document.getElementById('round_off_manual').checked==false){
		document.getElementById('round_off').value = round_off.toFixed(2)
		total_amount1 = total_amount1.toFixed(0);
	}
	else{
		var round_off_value = document.getElementById('round_off').value;
		round_off_value = parseFloat(round_off_value);
		if(!round_off_value){
			round_off_value=0;
		}
		total_amount1 = parseFloat(total_amount1.toFixed(2))+round_off_value;
		total_amount1 = total_amount1.toFixed(2);
	}
	
	$('#total_amount1').val(total_amount1);
	
	
	var other_disc = document.getElementById('other_discount').value;
	if(other_disc.search('%')==-1){
		other_disc = parseFloat(other_disc);
		var disc_symbol=0;
	}
	else{
		other_disc = other_disc.replace('%','');
		other_disc = parseFloat(other_disc);
		var disc_symbol=1;
	}
	if(!other_disc){
		other_disc=0;
	}
	if(disc_symbol==1){
		other_disc = (total_amount1*other_disc/100);
		grand_total = total_amount1 - other_disc;
	}
	else{
		grand_total = total_amount1-other_disc;
	}
	
	var round_off = grand_total;
	dummy_grand_total = grand_total.toFixed(2);
	round_off = round_off.toFixed(0) - dummy_grand_total;
	
	if(document.getElementById('round_off_manual').checked==false){
		document.getElementById('round_off_dn').value = round_off.toFixed(2)
		grand_total = grand_total.toFixed(0);
	}
	else{
		var round_off_value = document.getElementById('round_off_dn').value;
		round_off_value = parseFloat(round_off_value);
		if(!round_off_value){
			round_off_value=0;
		}
		grand_total = parseFloat(total_amount1.toFixed(2))+round_off_value;
		grand_total = grand_total.toFixed(2);
	}
	
	
	
	document.getElementById('grand_total').value = grand_total;
	tab_fill(1,12);
}

function reverse_calculate(value){
	var eprice = parseFloat($("#part_desc"+value+"_eprice").val());
	var eprice_length = eprice.length;
	if(!eprice){
		eprice = 0;
	}
	var disc = $("#part_desc"+value+"_discount").val();
	if(disc.search('%')==-1){
		disc = parseFloat(disc);
		var disc_symbol=0;
	}
	else{
		disc = disc.replace('%','');
		disc = parseFloat(disc);
		var disc_symbol=1;
	}
	if(!disc){
		disc=0;
	}
	var cgst = parseFloat($(".part_desc"+value+"_cgst_rate_hidden").val());
	if(!cgst){
		cgst=0;
	}
	var sgst = parseFloat($(".part_desc"+value+"_sgst_rate_hidden").val());
	if(!sgst){
		sgst=0;
	}
	var igst = parseFloat($(".part_desc"+value+"_igst").html());
	if(!igst){
		igst=0;
	}
	var tot_tax = ((sgst+cgst+igst)/100)+1;
	var unit_price = eprice/tot_tax;
	//console.log(unit_price);
	
	if(disc_symbol==1){
		disc = disc/100;
		var price = unit_price/(1-disc);
	}
	else{
		var price = unit_price+disc;
	}
	
	unit_price = Math.round(price*100)/100;
	
	$("#part_desc"+value+"_unitprice").val(unit_price);
	//console.log(unit_price);
	calculate_total(value);
}
	
function tab_fill(id,tab){
	var current = document.getElementById('current').value;
	id = parseFloat(document.getElementById('id').value)+1;
	console.log('part_desc'+current+'_quantity');
	var qty = document.getElementById('part_desc'+current+'_quantity').value;
	tab = (id*11)+30;
	var inputHTML = '<tr><th>'+id+'</th><td><input name="part_desc'+id+'_product" type="text" value="" class="fieldtextmedium" tabindex="'+(tab++)+'" id="part_desc" onBlur="tab_fill('+id+',12); chk_inventory(this.value);" onFocus="getCurrent('+id+')" /><br /><a class="part_desc'+id+'_avail_qty"></a></td><td class="part_desc'+id+'_hsn_code"></td><td><input name="part_desc'+id+'_description" type="text" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_description" /></td><td><input name="part_desc'+id+'_length" value="1" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_length" onblur="calculate_sqmt('+id+')" type="text"></td><td><input name="part_desc'+id+'_width" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_width" onblur="calculate_sqmt('+id+')" type="text"></td><td><input name="part_desc'+id+'_height" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_height" onblur="calculate_sqmt('+id+')" type="text"></td><td><select name="part_desc'+id+'_result_unit" id="part_desc'+id+'_result_unit" class="fieldtextsmall" tabindex="'+(tab++)+'" onchange="calculate_sqmt('+id+');"><option value="sqft">SQ. FT.</option><option value="cuft">CU. FT.</option><option value="inch">INCH</option></select></td><td><input name="part_desc'+id+'_result" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_result" onblur="calculate_total('+id+')" type="text"></td><td class="part_desc'+id+'_unit"></td><td><input name="part_desc'+id+'_quantity" type="text" value=""  class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_quantity" onBlur="tab_fill('+id+',12); record_barcode('+id+') && calculate_total('+id+');"/></td><td><input name="part_desc'+id+'_unitprice" type="text" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_unitprice" onBlur="calculate_total('+id+')"/></td><td><input name="part_desc'+id+'_discount" type="text" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_discount" onBlur="calculate_total('+id+')" /></td><td class="part_desc'+id+'_taxable_price"></td><td class="part_desc'+id+'_taxable"></td><td class="part_desc'+id+'_cgst"></td><td class="part_desc'+id+'_cgst_value"></td><td><input name="part_desc'+id+'_eprice" type="text" value="" class="fieldtextsmall" id="part_desc'+id+'_eprice" tabindex="'+(tab++)+'" onblur="reverse_calculate('+id+')"/></td><td class="part_desc'+id+'_total"></td><input type="hidden" id="part_desc'+id+'_sno" name="part_desc'+id+'_sno" value=""><input name="part_desc'+id+'_usertext" type="hidden" value="" id="part_desc'+id+'_usertext"/><input type="hidden" id="part_desc'+id+'_qty" name="part_desc'+id+'_qty" value=""><input type="hidden" id="part_desc'+id+'_type" name="part_desc'+id+'_type" value=""><input type="hidden" id="part_desc'+id+'_ebarcode" name="part_desc'+id+'_ebarcode" value=""><input type="hidden" id="part_desc'+id+'_inv_type" name="part_desc'+id+'_inv_type" value=""><input type="hidden" name="part_desc'+id+'_unit" class="part_desc'+id+'_unit" value=""><input type="hidden" name="part_desc'+id+'_taxable_hidden" class="part_desc'+id+'_taxable_hidden" value=""><input type="hidden" name="part_desc'+id+'_cgst_rate_hidden" class="part_desc'+id+'_cgst_rate_hidden" value=""><input type="hidden" name="part_desc'+id+'_cgst_value_hidden" class="part_desc'+id+'_cgst_value_hidden" value=""><input type="hidden" name="part_desc'+id+'_sgst_rate_hidden" class="part_desc'+id+'_sgst_rate_hidden" value=""><input type="hidden" name="part_desc'+id+'_sgst_value_hidden" class="part_desc'+id+'_sgst_value_hidden" value=""><input type="hidden" name="part_desc'+id+'_total_hidden" class="part_desc'+id+'_total_hidden" value=""></tr>';
	if((id-current)==1 && qty!='' && qty!=0){
		document.getElementById('other').tabIndex = tab+10;
		document.getElementById('labor').tabIndex = tab+11;
		document.getElementById('transport_charge').tabIndex = tab+12;
		document.getElementById('round_off_manual').tabIndex = tab+13;
		document.getElementById('round_off').tabIndex = tab+14;
		document.getElementById('other_discount').tabIndex = tab+15;
		document.getElementById('round_off_manual_dn').tabIndex = tab+16;
		document.getElementById('round_off_dn').tabIndex = tab+17;
		document.getElementById('remark').tabIndex = tab+18;
		document.getElementById('saveForm').tabIndex = tab+19;
        $(inputHTML).insertBefore("tr#finalValues");
		document.getElementById('id').value = id;
	}
}

function escapeHtml(text){
	var abc = text
		.replace('&', "&")
		.replace('<', "<")
		.replace('>', ">")
		.replace("'", "'")
		.replace('"', '"');
	return abc;
}

function chk_inventory(val){
	val = encodeURI(escapeHtml(val));
	var cur_date = $("#purchase_date").val();
	var store = $("#storeid").val();
	if(val!=''){
		$.ajax({
			url: "scripts/ajax_ply.php?id=chk_inventory&term="+val+"&cur_date="+cur_date+"&store="+store+"&type="+form_type,
			dataType:"json"
		})
		.done(function( data ) {
			data = data[0];
			var id = document.getElementById('current').value;
			if(data.id!=0){
				
				var unit='';
				var first_unit='';
				
				$.each( data.unit_conv, function( index, value ) {
					$.each(value, function(k,v){
						var conv_value = data.unit_conv_value[0][k];
						if(!conv_value){
							conv_value=1;
						}
						k = k.replace("key_", "");
						if(first_unit==''){
							first_unit=conv_value;
						}
						unit=unit+'<option value="'+k+'^'+conv_value+'" onclick="'+conv_value+'">'+v+'</option>';

					});
				});
				
				var txt = '<table width="100%"><tr><td>Unit :</td><td><input type="text" name="unit" id="unit" value="'+data.unit+'"></td></tr><tr><td>MRP :</td><td><input type="text" name="mrp" id="mrp" value="'+data.mrp+'"></td></tr><tr><td>SRP :</td><td><input type="text" name="srp" id="srp" value="'+data.srp+'"></td></tr><tr><td>MSRP :</td><td><input type="text" name="msrp" id="msrp" value="'+data.msrp+'"></td></tr><tr><td>Warranty :</td><td><input type="text" name="stock_warranty" id="stock_warranty" value="'+data.warranty+'"></td></tr><tr><td>Warning Qty :</td><td><input type="text" name="stock_warning" id="stock_warning" value="'+data.warning+'"></td></tr><tr><td>Barcode :</td><td><input type="text" name="stock_barcode" id="stock_barcode" value="'+data.barcode+'"></td></tr><tr><td>HSN Code :</td><td><input type="text" name="hsn_code" id="hsn_code" value="'+data.hsn_code+'"></td></tr><tr><td>CGST :</td><td><input type="text" name="vat" id="vat" value="'+data.vat+'"></td></tr><tr><td>SGST :</td><td><input type="text" name="excise" id="excise" value="'+data.excise+'"></td></tr><tr><td>Enable Barcode</td><td><input type="checkbox" name="enable_barcode" id="enable_barcode"';
				if(data.enable_barcode=='on' || data.enable_barcode=='true'){
					txt = txt + ' checked="checked" ';
				}
				txt = txt + '/></td></tr><tr><td>INVOICE TYPE</td><td><select name="inv_type" id="inv_type"><option value="tax"';
				if(data.inv_type=='tax'){
					txt=txt+' selected="selected" ';
				}
				txt=txt + '>TAXABLE</option><option value="exempt"';
				if(data.inv_type=='exempt'){
					txt=txt+' selected="selected" ';
				}
				txt=txt +'>EXEMPT</option></select></td></tr><tr><td>Group:</td><td><select name="type" id="type"><option value=""></option>';
				$.each( data.type_options, function( index, value ) {
					$.each(value, function(k,v){
						txt=txt+'<option value="'+k+'" ';
						k = k.replace("key_", "");
						if(k==data.type){
							txt=txt+'selected="selected"';
						}
						txt = txt+'>'+v+'</option>';

					});
				});
				txt=txt+'</select></td></tr><tr><td>Company</td><td><select name="company" id="company"><option value=""></option>';
	
				$.each( data.company_options, function( index, value ) {
					$.each(value, function(k,v){
						txt=txt+'<option value="'+k+'" ';
						k = k.replace("key_", "");
						if(k==data.company){
							txt=txt+'selected="selected"';
						}
						txt = txt+'>'+v+'</option>';

					});
				});

				txt = txt+'</select></td></tr><tr><td colspan="2" align="center"><input type="hidden" name="prod_sno" id="prod_sno" value="'+data.id+'"><input type="hidden" name="prod_desc" id="prod_desc" value="'+val+'"><input id="saveForm" name="saveForm" class="btTxt submit" type="button" value="Save" onClick="save_stock();"><input id="cancelForm" name="cancelForm" class="btTxt submit" type="button" value="Cancel" onClick="$(\'#element_to_pop_up2\').bPopup().close();"></td></tr></table><center><img src="images/loading_transparent.gif" id="loader_stock" style="display:none;" /></center>';
				var area_unit = data.area_unit;
				if(!area_unit){
					area_unit = '';
				}
				area_unit = area_unit.toLowerCase();
				var state = $("#state").val();
				state = state.trim();
				if(state != 'Uttar Pradesh'){
					var gst = 'IGST @ '+(data.vat*2)+'%';
				}
				else{
					var gst = 'SGST @ '+data.vat+'%<br />CGST @ '+data.vat+'%';
				}
				
				$("[name='part_desc"+id+"_product']").val(data.label);
				$('#part_desc'+id+'_sno').val(data.id);
				$('#part_desc'+id+'_unitprice').val(data.purchase_price);
				$('#part_desc'+id+'_result_unit').val(area_unit);
				$('#part_desc'+id+'_length').val(data.length);
				$('#part_desc'+id+'_width').val(data.width);
				$('#part_desc'+id+'_height').val(data.height);
				$('.part_desc'+id+'_cgst').html(gst);
				$('.part_desc'+id+'_cgst_rate_hidden').val(data.excise+'%');
				$('.part_desc'+id+'_sgst_rate_hidden').val(data.vat+'%');
				$('.part_desc'+id+'_unit').html(unit);
				$('.part_desc'+id+'_hsn_code').html(data.hsn_code);
				$('.part_desc'+id+'_avail_qty').html('Qty : '+data.avail_qty);
				$('#part_desc'+id+'_description').val(data.product_description);
				$('#part_desc'+id+'_discount').val(data.discount);
				$('#part_desc'+id+'_ebarcode').val(data.enable_barcode);
				$('#part_desc'+id+'_inv_type').val(data.inv_type);
				//alert('part_desc'+id+'_unit');
				if(data.data_type=="yes"){
					$('#part_desc'+id+'_usertext').val(data.barcode1);
				}
			}
			else{
				$.ajax({
					url: "scripts/ajax_ply.php?id=misc&term=1",
					dataType:"json"
				})
				.done(function( data ) {
					data = data[0];
					var unit='';
					var type='';
					var company='';

					$.each(data.unit, function( k, v ) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							unit=unit+'<option value="'+k+'">'+v+'</option>';

						//});
					});
					
					$.each(data.type, function( k, v ) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							type=type+'<option value="'+k+'">'+v+'</option>';

						//});
					});
					var txt = '<table width="100%"><tr><td>Unit :</td><td><input type="text" name="unit" id="unit" value=""></td></tr><tr><td>MRP :</td><td><input type="text" name="mrp" id="mrp" value=""></td></tr><tr><td>SRP :</td><td><input type="text" name="srp" id="srp" value=""></td></tr><tr><td>MSRP :</td><td><input type="text" name="msrp" id="msrp" value=""></td></tr><tr><td>Warranty :</td><td><input type="text" name="stock_warranty" id="stock_warranty" value=""></td></tr><tr><td>Warning Qty :</td><td><input type="text" name="stock_warning" id="stock_warning" value=""></td></tr><tr><td>Barcode :</td><td><input type="text" name="stock_barcode" id="stock_barcode" value=""></td></tr><tr><td>HSN Code :</td><td><input type="text" name="hsn_code" id="hsn_code" value=""></td></tr><tr><td>CGST :</td><td><input type="text" name="vat" id="vat" value=""></td></tr><tr><td>SGST :</td><td><input type="text" name="excise" id="excise" value=""></td></tr><tr><td colspan="2" align="center"><input type="hidden" name="prod_sno" id="prod_sno" value=""><tr><td>Enable Barcode</td><td><input type="checkbox" name="enable_barcode" id="enable_barcode" ></td></tr><tr><td>INVOICE TYPE</td><td><select name="inv_type" id="inv_type"><option value="tax">TAXABLE</option><option value="exempt">EXEMPT</option></select></td></tr></tr><tr><td>Group:</td><td><select name="type" id="type"><option value=""></option>'+type+'</select></td></tr><tr><td>Company</td><td><select name="company" id="company"><option value=""></option>'+company+'</select></td></tr><tr><td colspan="2"><input type="hidden" name="prod_desc" id="prod_desc" value="'+val+'"><input id="saveForm" name="saveForm" class="btTxt submit" type="button" value="Save" onClick="save_stock();"><input id="cancelForm" name="cancelForm" class="btTxt submit" type="button" value="Cancel" onClick="$(\'#element_to_pop_up2\').bPopup().close();"></td></tr></table><center><img src="images/loading_transparent.gif" id="loader_stock" style="display:none;" /></center>';
					$("#stock_details").html(txt);
					$('#element_to_pop_up2').bPopup({
						modalClose: false,
						closeClass: 'saveForm',
						opacity: 0.6,
						positionStyle: 'fixed', //'fixed' or 'absolute'
						modalColor: 'greenYellow',
						onClose: function() {
							$("#part_desc"+id+"_description").focus();
						}
					});
					$("#element_to_pop_up2 #unit").focus();
				});
			}
		});
	}
}

function save_stock(){
	var id = document.getElementById('prod_sno').value;
	document.getElementById('loader_stock').style.display = 'block';
	var mrp = $('#mrp').val();
	var prod_desc = encodeURI($('#prod_desc').val());
	var srp = $('#srp').val();
	var msrp = $('#msrp').val();
	var stock_warranty = $('#stock_warranty').val();
	var stock_barcode = encodeURI($('#stock_barcode').val())
	var hsn_code = encodeURI($('#hsn_code').val())
	var enable_barcode=$('#enable_barcode').is(':checked');
	var inv_type = $('#inv_type').val();
	var type = $('#type').val();
	var company = $('#company').val();
	var unit = $('#unit').val();
	var vat = $('#vat').val();
	var excise = $('#excise').val();
	var id = document.getElementById('current').value;
	//alert(stock_barcode);
	var stock_warning = $('#stock_warning').val();
	var prod_sno = $('#prod_sno').val();
	finaldata = 'mrp='+mrp+'&srp='+srp+'&msrp='+msrp+'&stock_warranty='+stock_warranty+'&stock_barcode='+encodeURI(stock_barcode)+'&hsn_code='+encodeURI(hsn_code)+'&prod_sno='+prod_sno+'&prod_desc='+prod_desc+'&stock_warning='+stock_warning+'&enable_barcode='+enable_barcode+'&inv_type='+inv_type+'&type='+type+'&company='+company+'&vat='+vat+'&excise='+excise+'&unit='+unit;
	$.ajax({
	  type: "GET",
	  url: "scripts/ajax_ply.php?id=save_stock&term=1",
	  data: finaldata,
	  cache: false,
	  //success: result
	  complete: function(response){
		  document.getElementById('loader_stock').style.display = 'none';
		  if(response.responseText==''){
			  $("#element_to_pop_up2").bPopup().close();
			  $("#part_desc"+id+"_ebarcode").val(enable_barcode);
			  $(".part_desc"+id+"_hsn_code").html(hsn_code);
			  $(".part_desc"+id+"_unit").html(unit);
			  $(".part_desc"+id+"_cgst").html(vat);
			  $(".part_desc"+id+"_sgst").html(excise);
			  $("#part_desc"+id+"_description").focus();
			  
		  }
		  else{
			  //alert(response.responseText);
		  }
	  }
	});
}


function getCurrent(id){
	document.getElementById('current').value = id;
}
function printinvoice() {
	window.open("printing.php");
}