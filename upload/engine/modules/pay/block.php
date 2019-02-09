<?php
/*
=====================================================
 DLEFA Pay  Ver 1.0
-----------------------------------------------------
 Persian support site: https://dlefa.ir
-----------------------------------------------------
 FileName :  block.php
-----------------------------------------------------
 Copyright (c) 2018, All rights reserved.
=====================================================
 */

defined("DATALIFEENGINE") || exit();

$module_title = $pay_config['module_title'];

$tpl->load_template('pay/block.tpl');

$JS = <<<HTML
<script>
function Pay(){
	ShowLoading('در حال برقراری ارتباط با درگاه بانک...');
	var amount = $("input[name=pay_amount]").val();
	if($("input[name=name]").val().length < 1){
	    HideLoading();
		DLEalert( 'نام و نام خانوادگی خود را وارد نمایید.', 'خطا' );
		return;
	}
	var name = $("input[name=name]").val();
	if($("input[name=mob]").val().length < 1){
	    HideLoading();
		DLEalert( 'لطفا تلفن همراه خود را وارد نمایید.', 'خطا' );
		return;
	}
    var phone = /^[0][9][0-9]{9,9}$/;
    if($("input[name=mob]").val().match(phone)) {
    	var mob = $("input[name=mob]").val();
    } else {
	    HideLoading();
        DLEalert( 'لطفا تلفن همراه خود را به صورت صحیح وارد نمایید.', 'خطای ورودی تلفن همراه' );
        return;
    }
    var message = $("#message").val();
	var bankid = $("select[name=pay_bankid]").val();
	$.post( dle_root+'engine/ajax/controller.php?mod=pay',{
		action: "pay",
		amount: amount,
		name: name,
		mob: mob,
		message: message,
		bankid: bankid
	}, function(result){
		if( result.status ){
			if( bankid == "mellat" ){
				var form = document.createElement("form");
				form.setAttribute("method", "POST");
				form.setAttribute("action", result.url);
				form.setAttribute("target", "_self");
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("name", "RefId");
				hiddenField.setAttribute("value", result.ref);
				form.appendChild(hiddenField);
				document.body.appendChild(form);
				form.submit();
				document.body.removeChild(form);
			}else if( bankid == "zarinpal" ){
				setTimeout(function(){
					window.location.replace(result.url);
					window.location.href=result.url;
				},3000);
			}else if( bankid == "payir" ){
				setTimeout(function(){
					window.location.replace(result.url);
					window.location.href=result.url;
				},3000);
			}
		}else{
			HideLoading();
			DLEalert( result.msg, 'خطا' );
		}
	},"JSON");
	return false;
}
</script>
HTML;
$tpl->copy_template = "<!--DLEFA Pay Plugin-->" . $JS . $tpl->copy_template . "<!--DLEFA Pay Plugin-->";
$tpl->set('{onclick}', 'onclick="Pay(); return false;"');
$tpl->set('{pay}', "<input type=\"submit\" name=\"pay\" class=\"btn btn-big\" value=\"پرداخت\" onclick=\"Pay(); return false;\">");
if ($is_logged) {
	$tpl->set('{input-name}', "<input type=\"text\" class=\"form-control width-200 ltr\" name=\"name\" id=\"name\" value=\"{$member_id['name']}\" readonly>");
	$xfs = xfieldsdataload($member_id['xfields']);
	$tpl->set('{input-mob}', "<input type=\"text\" class=\"form-control width-200 ltr\" name=\"mob\" id=\"mob\" value=\"{$xfs['mob']}\">");
} else {
	$tpl->set('{input-name}', "<input type=\"text\" class=\"form-control width-200\" name=\"name\" id=\"name\">");
	$tpl->set('{input-mob}', "<input type=\"text\" class=\"form-control width-200 ltr\" name=\"mob\" id=\"mob\">");
}
$tpl->set('{input-amount}', "<input type=\"text\" class=\"form-control width-200 ltr\" name=\"pay_amount\" id=\"pay_amount\">");

if ($pay_config['mellat_on'] || $pay_config['zarinpal_on'] || $pay_config['payir_on']) {
	$banks = "<select class=\"uniform\" name=\"pay_bankid\" id=\"pay_bankid\">";
	if ($pay_config['mellat_on']) $banks .= "<option value=\"mellat\">درگاه ملت</option>";
	if ($pay_config['zarinpal_on']) $banks .= "<option value=\"zarinpal\">درگاه زرین پال</option>";
	if ($pay_config['payir_on']) $banks .= "<option value=\"payir\">درگاه pay.ir </option>";
	$banks .= "</select>";
	$tpl->set('{bank-list}', $banks);
} else {
	$tpl->set('{bank-list}', "درگاهی توسط مدیر سایت تعریف نشده است!");
}
$tpl->set('{input-desc}', "<textarea name=\"message\" id=\"message\" rows=\"8\" class=\"wide\"></textarea>");
$tpl->compile('content');
$tpl->clear();
echo $tpl->result['pay'];
?>