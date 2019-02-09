<?php
/*
=====================================================
 DLEFA Pay  Ver 1.0
-----------------------------------------------------
 Persian support site: https://dlefa.ir
-----------------------------------------------------
 FileName :  result.php
-----------------------------------------------------
 Copyright (c) 2018, All rights reserved.
=====================================================
 */

defined("DATALIFEENGINE") || exit();

$module_title = $pay_config['module_title'];

require_once ENGINE_DIR . '/modules/pay/data/data.php';

$bankid = $db->safesql($_GET['bankid']);

switch ($bankid) {

	case "mellat":
		require_once ENGINE_DIR . '/modules/pay/gateway/mellat.php';
		$orderid = $db->safesql($_GET['orderid']);
		if (empty($orderid)) {
			header("HTTP/1.0 301 Moved Permanently");
			header("Location: {$config['http_home_url']}pay");
			die("Redirect");
		} else {
			$invoice = $db->super_query("SELECT id, amount, mob FROM " . PREFIX . "_pay_invoices WHERE refid='{$orderid}'");
			if ($invoice) {
				$result = Verify($pay_config['mellat_terminalid'], $pay_config['mellat_username'], $pay_config['mellat_password'], $orderid);
				if ($result['status']) {
					$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$result['msg']}',`refid`='{$result['ref']}' WHERE id='{$invoice['id']}'");
					$refid = $result['ref'];
					$msg = $result['msg'];
				} else {
					$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$result['msg']}',`refid`='{$result['ref']}' WHERE id='{$invoice['id']}'");
					$refid = $result['ref'];
					$msg = $result['msg'];
				}
			} else
				$msg = "فیش پرداختی در سیستم یافت نشد.";
			$tpl->set_block("'\\[success\\](.*?)\\[/success\\]'si", "");
		}
		break;
	case "zarinpal":
		require_once ENGINE_DIR . '/modules/pay/gateway/zarinpal.php';
		$Authority = $db->safesql($_GET['Authority']);
		$status = $db->safesql($_GET['Status']);
		if (strtolower($status) == "ok") {
			$invoice = $db->super_query("SELECT id, amount, mob FROM " . PREFIX . "_pay_invoices WHERE refid='{$Authority}'");
			if ($invoice) {
				$result = Verify($pay_config['zarinpal_MerchantID'], $Authority, $invoice['amount']);
				if ($result['status']) {
					$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$result['msg']}',`refid`='{$result['ref']}' WHERE id='{$invoice['id']}'");
					$refid = $result['ref'];
					$msg = $result['msg'];
					$tpl->set('[success]', "");
					$tpl->set('[/success]', "");
				} else {
					$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$result['msg']}',`refid`='{$result['ref']}' WHERE id='{$invoice['id']}'");
					$refid = $result['ref'];
					$msg = $result['msg'];
				}
			} else
				$msg = "فیش پرداختی در سیستم یافت نشد.";
		} else
			$msg = "عملیات پرداخت توسط کاربر لغو گردید.";
		$bank = "زرین پال";
		break;


	case "payir":
		require_once ENGINE_DIR . '/modules/pay/gateway/payir.php';
		$status = $db->safesql($_GET['status']);
		$token = $db->safesql($_GET['token']);
		if (intval($status) == 1) {
			$invoice = $db->super_query("SELECT id, refid, amount, mob FROM " . PREFIX . "_pay_invoices WHERE refid='{$token}'");
			if ($invoice) {
				$result = json_decode(Verify($pay_config['payir_api'], $token));
				if ($result->status) {
					if ($result->status == 1) {
						$msg = "پرداخت موفق";
						$refid = $result->transId;
						$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$msg}' WHERE id='{$invoice['id']}'");
						$tpl->set('[success]', "");
						$tpl->set('[/success]', "");
					} else {
						$msg = $result->errorMessage;
						$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$msg}' WHERE id='{$invoice['id']}'");
					}
				} else {
					$msg = $result->errorMessage;
					$db->query("UPDATE " . PREFIX . "_pay_invoices SET `status`='{$msg}' WHERE id='{$invoice['id']}'");
				}
			} else
				$msg = "فیش پرداختی در سیستم یافت نشد.";
		} else
			$msg = "عملیات پرداخت توسط کاربر لغو گردید.";
		$bank = "payir";
		break;

	default:
		header("HTTP/1.0 301 Moved Permanently");
		header("Location: {$config['http_home_url']}pay");
		die("Redirect");
}



if (isset($refid) && !empty($refid)) {

	if ($pay_config['sms_on']) {
		$sms_text = str_replace('%pay_code%', $refid, $pay_config['sms_text']);
		send_sms($sms_text, $invoice['mob']);
		if ($pay_config['sms_admin_on']) {
			send_sms($pay_config['admin_text'], $pay_config['sms_admin']);
		}
	}

	$tpl->load_template('pay/result.tpl');
} else {
	$tpl->load_template('pay/error.tpl');
}
$tpl->set('{bank}', $bank);
$tpl->set('{amount}', intval($invoice['amount']));
$tpl->set('{msg}', $msg);
$tpl->set('{pcode}', $refid);
$tpl->compile('content');
$tpl->clear();
?>