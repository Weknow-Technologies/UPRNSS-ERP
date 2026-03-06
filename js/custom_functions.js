
/*function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}*/

function save_barcode(){
	var id = document.getElementById('barcodeid').value;
	var count = document.getElementById('part_desc'+id+'_quantity').value;
	var product_id = document.getElementById('part_desc'+id+'_sno').value;
	if(form_type=='purchase'){
		var trans_date = document.getElementById('purchase_date').value;
		var in_out = 0;
	}
	else if(form_type=='purchase_revert'){
		var trans_date = document.getElementById('purchase_date').value;
		var in_out = 1;
	}
	else{
		var trans_date = document.getElementById('sale_date').value;
		var in_out = 1;
	}
	document.getElementById('trans_date').value = trans_date;
	var finaldata = '';
	var comma=0;
	var elem = 'part_desc'+id;
	var elemid = 'barcode_'+id;
	for(var i=1; i<=count; i++){
		var value = document.getElementById(elemid+'_'+i).value
		if(value==''){
			alert("Barcode No."+i+" is blank");
			value = 0;
			return false;
			
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
	finaldata += '&epin='+epin+'&elem='+elem+'&trans_date='+trans_date+'&in_out='+in_out+'&inv='+inv+'&product_id='+product_id;
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
			  $("#part_desc"+id+"_unit").focus();
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

function calculate_total(value) {
	var id = document.getElementById('id').value;
	var tot_qty=0;
	var tot_cgst=0;
	var tot_sgst=0;
	var tot_igst=0;
	var tot_disc=0;
	var tot_scheme=0;
	var tot_taxable_amount = 0;
	
	var return_qty=0;
	var return_cgst=0;
	var return_sgst=0;
	var return_igst=0;
	var return_disc=0;
	var return_scheme=0;
	var return_taxable_amount = 0;
	var return_amount=0;
	
	var taxable_amount = 0;
	var total_amount=0;
	var total_amount1=0;
	var grand_total=0;
	var overall_total=0;
	
	if(form_type=='sale_pos'){
		var state = default_state;
	}
	else{
		var state = $("#state_hidden").val();
	}
	
	for(i=1;i<=id;i++){
		var qty = document.getElementById('part_desc'+i+'_quantity').value;
		var return_check = $("#part_desc"+i+"_return_flag").val();
		if(form_type=='sale_revert'){
			return_check = 0;
		}
		
		qty = parseFloat(qty);
		if(!qty){
			qty = 0;
		}
		if(qty!=0){
			var up = document.getElementById('part_desc'+i+'_unitprice').value;
			var disc = document.getElementById('part_desc'+i+'_discount').value;
			up = parseFloat(up);
			if(!up){
				up = 0;
			}
			var price = up;
			if(form_type!='sale_pos'){
				var scheme = document.getElementById('part_desc'+i+'_scheme').value;
				if(scheme.search('%')==-1){
					scheme = parseFloat(scheme);
					//var tmp_qty = scheme;
					var scheme_symbol=0;
				}
				else{
					scheme = scheme.replace('%','');
					scheme = parseFloat(scheme);
					var scheme_symbol=1;
				}
				if(!scheme){
					scheme=0;
				}
				if(scheme_symbol==1){
					scheme = (up*scheme/100);
					price = up - scheme;
				}
				else{
					if(return_check=='0'){
						tot_qty += scheme;
					}
					else{
						return_qty += scheme;							
					}
					
					price = up;
				}
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
				disc = (price*disc/100);
				var price = price - disc;
				var disc_val = Math.round(disc*100)/100;
			}
			else{
				var price = price-disc;
				var disc_val = Math.round((disc*100)/price*100)/100;
				disc_val = disc_val+'%';
			}
			//alert(price+'@@'+disc);
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

			var total = qty * price;
			vat = vat*qty;
			sat = sat*qty;
			disc = disc*qty;
			scheme = scheme*qty;

			vat = Math.round(vat*100)/100;
			sat = Math.round(sat*100)/100;
			price = Math.round(price*100)/100;
			total = Math.round(total*100)/100;

			if(return_check=='0'){
				tot_qty += qty;
				tot_disc+=disc;
				//tot_scheme+=scheme;
				total_amount+=total;
			}
			else{
				return_qty += qty;
				return_disc+=disc;
				//tot_scheme+=scheme;
				return_amount+=total;
			}

			var state = $("#state_hidden").val();
			if(!state){
				state = default_state;
			}
			if(state != default_state){
				var gst = vat+sat;
				if(return_check=='0'){
					tot_igst += vat+sat;
				}
				else{
					return_igst += vat+sat;
				}
			}
			else{
				if(return_check=='0'){
					tot_cgst+=vat;
					tot_sgst+=sat;
				}
				else{
					return_cgst += vat;
					return_sgst += sat;
				}
				var gst = vat+'<br/>'+sat;
			}
			$('.part_desc'+i+'_cgst_value').html(gst);
			$('.part_desc'+i+'_cgst_value_hidden').val(vat);
			$('.part_desc'+i+'_cgst_hidden').val(vat);
			//$('.part_desc'+i+'_sgst_value').html(sat);
			$('.part_desc'+i+'_sgst_value_hidden').val(sat);
			$('.part_desc'+i+'_sgst_hidden').val(sat);
			$('.part_desc'+i+'_taxable_price').html(Math.round(taxable_amount*100)/100);
			$('.part_desc'+i+'_discount_value').html(disc_val);
			
			taxable_amount = taxable_amount*qty;
			if(return_check=='0'){
				tot_taxable_amount += taxable_amount;
			}
			else{
				return_taxable_amount += taxable_amount;
			}
			taxable_amount = Math.round(taxable_amount*100)/100;
			$('.part_desc'+i+'_taxable').html(taxable_amount);
			$('.part_desc'+i+'_taxable_hidden').val(taxable_amount);
			$('#part_desc'+i+'_eprice').val(price);
			$('.part_desc'+i+'_total').html(total);
			$('.part_desc'+i+'_total_hidden').val(total);
			if(scheme_symbol==0 && scheme!=''){
				var temp_price = total/(parseFloat(qty)+parseFloat($("#part_desc"+i+"_scheme").val()));
				temp_price = Math.round(temp_price*100)/100;
				$(".part_desc"+i+"_ep").html('<br/><small>PP: '+temp_price+'</small>');
			}
			else{
				$(".part_desc"+i+"_ep").html('');
			}
		}
	}
	tot_disc = Math.round(tot_disc*100)/100;
	tot_scheme = Math.round(tot_scheme*100)/100;
	tot_taxable_amount = Math.round(tot_taxable_amount*100)/100;
	tot_cgst = Math.round(tot_cgst*100)/100;
	
	tot_sgst = Math.round(tot_sgst*100)/100;
	total_amount = Math.round(total_amount*100)/100;
	$('.tot_qty').html(tot_qty);
	//$('#tot_scheme').html(tot_scheme);
	$('#tot_disc').html(tot_disc);
	$('#tot_taxable').html(tot_taxable_amount);
	/*$('#total_cgst').html(tot_vat);
	$('#total_sgst').html(tot_sat);*/
	if(state != default_state){
		if(return_qty=='0'){
			var tot_gst = tot_igst;
			tot_cgst = tot_igst/2;
			tot_sgst = tot_igst/2;
		}
		else{
			var return_gst = return_igst;
			return_cgst = return_igst/2;
			return_sgst = return_igst/2;
		}
	}
	else{
		if(return_qty=='0'){
			var tot_gst = tot_cgst+'<br/>'+tot_sgst;
		}
		else{
			var return_gst = return_cgst+'<br/>'+return_sgst;
		}
	}
	$('#total_cgst').html(tot_gst);
	$('#total_amount').html(total_amount);
	$('#tot_qty_hidden').val(tot_qty);
	$('#tot_scheme_hidden').val(tot_scheme);
	$('#tot_disc_hidden').val(tot_disc);
	$('#tot_taxable_hidden').val(tot_taxable_amount);
	$('#total_cgst_hidden').val(tot_cgst);
	$('#total_sgst_hidden').val(tot_sgst);
	$('#total_amount_hidden').val(total_amount);
	if(return_qty!=0){
		var return_row = '<tr id="return_row"><th colspan="3" style="text-align:right;">Total </th><th id="return_qty">'+return_qty+'</th><th colspan="2"></th><th id="return_scheme"></th><th id="return_disc">'+return_disc+'</th><th>&nbsp;</th><th id="return_taxable">'+return_taxable_amount+'</th><th style="text-align:right;">Total Tax</th><th id="return_cgst">'+return_cgst+'</th><th>Total</th><th id="return_amount">'+return_amount+'</th><input type="hidden" name="return_qty_hidden" value="'+return_qty+'" id="return_qty_hidden"><input type="hidden" name="return_disc_hidden" value="'+return_disc+'" id="return_disc_hidden"><input type="hidden" name="return_scheme_hidden" value="" id="return_scheme_hidden"><input type="hidden" name="return_taxable_hidden" value="'+return_taxable_amount+'" id="return_taxable_hidden"><input type="hidden" name="return_cgst_hidden" value="'+return_cgst+'" id="return_cgst_hidden"><input type="hidden" name="return_sgst_hidden" value="'+return_sgst+'" id="return_sgst_hidden"><input type="hidden" name="return_amount_hidden" value="'+return_amount+'" id="return_amount_hidden"></tr>';
		//if($("#return_row").show()){
			$("#return_row").show()
			$('#return_qty').html(return_qty);
			$('#return_disc').html(return_disc);
			$('#return_taxable').html(return_taxable_amount);
			$('#return_cgst').html(return_gst);
			$('.return_amount').html(return_amount);

			$('#return_qty_hidden').val(return_qty);
			$('#return_disc_hidden').val(return_disc);
			$('#return_taxable_hidden').val(return_taxable_amount);
			$('#return_cgst_hidden').val(return_cgst);
			$('#return_sgst_hidden').val(return_sgst);
			$('#return_amount_hidden').val(return_amount);
		/*}
		else{
			$("#total_row").after(return_row);	
		}*/		
	}
	else{
		$("#return_row").hide()
	}
	
	total_amount1 = total_amount;
	
	if(form_type!='sale_revert'){
		
		var labor = document.getElementById('labor').value;
		var transport_charge = document.getElementById('transport_charge').value;
		var expenses = 0;
		labor = parseFloat(labor);
		if(!labor){
			labor=0;
		}
		transport_charge = parseFloat(transport_charge);
		if(!transport_charge){
			transport_charge=0;
		}
		expenses = labor + transport_charge;

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

		var tcs_rate = parseFloat($("#tcs_rate").val());
		if(!tcs_rate){
			tcs_rate=0;
		}
		if(tcs_rate!=0){
			var tcs_value = Math.round(grand_total*tcs_rate)/100;
			$("#tcs_value_display").html(tcs_value);
			$("#tcs_value").val(tcs_value);
			grand_total = parseFloat(grand_total)+tcs_value;
		}
		else{
			$("#tcs_value_display").html('');
			$("#tcs_value").val('');
		}

		document.getElementById('grand_total').value = grand_total;
	}
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
	
	var scheme = $("#part_desc"+value+"_scheme").val();
	if(scheme.search('%')==-1){
		scheme = parseFloat(scheme);
		var scheme_symbol=0;
	}
	else{
		scheme = scheme.replace('%','');
		scheme = parseFloat(scheme);
		var scheme_symbol=1;
	}
	if(!scheme){
		scheme=0;
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
	
	if(disc_symbol==1){
		disc = disc/100;
		var price = unit_price/(1-disc);
	}
	else{
		var price = unit_price+disc;
	}
	
	unit_price = Math.round(price*100)/100;
	
	if(scheme_symbol==1){
		scheme = scheme/100;
		var price = unit_price/(1-scheme);
	}
	else{
		//var price = unit_price+scheme;
	}
	
	unit_price = Math.round(price*100)/100;
	$("#part_desc"+value+"_unitprice").val(unit_price);
	calculate_total(value);
}
	
function tab_fill(id,tab){
	var current = document.getElementById('current').value;
	if(current==''){
		current=id;
	}
	id = parseFloat(document.getElementById('id').value)+1;
	var qty = document.getElementById('part_desc'+current+'_quantity').value;
	tab = (id*9)+30;
	if(form_type=='sale_pos'){
		var inputHTML = '<tr><th>'+id+'</th><td><input name="part_desc'+id+'_product" value="" tabindex="'+(tab++)+'" id="part_desc" class="fieldtextmedium" onblur="tab_fill('+id+','+(tab++)+'); chk_inventory(this.value); " onfocus="getCurrent('+id+')" type="text"><br><a class="part_desc'+id+'_avail_qty"></a></td><td style="display:none;"><input name="part_desc'+id+'_description" class="fieldtextmedium" value="" id="part_desc'+id+'_description" type="hidden"><br><a class="part_desc'+id+'_hsn_code"> </a></td><td><input name="part_desc'+id+'_quantity" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_quantity" onblur="tab_fill('+id+','+(tab++)+'); record_barcode('+id+') &amp;&amp; calculate_total('+id+')" type="text"><br/><a class="part_desc'+id+'_multistore"></a></td><td class="part_desc'+id+'_unit"><select name="part_desc'+id+'_unit" id="part_desc'+id+'_unit" class="fieldtextsmall" tabindex="'+(tab++)+'" onchange="update_price('+id+', this.value);"></select></td><td><input name="part_desc'+id+'_unitprice" value="" class="fieldtextsmall" readonly="readonly" id="part_desc'+id+'_unitprice" onblur="calculate_total('+id+')" type="text"></td><td style="display:none;"><input name="part_desc'+id+'_scheme" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_scheme" onblur="calculate_total('+id+')" type="text"><a class="part_desc'+id+'_ep"></a></td><td><input name="part_desc'+id+'_discount" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_discount" onblur="calculate_total('+id+')" type="text"></td><td class="part_desc'+id+'_discount_value"></td><td class="part_desc'+id+'_taxable_price"></td><td class="part_desc'+id+'_taxable"></td><td class="part_desc'+id+'_cgst"></td><td class="part_desc'+id+'_cgst_value"></td><td><input name="part_desc'+id+'_eprice" value="" class="fieldtextsmall" id="part_desc'+id+'_eprice" readonly="readonly" onblur="reverse_calculate('+id+')" type="text"></td><td class="part_desc'+id+'_total"></td><input id="part_desc'+id+'_sno" name="part_desc'+id+'_sno" value="" type="hidden"><input name="part_desc'+id+'_usertext" value="" id="part_desc'+id+'_usertext" type="hidden"><input id="part_desc'+id+'_qty" name="part_desc'+id+'_qty" value="" type="hidden"><input id="part_desc'+id+'_type" name="part_desc'+id+'_type" value="" type="hidden"><input id="part_desc'+id+'_ebarcode" name="part_desc'+id+'_ebarcode" value="" type="hidden"><input id="part_desc'+id+'_ebatchno" name="part_desc'+id+'_ebatchno" value="" type="hidden"><input id="part_desc'+id+'_batch_no" name="part_desc'+id+'_batch_no" value="" type="hidden"><input id="part_desc'+id+'_expiry" name="part_desc'+id+'_expiry" value="" type="hidden"><input id="part_desc'+id+'_mfg_date" name="part_desc'+id+'_mfg_date" value="" type="hidden"><input id="part_desc'+id+'_s_no" name="part_desc'+id+'_s_no" value="" type="hidden"><input id="part_desc'+id+'_inv_type" name="part_desc'+id+'_inv_type" value="" type="hidden"><input name="part_desc'+id+'_taxable_hidden" class="part_desc'+id+'_taxable_hidden" value="" type="hidden"><input name="part_desc'+id+'_cgst_rate_hidden" class="part_desc'+id+'_cgst_rate_hidden" value="" type="hidden"><input name="part_desc'+id+'_cgst_value_hidden" class="part_desc'+id+'_cgst_value_hidden" value="" type="hidden"><input name="part_desc'+id+'_sgst_rate_hidden" class="part_desc'+id+'_sgst_rate_hidden" value="" type="hidden"><input name="part_desc'+id+'_sgst_value_hidden" class="part_desc'+id+'_sgst_value_hidden" value="" type="hidden"><input name="part_desc'+id+'_igst_value_hidden" class="part_desc'+id+'_igst_value_hidden" value="" type="hidden"><input name="part_desc'+id+'_total_hidden" class="part_desc'+id+'_total_hidden" value="" type="hidden"><input name="part_desc'+id+'_unit_conv_value" class="part_desc'+id+'_unit_conv_value" value="" type="hidden"><input type="hidden" name="part_desc'+id+'_return_flag" id="part_desc'+id+'_return_flag" value="0"></tr>';
		if((id-current)==1 && qty!='' && qty!=0){
			document.getElementById('other_discount').tabIndex = tab+9;
			document.getElementById('mode_of_payment').tabIndex = tab+10;
			document.getElementById('labor').tabIndex = tab+11;
			document.getElementById('transport_charge').tabIndex = tab+12;
			document.getElementById('tender_amount').tabIndex = tab+13;
			document.getElementById('tender_amount_return').tabIndex = tab+14;
			document.getElementById('transport_charge').tabIndex = tab+15;
			document.getElementById('round_off').tabIndex = tab+16;
			document.getElementById('round_off_manual').tabIndex = tab+17;
			document.getElementById('remark').tabIndex = tab+18;
			$(inputHTML).insertBefore("tr#finalValues");
			document.getElementById('id').value = id;
		}
	}
	else{
		var inputHTML = '<tr><th>'+id+'</th><td><input name="part_desc'+id+'_product" type="text" value=""  tabindex="'+(tab++)+'" id="part_desc" class="fieldtextmedium" onBlur="tab_fill('+id+',12); chk_inventory(this.value);" onFocus="getCurrent('+id+')" /><br /><a class="part_desc'+id+'_avail_qty"></a></td><td><input name="part_desc'+id+'_description" type="text" class="fieldtextmedium" value="" tabindex="'+(tab++)+'" id="part_desc'+id+'_description"><br/><a class="part_desc'+id+'_hsn_code"></a></td><td><input name="part_desc'+id+'_quantity" type="text" value=""  class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_quantity" onBlur="tab_fill('+id+',12); record_barcode('+id+') && calculate_total('+id+');"/></td><td class="part_desc'+id+'_unit"><select name="part_desc'+id+'_unit" id="part_desc'+id+'_unit" class="fieldtextsmall" tabindex="'+(tab++)+'"  onChange="update_price('+id+', this.value);"></select></td><td><input name="part_desc'+id+'_unitprice" type="text" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_unitprice" onBlur="calculate_total('+id+')"/></td><td><input name="part_desc'+id+'_scheme" type="text" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_scheme" onBlur="calculate_total('+id+')"/></td><td><input name="part_desc'+id+'_discount" type="text" value="" class="fieldtextsmall" tabindex="'+(tab++)+'" id="part_desc'+id+'_discount" onBlur="calculate_total('+id+')" /></td><td class="part_desc'+id+'_discount_value"></td><td class="part_desc'+id+'_taxable_price"></td><td class="part_desc'+id+'_taxable"></td><td class="part_desc'+id+'_cgst"></td><td class="part_desc'+id+'_cgst_value"></td><td><input name="part_desc'+id+'_eprice" type="text" value="" class="fieldtextsmall" id="part_desc'+id+'_eprice" tabindex="'+(tab++)+'" onblur="reverse_calculate('+id+')"/></td><td class="part_desc'+id+'_total"></td><input type="hidden" id="part_desc'+id+'_sno" name="part_desc'+id+'_sno" value=""><input name="part_desc'+id+'_usertext" type="hidden" value="" id="part_desc'+id+'_usertext"/><input type="hidden" id="part_desc'+id+'_qty" name="part_desc'+id+'_qty" value=""><input type="hidden" id="part_desc'+id+'_type" name="part_desc'+id+'_type" value=""><input type="hidden" id="part_desc'+id+'_ebarcode" name="part_desc'+id+'_ebarcode" value=""><input type="hidden" id="part_desc'+id+'_ebatchno" name="part_desc'+id+'_ebatchno" value=""><input type="hidden" id="part_desc'+id+'_batch_no" name="part_desc'+id+'_batch_no" value=""><input type="hidden" id="part_desc'+id+'_expiry" name="part_desc'+id+'_expiry" value=""><input type="hidden" id="part_desc'+id+'_mfg_date" name="part_desc'+id+'_mfg_date" value=""><input type="hidden" id="part_desc'+id+'_s_no" name="part_desc'+id+'_s_no" value=""><input type="hidden" id="part_desc'+id+'_inv_type" name="part_desc'+id+'_inv_type" value=""><input type="hidden" name="part_desc'+id+'_taxable_hidden" class="part_desc'+id+'_taxable_hidden" value=""><input type="hidden" name="part_desc'+id+'_cgst_rate_hidden" class="part_desc'+id+'_cgst_rate_hidden" value=""><input type="hidden" name="part_desc'+id+'_cgst_value_hidden" class="part_desc'+id+'_cgst_value_hidden" value=""><input type="hidden" name="part_desc'+id+'_sgst_rate_hidden" class="part_desc'+id+'_sgst_rate_hidden" value=""><input type="hidden" name="part_desc'+id+'_sgst_value_hidden" class="part_desc'+id+'_sgst_value_hidden" value=""><input type="hidden" name="part_desc'+id+'_igst_value_hidden" class="part_desc'+id+'_igst_value_hidden" value=""><input type="hidden" name="part_desc'+id+'_total_hidden" class="part_desc'+id+'_total_hidden" value=""><input name="part_desc'+id+'_unit_conv_value" class="part_desc'+id+'_unit_conv_value" value="" type="hidden"><input type="hidden" name="part_desc'+id+'_return_flag" id="part_desc'+id+'_return_flag" value="0"></tr>';
		if((id-current)==1 && qty!='' && qty!=0){
			if(form_type=='sale_revert'){
				
			}
			else{
				document.getElementById('labor').tabIndex = tab+10;
				document.getElementById('transport_charge').tabIndex = tab+11;
				document.getElementById('round_off_manual').tabIndex = tab+12;
				document.getElementById('round_off').tabIndex = tab+13;
				document.getElementById('other_discount').tabIndex = tab+14;
				document.getElementById('round_off_manual_dn').tabIndex = tab+15;
				document.getElementById('round_off_dn').tabIndex = tab+16;
				
			}
			document.getElementById('remark').tabIndex = tab+17;
			document.getElementById('saveForm').tabIndex = tab+18;
			$(inputHTML).insertBefore("tr#finalValues");
			document.getElementById('id').value = id;
		}
	}
}    

function escapeHtml(text){
	var abc = text
		.replace('&', "&amp")
		.replace('<', "<")
		.replace('>', "&gt")
		.replace("'", "&quot")
		.replace('"', "quot");
	return abc;
}

function chk_inventory(val){
	val = encodeURI(val);
	var id = document.getElementById('current').value;
	var cur_date = $("#sale_date").val();
	var store = $("#storeid").val();
	if(!store){
		store = 1;
	}
	//console.log("RF :"+$("#part_desc"+id+"_return_flag").val());
	if($("#part_desc"+id+"_return_flag").val()==1){
		var cust = $("#supplier_sno").val();
		var execution_url = "scripts/ajax.php?id=chk_inventory&return=1&term="+val+"&cur_date="+cur_date+"&store="+store+"&type="+form_type+"&cust="+cust;
	}
	else{
		var execution_url = "scripts/ajax.php?id=chk_inventory&term="+val+"&cur_date="+cur_date+"&store="+store+"&type="+form_type;
	}
	if(val!=''){
		$.ajax({
			url: execution_url,
			dataType:"json"
		})
		.done(function( data ) {
			data = data[0];
			var id = document.getElementById('current').value;
			var return_flag = $("#part_desc"+id+"_return_flag").val();
			if(return_flag==1){
				if(data.id==0){
					alert("Cannot Create Item in Return Mode.");
					$("[name='part_desc"+id+"_product']").val('');
					$("[name='part_desc"+id+"_product']").focus();
					return;
				}
			}
			if(data.id!=0){
				if(form_type=='sale' || form_type=='sale_pos'){
					if(data.negative_stock_sale=='0' && data.avail_qty<=0){
						alert('No stock available. Cannot Sale.');
						$("[name='part_desc"+id+"_product']").val('');
						return;
					}
					else if(data.avail_qty<=0){
						data.avail_qty = '<span style="color:#ff0000; font-weight:bold">'+data.avail_qty+'</span>';
					}
					else if(data.avail_qty<= data.warning){
						alert('Available quantity is below warning level.')
					}
				}
				var unit='';
				var first_unit='';
				$('#part_desc'+id+'_unitprice').val('');
				$('#part_desc'+id+'_eprice').val('');
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
						unit=unit+'<option value="'+k+'" data-id="'+conv_value+'">'+v+'</option>';

					});
				});
				var state = $("#state_hidden").val();
				if(!state){
					state = default_state;
				}
				//console.log(default_state+'>>'+state);
				if(state != default_state){
					var gst = 'IGST @ '+(data.vat*2)+'%';
				}
				else{
					var gst = 'SGST @ '+data.vat+'%<br />CGST @ '+data.vat+'%';
				}
				var old_price = $("#part_desc"+id+"_eprice").val();
				if(old_price!=''){
					data.sale_price = old_price;
				}
				$("[name='part_desc"+id+"_product']").val(data.label);
				if($('#part_desc'+id+'_sno').val()!=data.id){
					$('#part_desc'+id+'_unitprice').val(data.unit_price);
				}
				$('#part_desc'+id+'_sno').val(data.id);
				$('.part_desc'+id+'_cgst').html(gst);
				$('.part_desc'+id+'_cgst_rate_hidden').val(data.excise+'%');
				$('.part_desc'+id+'_sgst_rate_hidden').val(data.vat+'%');
				$('.part_desc'+id+'_unit_conv_value').val(first_unit);
				$('#part_desc'+id+'_unit').html(unit);
				$('#part_desc'+id+'_quantity').val('1');
				//$('.part_desc'+id+'_hsn_code').html();
				
				$('.part_desc'+id+'_avail_qty').html('Qty : '+data.avail_qty+'| HSN : '+data.hsn_code);
				$('#part_desc'+id+'_description').val(data.product_description);
				$('#part_desc'+id+'_discount').val(data.discount);
				$('#part_desc'+id+'_ebarcode').val(data.enable_barcode);
				$('#part_desc'+id+'_ebatchno').val(data.enable_batch_no);
				$('#part_desc'+id+'_batch_no').val(data.batch_no);
				$('#part_desc'+id+'_inv_type').val(data.inv_type);
				if(form_type=='sale_pos'){
					$('#part_desc'+id+'_eprice').val(data.sale_price);
					reverse_calculate(id);
				}
				else{
					$('#part_desc'+id+'_eprice').val(data.sale_price);
					reverse_calculate(id);
				}
				if(data.table_name=='barcode_new'){
					$('#part_desc'+id+'_quantity').val('1');
					record_barcode(id, data.barcode1);
				}
				if(data.multi_store=='1'){
					$('.part_desc'+id+'_hsn_code').html($("#storeid").clone());
					//$('a.part_desc'+id+'_multistore > select').addClass("small");
					$('a.part_desc'+id+'_hsn_code > select').addClass("fieldtextmedium");
					$('a.part_desc'+id+'_hsn_code > select').removeClass("medium");
					$('a.part_desc'+id+'_hsn_code > select').prop("id", 'part_desc'+id+'_store')
					$('a.part_desc'+id+'_hsn_code > select').prop("name", 'part_desc'+id+'_store')
				}
				console.log(form_type+' >> '+return_flag);
				if((form_type=='sale' || form_type=='sale_revert') && return_flag==1){
					$("#part_desc"+id+"_description").attr("readonly","readonly");
					$("#part_desc"+id+"_unit").attr("readonly","readonly");
					$("#part_desc"+id+"_unitprice").attr("readonly","readonly");
					$("#part_desc"+id+"_scheme").attr("readonly","readonly");
					$("#part_desc"+id+"_discount").attr("readonly","readonly");
					$("#part_desc"+id+"_eprice").attr("readonly","readonly");
					$("#part_desc"+id+"_s_no").val(data.batch_sno);
					$("#part_desc"+id+"_quantity").focus();
				}
				if(data.enable_multiple_price=='TRUE' && $("#part_desc"+id+"_return_flag").val()!=1){
					var multiple_price = '<table class=" table table-striped table-hover table-bordered"><tr><th colspan="4">Multiple Prices Available (Please Select)</td></tr><tr><td>1.</td><td>MRP</td><td>'+data.mrp+'</td><td><input type="button" onClick="$(\'#part_desc'+id+'_eprice\').val('+data.mrp+'); reverse_calculate('+id+'); $(\'#element_to_pop_up3\').bPopup().close(); $(\'#part_desc'+id+'_quantity\').focus(); calculate_total('+id+');" value="Select"></td></tr><tr><td>2.</td><td>SRP</td><td>'+data.srp+'</td><td><input type="button" onClick="$(\'#part_desc'+id+'_eprice\').val(\''+data.srp+'\'); reverse_calculate('+id+'); $(\'#element_to_pop_up3\').bPopup().close(); $(\'#part_desc'+id+'_quantity\').focus(); calculate_total('+id+');" value="Select"></td></tr><tr><td>3.</td><td>MSRP</td><td>'+data.msrp+'</td><td><input type="button" onClick="$(\'#part_desc'+id+'_eprice\').val(\''+data.msrp+'\'); reverse_calculate('+id+'); $(\'#element_to_pop_up3\').bPopup().close(); $(\'#part_desc'+id+'_quantity\').focus(); calculate_total('+id+');" value="Select"></td></tr></table>';
					$("#product_details").html(multiple_price);
					$('#element_to_pop_up3').bPopup({
						modalClose: false,
						closeClass: 'saveForm',
						opacity: 0.6,
						positionStyle: 'fixed', //'fixed' or 'absolute'
						modalColor: 'greenYellow',
						onClose: function() {
							if(form_type=='sale_pos'){
								$("#part_desc"+id+"_quantity").focus();
							}
							else{
								$("#part_desc"+id+"_description").focus();
							}
							
						}
					});
				}				
				
			
				if(data.enable_batch_no=='TRUE'){
					var edit_sno = $("#edit_sno").val();
					var batch_no = '';
					var expiry = new Date();
					var mfg = new Date();
					if(edit_sno!=''){
						$.ajax({
						  type: "GET",
							dataType: "json",
							async: false,
							cache: false,
							url: "scripts/ajax.php?id=get_batch&term=a&edit_sno="+edit_sno+"&part_id="+data.id+"&part_desc="+id+"&form_type="+form_type
						})
						.done(function(batch_data){
							document.getElementById('loader').style.display = 'none';
							batch_data = batch_data[0];
							//console.log(batch_data);
							batch_no = batch_data.batch_no;
							expiry = batch_data.expiry;
							mfg = batch_data.mfg_date;
						});
					}	
					else{
						var batch_no = '';
						var expiry = new Date();
						var mfg = new Date();
					}
					if(form_type=='purchase'){
						var txt = '<table width="100%" class=" table table-striped table-hover table-bordered"><tr><td>Batch No. :</td><td><input type="text" class="form-control" name="batch_no" id="batch_no" value="'+batch_no+'" tabindex="9999"></td></tr><tr><td>Expiry Date :</td><td>';
						
						//<input type="text" name="expiry" id="expiry" value="">
						var today = new Date();
						txt += DateInput('expiry', 'product_details', false, 'YYYY-MM-DD', expiry, 10000);
						txt += '</td></tr><tr><td>Manufacturing Date :</td><td>';
						txt += DateInput('mfg_date', 'product_details', false, 'YYYY-MM-DD', mfg, 10010);
						txt += '</td></tr><tr><td colspan="2" align="center"><input type="hidden" name="prod_sno" id="prod_sno" value="'+data.id+'"><button id="saveForm" name="saveForm" class="btn btn-success" type="button" value="Save" onClick="update_batch('+id+');" tabindex="10020">Save</button> <button id="cancelForm" name="cancelForm" class="btn btn-danger" type="button" value="Cancel" onClick="$(\'#element_to_pop_up3\').bPopup().close();"  tabindex="10021">Cancel</button></td></tr></table><center><img src="images/loading_transparent.gif" id="loader_stock" style="display:none;" /></center>';
						$('#part_desc'+id+'_sno').val(data.id);
						$("#product_details").html(txt);
						$('#element_to_pop_up3').bPopup({
							modalClose: false,
							closeClass: 'saveForm',
							opacity: 0.6,
							positionStyle: 'fixed', //'fixed' or 'absolute'
							modalColor: 'greenYellow',
							onClose: function() {
								if(form_type=='sale_pos'){
									$("#part_desc"+id+"_quantity").focus();
								}
								else{
									$("#part_desc"+id+"_description").focus();
								}

							}
						});
						$("#batch_no").focus();
						//$('.part_desc'+id+'_batch_no').html(batch_no);
						//$('.part_desc'+id+'_expiry').html(data.expiry);
						//$('.part_desc'+id+'_mfg_date').html(data.mfg_date);
					}
					else if(form_type=='sale' || form_type=='sale_pos' || form_type=='purchase_revert'){
						if(return_flag==0){
							var txt = '<table width="100%" class=" table table-striped table-hover table-bordered"><tr><td>Batch No. :</td><td><select name="batch_no" id="batch_no" style="width:100%">';
							//console.log(batch_data);
							$.each( data.batch_no_options, function( index, value ) {
								$.each(value, function(k,v){
									k = k.replace("key_", "");
									txt=txt+'<option value="'+v+','+k+'" ';
									if(k==data.batch_no){
										txt=txt+'selected="selected"';
									}
									txt = txt+'>'+v+'</option>';

								});
							});

							//var tst = document.getElementById("batch_no").value();

							txt = txt+'</select></td></tr><tr><td colspan="2" align="center"><input type="hidden" name="prod_sno" id="prod_sno" value="'+data.id+'"><input type="hidden" name="s_no" id="s_no" value=""><button id="saveForm" name="saveForm" class="btn btn-success" type="button" value="Save" onClick="update_batch('+id+', $(\'#batch_no\').val()); $(\'#element_to_pop_up3\').bPopup().close();">Save</button> <button id="cancelForm" name="cancelForm" class="btn btn-danger" type="button" value="Cancel" onClick="$(\'#element_to_pop_up3\').bPopup().close();">Cancel</button></td></tr></table><center><img src="images/loading_transparent.gif" id="loader_stock" style="display:none;" /></center>';
							//console.log(txt);
							$('#part_desc'+id+'_sno').val(data.id);
							$("#product_details").html(txt);
							$('#element_to_pop_up3').bPopup({
								modalClose: false,
								closeClass: 'saveForm',
								opacity: 0.6,
								positionStyle: 'fixed', //'fixed' or 'absolute'
								modalColor: 'greenYellow',
								onClose: function() {
									if(form_type=='sale_pos'){
										$("#part_desc"+id+"_quantity").focus();
									}
									else{
										$("#part_desc"+id+"_description").focus();
									}

								}
							});
							$("#batch_no").focus();
						}
						else{
							$('.part_desc'+id+'_avail_qty').html('Batch : '+data.batch_no);
						}
					}
				}
				var txt = '<table width="100%" class=" table table-striped table-hover table-bordered"><tr><td>Unit :</td><td><select name="unit" id="unit">';
			
				$.each( data.unit_options, function( index, value ) {
					$.each(value, function(k,v){
						k = k.replace("key_", "");
						if(k<=44){
							txt=txt+'<option value="'+k+'" ';
							if(k==data.unit){
								txt=txt+'selected="selected"';
							}
							txt = txt+'>'+v+'</option>';
						}

					});
				});
				
				txt = txt+'</select></td></tr>';
				
				txt += '<tr><td>Unit 2:</td><td><select name="unit2" id="unit2">';
				
				$.each( data.unit_options, function( index, value ) {
					$.each(value, function(k,v){
						k = k.replace("key_", "");
						if(k<=44){
							txt=txt+'<option value="'+k+'" ';
							if(k==data.unit){
								txt=txt+'selected="selected"';
							}
							txt = txt+'>'+v+'</option>';
						}

					});
				});
				
				txt += '<input type="text" name="conversion2" id="conversion2" class="input" placeholder="Conversion"/>';
				
				txt += '<tr><td>MRP :</td><td><input type="text" name="mrp" id="mrp" value="'+data.mrp+'"></td></tr><tr><td>SRP :</td><td><input type="text" name="srp" id="srp" value="'+data.srp+'"></td></tr><tr><td>MSRP :</td><td><input type="text" name="msrp" id="msrp" value="'+data.msrp+'"></td></tr><tr><td>Warranty :</td><td><input type="text" name="stock_warranty" id="stock_warranty" value="'+data.warranty+'"></td></tr><tr><td>Warning Qty :</td><td><input type="text" name="stock_warning" id="stock_warning" value="'+data.warning+'"></td></tr><tr><td>Barcode :</td><td><input type="text" name="stock_barcode" id="stock_barcode" value="'+data.barcode+'"></td></tr><tr><td>HSN Code :</td><td><input type="text" name="hsn_code" id="hsn_code" value="'+data.hsn_code+'"></td></tr><tr><td>CGST :</td><td><input type="text" name="vat" id="vat" value="'+data.vat+'"></td></tr><tr><td>SGST :</td><td><input type="text" name="excise" id="excise" value="'+data.excise+'"></td></tr><tr><td>Enable Barcode</td><td><input type="checkbox" name="enable_barcode" id="enable_barcode"';
				if(!data.enable_barcode){
					data.enable_barcode = 'FALSE';
				}
				if(!data.enable_batch_no){
					data.enable_batch_no = 'FALSE';
				}
				data.enable_barcode = data.enable_barcode.toUpperCase();
				data.enable_batch_no = data.enable_batch_no.toUpperCase();
				//console.log(data.enable_barcode);
				if(data.enable_barcode=='ON' || data.enable_barcode=='TRUE'){
					txt = txt + ' checked="checked" ';
				}
				txt = txt + '/></td></tr><tr><td>Enable Batch No.</td><td><input type="checkbox" name="enable_batch_no" id="enable_batch_no"';
				if(data.enable_batch_no=='on' || data.enable_batch_no=='TRUE'){
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
						k = k.replace("key_", "");
						txt=txt+'<option value="'+k+'" ';
						if(k==data.type){
							txt=txt+'selected="selected"';
						}
						txt = txt+'>'+v+'</option>';

					});
				});
				txt=txt+'</select></td></tr><tr><td>Company</td><td><select name="company" id="company"><option value=""></option>';
	
				$.each( data.company_options, function( index, value ) {
					$.each(value, function(k,v){
						k = k.replace("key_", "");
						txt=txt+'<option value="'+k+'" ';
						if(k==data.company){
							txt=txt+'selected="selected"';
						}
						txt = txt+'>'+v+'</option>';

					});
				});

				txt = txt+'</select></td></tr><tr><td colspan="2" align="center"><input type="hidden" name="prod_sno" id="prod_sno" value="'+data.id+'"><input type="hidden" name="prod_desc" id="prod_desc" value="'+val+'"><input id="saveForm" name="saveForm" class="btTxt submit" type="button" value="Save" onClick="save_stock();"><input id="cancelForm" name="cancelForm" class="btTxt submit" type="button" value="Cancel" onClick="$(\'#element_to_pop_up2\').bPopup().close();"></td></tr></table><center><img src="images/loading_transparent.gif" id="loader_stock" style="display:none;" /></center>';
				
				var append = '<input type="button" onClick="$(\'#element_to_pop_up2\').bPopup({modalClose: false, 	closeClass: \'saveForm\', opacity: 0.6,	positionStyle: \'fixed\', modalColor: \'greenYellow\', onClose: function() {$(\'#part_desc'+id+'_description\').focus();}}); $(\'#element_to_pop_up2 #unit\').focus();" value="EDIT" class="small">';
				//$("[name='part_desc"+id+"_product']").parent().append(append);
				
				//alert('part_desc'+id+'_unit');
				if(data.data_type=="yes"){
					$('#part_desc'+id+'_usertext').val(data.barcode1);
				}
				$("#stock_details").html(txt);
				/*$('#element_to_pop_up2').bPopup({
					modalClose: false,
					closeClass: 'saveForm',
					opacity: 0.6,
					positionStyle: 'fixed', //'fixed' or 'absolute'
					modalColor: 'greenYellow',
					onClose: function() {
						$("#part_desc"+id+"_description").focus();
					}
				});
				$("#element_to_pop_up2 #unit").focus();*/
			}
			else{
				$.ajax({
					url: "scripts/ajax.php?id=misc&term=1",
					dataType:"json"
				})
				.done(function( data ) {
					data = data[0];
					var unit='';
					var type='';
					var company='';
					var gst='';

					$.each(data.unit, function( k, v ) {
						//$.each(value, function(k,v){
						k = k.replace("key_", "");
						if(k<=44){
							unit=unit+'<option value="'+k+'">'+v+'</option>';
						}
						//});
					});
					
					var unit_conv_row = '<tr><td>Unit 2:</td><td><select name="unit2" id="unit2" class="small">';
				
					$.each( data.unit, function( k, v) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							unit_conv_row += '<option value="'+k+'">'+v+'</option>';
						//});
					});

					unit_conv_row += '</td><td><input type="text" name="conversion2" id="conversion2" class="input small" placeholder="Conversion"/></td></tr>';
					
					unit_conv_row += '<tr><td>Unit 3:</td><td><select name="unit3" id="unit3" class="small">';
				
					$.each( data.unit, function( k, v) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							unit_conv_row += '<option value="'+k+'">'+v+'</option>';
						//});
					});

					unit_conv_row += '</td><td><input type="text" name="conversion3" id="conversion3" class="input small" placeholder="Conversion"/></td></tr>';
					
					$.each(data.type, function( k, v ) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							type=type+'<option value="'+k+'">'+v+'</option>';

						//});
					});
					$.each(data.company, function( k, v ) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							company=company+'<option value="'+k+'">'+v+'</option>';

						//});
					});
					$.each(data.gst, function( k, v ) {
						//$.each(value, function(k,v){
							k = k.replace("key_", "");
							gst=gst+'<option value="'+k+'">'+v+'</option>';

						//});
					});
					
					if(software_type=='pharma'){						
						var pharma = '<tr><td>Drug Category:</td><td colspan="3"><input type="radio" name="category" value="schedule_h"> Schedule H Drug<input type="radio" name="category" value="schedule_h1"> Schedule H1 Drug <br/><input type="radio" name="category" value="narcotics"> Narcotic Drug <input type="radio" name="category" value="other"> Other Special Category Drug </td></tr>';
					}	
					else{
						var pharma = '';
					}
					var txt = '<table class="table table-striped table-hover" width="100%"><tr><td style="width:100px;">Unit :</td><td style="width:150px;"><select name="unit" id="unit" class="small">'+unit+'</select></td><td style="width:150px;">&nbsp;</td></tr>'+unit_conv_row+'<tr><td>Prices :</td><td><input type="text" name="mrp" id="mrp" value="" placeholder="MRP" class="small"></td><td><input type="text" name="srp" id="srp" value="" placeholder="SRP" class="small"></td><td><input type="text" name="msrp" id="msrp" value="" placeholder="MSRP" class="small"></td></tr><tr><td>GST Details :</td><td><input type="text" name="hsn_code" id="hsn_code" value="" placeholder="HSN Code" class="small"></td><td colspan="2"><select name="gst" id="gst" tabindex="" class="small">'+gst+'</select></td></tr><tr><td>Misc Details :</td><td><input type="text" name="stock_warranty" id="stock_warranty" value="" placeholder="Warranty" class="small"></td><td><input type="text" name="stock_warning" id="stock_warning" value="" placeholder="Warning Qty" class="small"></td><td><input type="text" name="stock_barcode" id="stock_barcode" value="" placeholder="Model No." class="small"></td></tr><tr><td align="center"><input type="hidden" name="prod_sno" id="prod_sno" value="">Enable Serial No.: </td><td><input type="checkbox" name="enable_barcode" id="enable_barcode" ></td><td>Enable Batch No.: </td><td><input type="checkbox" name="enable_batch_no" id="enable_batch_no" ></td></tr>'+pharma+'<tr><td>PRODUCT TYPE</td><td><select name="inv_type" id="inv_type" class="small"><option value="tax">TAXABLE</option><option value="exempt">EXEMPT</option></select></td><td><select name="type" id="type" class="small"><option value="">Select Group</option>'+type+'</select></td><td><select name="company" id="company" class="small"><option value="">Select Company</option>'+company+'</select></td></tr><tr><td colspan="2"><input type="hidden" name="prod_desc" id="prod_desc" value="'+val+'"><input id="saveForm" name="saveForm" class="btTxt submit" type="button" value="Save" onClick="save_stock();"></td><td colspan="2"><input id="cancelForm" name="cancelForm" class="btTxt submit" type="button" value="Cancel" onClick="$(\'#element_to_pop_up2\').bPopup().close();"></td></tr></table><center><img src="images/loading_transparent.gif" id="loader_stock" style="display:none;" /></center>';
					$("#stock_details").html(txt);
					$('#element_to_pop_up2').bPopup({
						modalClose: false,
						closeClass: 'saveForm',
						opacity: 0.6,
						positionStyle: 'fixed', //'fixed' or 'absolute'
						modalColor: 'greenYellow',
						onClose: function() {
							//$("#part_desc"+id+"_description").focus();
						}
					});
					$("#unit").focus();
					
				});
			}
		});
	}
}

function record_barcode(value, serial_no=''){
	var ebarcode = document.getElementById('part_desc'+value+'_ebarcode').value.toUpperCase();
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
			if(ebarcode=='ON' || ebarcode=='TRUE'){
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
					inputPOP += i+'. <input type="text" name="barcode_'+value+'_'+i+'" id="barcode_'+value+'_'+i+'" value="'+finaldata[1]+serial_no+'" onblur="duplicate_check('+value+', '+count+');" onfocus="duplicate_check();"><br />';
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
						//alert(form_type);
						if(form_type=='sale_pos'){
							document.getElementById("part_desc"+value+"_unit").focus();
						}
						else{
							document.getElementById("part_desc"+value+"_unitprice").focus();
						}
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
	var url = window.location.href;
	var edit = url.search("id=");
	if(edit==-1){
		edit='';
	}
	else{
		edit = url.split("id=");
		edit = edit[1];
		edit = '&ed='+edit;
	}
	xmlhttp.open("GET","scripts/save_barcode.php?id=get&epin="+epin+"&elem=part_desc"+value+edit,true);
	xmlhttp.send();	
	//get_barcode_data(epin,value);
	// Do something with content:
	var inputPOP = '';
	calculate_total(value);
}

function duplicate_check(row_id, count){
	var data = [];
	for(var i=1; i<=count; i++){
		if($("#barcode_"+row_id+"_"+i).val()!=''){
			if(data.indexOf($("#barcode_"+row_id+"_"+i).val())!=-1){
				var index = data.indexOf($("#barcode_"+row_id+"_"+i).val());
				alert("Duplicate Barcode. Same exists on : "+index);
				$("#barcode_"+row_id+"_"+i).val('');
				$("#barcode_"+row_id+"_"+i).focus();
			}
			else{
				data[i] = $("#barcode_"+row_id+"_"+i).val();
			}				
		}
	}
	//console.log(data);
}

function update_price(id, val){
	var base_unit = parseFloat($("#part_desc"+id+"_unit option:first-child").data('id'));
	var val = parseFloat($("#part_desc"+id+"_unit").find(':selected').data('id'));
	var prev_conv = parseFloat($(".part_desc"+id+"_unit_conv_value").val());
	var price = parseFloat($("#part_desc"+id+"_unitprice").val());
	var disc = $("#part_desc"+id+"_discount").val();
	
	if(disc.search('%')==-1){
		disc = parseFloat(disc);
	}
	else{
		disc = disc.replace('%','');
		disc = 0;
	}
	//console.log(base_unit+'##'+val+'##'+prev_conv+'##'+price);		
	if(!prev_conv){
		prev_conv=1;
	}
	if(!price){
		price='';
	}
	if(price!=''){
		if(val>=1){
			if(prev_conv>=1){
				price = price*prev_conv;
				disc = disc*prev_conv;
			}
			else{
				price = price*prev_conv;
				disc = disc*prev_conv;
			}
			price = price/val;
			disc = disc/val;
		}
		else{
			//console.log('Less');
			price = price*prev_conv;
			disc = disc*prev_conv;
			price = price/val;
			disc = disc/val;
		}
	}
	price = Math.round(price*100)/100;
	disc = Math.round(disc*100)/100;
	$("#part_desc"+id+"_unitprice").val(price);
	if(disc!=0){
		$("#part_desc"+id+"_discount").val(disc);
	}
	$(".part_desc"+id+"_unit_conv_value").val(val);
	calculate_total(id);
}

function update_batch(id, batch_no=''){
	if(form_type=='purchase'){
		$('#part_desc'+id+'_batch_no').val($("#batch_no").val());
		$('#part_desc'+id+'_expiry').val($("#expiry").val());
		$('#part_desc'+id+'_mfg_date').val($("#mfg_date").val());
		$("#element_to_pop_up3").bPopup().close();
		$("#part_desc"+id+"_description").focus();
	}
	else if(form_type=='sale' || form_type=='sale_pos' || form_type=='purchase_revert'){
		batch_no = batch_no.split(',');
		var expiry = batch_no[1].substr(6);
		var mfg_date = batch_no[2].substr(6);
		var s_no = batch_no[5];
		var srp = batch_no[4].substr(5);
		if(srp!=''){
			$('#part_desc'+id+'_eprice').val(srp);
			reverse_calculate(id);
		}
		/*console.log(s_no+'>>'+srp);
		console.log(expiry);
		console.log(mfg_date);
		console.log(s_no);*/
		$('#part_desc'+id+'_batch_no').val(batch_no[0]);
		$('#part_desc'+id+'_expiry').val(expiry);
		$('#part_desc'+id+'_mfg_date').val(mfg_date);
		$('#part_desc'+id+'_s_no').val(s_no);
		$("#part_desc"+id+"_description").focus();
		$(".part_desc"+id+"_avail_qty").html($(".part_desc"+id+"_avail_qty").html()+"  | Batch:"+batch_no[0]);
		$(".part_desc"+id+"_hsn_code").html($(".part_desc"+id+"_hsn_code").html()+"  | Expiry:"+expiry);
		if(form_type=='sale_pos'){
			$("#part_desc"+id+"_quantity").focus();
		}
		else{
			$("#part_desc"+id+"_description").focus();
		}
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
	var enable_batch_no=$('#enable_batch_no').is(':checked');
	var type = $('#type').val();
	var company = $('#company').val();
	var unit = $('#unit').val();
	var unit2 = $('#unit2').val();
	var unit2_conv = $('#conversion2').val();
	var unit3 = $('#unit3').val();
	var unit3_conv = $('#conversion3').val();
	var gst = $('#gst').val();
	var id = document.getElementById('current').value;
	//alert(stock_barcode);
	var stock_warning = $('#stock_warning').val();
	var prod_sno = $('#prod_sno').val();
	finaldata = 'mrp='+mrp+'&srp='+srp+'&msrp='+msrp+'&stock_warranty='+stock_warranty+'&stock_barcode='+encodeURI(stock_barcode)+'&hsn_code='+encodeURI(hsn_code)+'&prod_sno='+prod_sno+'&prod_desc='+prod_desc+'&stock_warning='+stock_warning+'&enable_barcode='+enable_barcode+'&inv_type='+inv_type+'&enable_batch_no='+enable_batch_no+'&type='+type+'&company='+company+'&gst='+gst+'&unit='+unit+'&unit2='+unit2+'&unit2_conv='+unit2_conv+'&unit3='+unit3+'&unit3_conv='+unit3_conv;
	$.ajax({
	  type: "GET",
	  url: "scripts/ajax.php?id=save_stock&term=1",
	  data: finaldata,
	  cache: false,
	  //success: result
	  complete: function(response){
		  document.getElementById('loader_stock').style.display = 'none';
		  if(response.responseText==''){
			  $("#element_to_pop_up2").bPopup().close();
			  /*$("#part_desc"+id+"_ebarcode").val(enable_barcode);
			  $("#part_desc"+id+"_ebatchno").val(enable_batch_no);
			  $(".part_desc"+id+"_hsn_code").html(hsn_code);
			  $(".part_desc"+id+"_cgst").html(vat);
			  $(".part_desc"+id+"_sgst").html(excise);*/
			  $("[name='part_desc"+id+"_product']").focus();
			  //chk_inventory(prod_desc);
			  
		  }
		  else{
			  //alert(response.responseText);
		  }
	  }
	});
}

function getCurrent(id){
	document.getElementById('current').value = id;
	if(form_type=='sale' || form_type=='sale_pos'){
		if(id!=1){
			$.ajax({
                url: 'scripts/autosave_sale.php', // url where to submit the request
                type : "POST", // type of action POST || GET
                dataType : 'json', // data type
                data : $("#sale_form").serialize(), // post data || get data
                success : function(result) {
                    // you can see the result from the console
                    // tab of the developer tools
                    //console.log(result);
                },
                error: function(xhr, resp, text) {
                    //console.log(xhr, resp, text);
                }
            })			
		}
	}
	else if(form_type=='purchase'){
		if(id!=1){
			$.ajax({
                url: 'scripts/autosave_purchase.php', // url where to submit the request
                type : "POST", // type of action POST || GET
                dataType : 'json', // data type
                data : $("#purchase_form").serialize(), // post data || get data
                success : function(result) {
                    // you can see the result from the console
                    // tab of the developer tools
                    //console.log(result);
                },
                error: function(xhr, resp, text) {
                    //console.log(xhr, resp, text);
                }
            })			
		}
	}
}

function change_mop(){
	$("#tender_amount").val('');
	$("#tender_amount_return").val('');
	if($("#mode_of_payment").val()=='cash'){
		$("#mop_detail").html("Tender Amount");
		$("#mop_detail_other").html("Change Returned");
		$("#tender_amount_return").attr("readonly", true);
	}
	else if($("#mode_of_payment").val()=='card'){
		$("#mop_detail").html("Last 4 Digits");
		$("#mop_detail_other").html("Transaction ID");
		$("#tender_amount_return").attr("readonly", false);
	}
	else if($("#mode_of_payment").val()=='other'){
		$("#mop_detail").html("Mode Details");
		$("#mop_detail_other").html("Reference No.");
		$("#tender_amount_return").attr("readonly", false);
	}
}

function calculate_tendered(){
	if($("#mode_of_payment").val()=='cash'){
		var total_amt = parseFloat($("#total_amount1").val());
		if(!total_amt){
			total_amt=0;
		}
		var tendered = parseFloat($("#tender_amount").val());
		if(!tendered){
			tendered=0;
		}
		var change = tendered-total_amt;
		$("#tender_amount_return").val(change);
		
	}
}

function sortTable(tableId, colId) {
	var n = colId
	var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	table = document.getElementById(tableId);
	switching = true;
	// Set the sorting direction to ascending:
	dir = "asc";
	/* Make a loop that will continue until
	no switching has been done: */
	while (switching) {
		// Start by saying: no switching is done:
		switching = false;
		rows = table.rows;
		/* Loop through all table rows (except the
		first, which contains table headers): */
		for (i = 1; i < (rows.length - 1); i++) {
			// Start by saying there should be no switching:
			shouldSwitch = false;
			/* Get the two elements you want to compare,
			one from current row and one from the next: */
			x = rows[i].getElementsByTagName("TD")[n];
			y = rows[i + 1].getElementsByTagName("TD")[n];
			/* Check if the two rows should switch place,
			based on the direction, asc or desc: */
			if (dir == "asc") {
				if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
					// If so, mark as a switch and break the loop:
					shouldSwitch = true;
					break;
				}
			} else if (dir == "desc") {
				if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
					// If so, mark as a switch and break the loop:
					shouldSwitch = true;
					break;
				}
			}
		}
		if (shouldSwitch) {
			/* If a switch has been marked, make the switch
			and mark that a switch has been done: */
			rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
			switching = true;
			// Each time a switch is done, increase this count by 1:
			switchcount ++;
		} else {
			/* If no switching has been done AND the direction is "asc",
			set the direction to "desc" and run the while loop again. */
			if (switchcount == 0 && dir == "asc") {
				dir = "desc";
				switching = true;
			}
		}
	}
}
