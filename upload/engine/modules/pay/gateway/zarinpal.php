<?php
/*
=====================================================
 DLEFA Pay  Ver 1.0
-----------------------------------------------------
 Persian support site: https://dlefa.ir
-----------------------------------------------------
 FileName :  zarinpal.php
-----------------------------------------------------
 Copyright (c) 2018, All rights reserved.
=====================================================
*/

if ($pay_config['sandbox'] == 1) {
    $Mtest = 1;
} else {
    $Mtest = 0;
}

if ($pay_config['zaringate'] == 1) {
    $ZarinGate = '/ZarinGate';
} else {
    $ZarinGate = '';
}

$Url = array(
	array('0' => "https://www.zarinpal.com/pg/services/WebGate/wsdl", '1' => "https://www.zarinpal.com/pg/StartPay/"),
	array('0' => "https://sandbox.zarinpal.com/pg/services/WebGate/wsdl", '1' => "https://sandbox.zarinpal.com/pg/StartPay/")
);
 
$error = array(
	'-1' => 'اطلاعات ارسال شده ناقص است',
	'-2' => 'IP و یا مرچنت کد پذیرنده صحیح نیست',
	'-3' => 'با توجه به محدودیت های شاپرک امکان پرداخت با رقم در خواست شده میسر نمی باشد',
	'-4' => 'سطح تایید پذیرنده یایین تر از سطح نقره ای است',
	'-11' => 'درخواست مورد نظر یافت نشد',
	'-12' => 'امکان ویرایش درخواست میسر نمی باشد',
	'-21' => 'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد',
	'-22' => 'تراكنش ناموفق مي باشد.',
	'-33' => 'رقم تراكنش با رقم پرداخت شده مطابقت ندارد',
	'-34' => 'سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است',
	'-40' => 'اجازه دسترسي به متد مربوطه وجود ندارد',
	'-41' => 'اطلاعات ارسال شده مربوط به  AdditionalDataغيرمعتبر ميباشد',
	'-42' => 'مدت زمان معتبر طول عمر شناسه پرداخت بايد بين  30دقيه تا  45روز مي باشد',
	'-45' => 'درخواست مورد نظر آرشيو شده است',
	'100' => 'عمليات با موفقيت انجام گرديده است',
	'101' => 'عمليات پرداخت موفق بوده و قبلا PaymentVerification تراكنش انجام شده است'
);

function Request($MerchantID, $amount, $callback){
	global $Url, $Mtest, $ZarinGate, $error;
	
	$client = new SoapClient($Url[$Mtest][0], ['encoding' => 'UTF-8']);
	$orderId = rand();
	$result = $client->PaymentRequest([
		'MerchantID' => $MerchantID,
		'Amount' => $amount,
		'Description' => 'پرداخت آنلاین سایت',
		//'Email' => '',
		//'Mobile' => '',
		'CallbackURL' => $callback,
	]);
	
	if ($result->Status == 100) {
		return array(
			'status' => true,
			'ref'    => $result->Authority,
			'url'    => $Url[$Mtest][1].$result->Authority.$ZarinGate
		);
	}else
		return array(
			'status' => false,
			'msg'	 => $error[$result->Status]
		);
}

function Verify($MerchantID, $Authority, $amount){
	global $Url, $Mtest, $error;
	
	$client = new SoapClient($Url[$Mtest][0], ['encoding' => 'UTF-8']);

	$result = $client->PaymentVerification([
		'MerchantID' => $MerchantID,
		'Authority' => $Authority,
		'Amount' => $amount,
	]);

	if ($result->Status == 100) {
		return array(
			'status' => true,
			'ref'    => $result->RefID,
			'msg'    => $error[$result->Status]
		);
	}else
		return array(
			'status' => false,
			'msg'    => $error[$result->Status]
		);
}

?>