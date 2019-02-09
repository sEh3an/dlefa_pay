<?php
/*
=====================================================
 DLEFA Pay Ver 1.0
-----------------------------------------------------
 Persian support site: https://dlefa.ir
-----------------------------------------------------
 FileName :  pay.php
-----------------------------------------------------
 Copyright (c) 2018, All rights reserved.
=====================================================
 */

if (!defined('DATALIFEENGINE')) {
	header("HTTP/1.1 403 Forbidden");
	header('Location: ../../');
	die("Hacking attempt!");
}

include_once(DLEPlugins::Check(ENGINE_DIR . '/data/config.php'));

require_once(DLEPlugins::Check(ENGINE_DIR . '/classes/mysql.php'));
require_once(DLEPlugins::Check(ENGINE_DIR . '/data/dbconfig.php'));
require_once(DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php'));
require_once(DLEPlugins::Check(ENGINE_DIR . '/modules/pay/data/data.php'));

dle_session();

$action = $db->safesql($_POST['action']);
$amount = intval($_POST['amount']);
$name = $db->safesql($_POST['name']);
$mob = $db->safesql($_POST['mob']);
$message = $db->safesql($_POST['message']);
$bankid = $db->safesql($_POST['bankid']);

if ($action == "pay") {

	switch ($bankid) {

		case "mellat":
			require_once ENGINE_DIR . '/modules/pay/gateway/mellat.php';
			$orderid = rand();
			$callback = $config['http_home_url'] . 'index.php?do=pay&action=verify&bankid=mellat&orderid=' . $orderid;
			$result = Request($pay_config['mellat_terminalid'], $pay_config['mellat_username'], $pay_config['mellat_password'], $amount . '0', $orderid, $callback);
			if ($result['status']) {
				$userid = ($member_id['user_id']) ? $member_id['user_id'] : "0";
				$date = time();
				$db->query("INSERT INTO " . PREFIX . "_pay_invoices (`userid`, `name`, `date`, `mob`, `status`, `refid`, `bank`, `amount`, `message`) VALUES ('{$userid}', '{$name}', '{$date}', '{$mob}', '', '{$orderid}', 'ملت', '{$amount}', '{$message}')");
				echo json_encode(
					array(
						'status' => true,
						'url' => $result['url'],
						'ref' => $result['ref']
					)
				);
			} else {
				echo json_encode(
					array(
						'status' => false,
						'msg' => $result['msg']
					)
				);
			}
			die();
			break;
		case "zarinpal":
			require_once ENGINE_DIR . '/modules/pay/gateway/zarinpal.php';
			$callback = $config['http_home_url'] . 'index.php?do=pay&action=verify&bankid=zarinpal';
			$result = Request($pay_config['zarinpal_MerchantID'], $amount, $callback);
			if ($result['status']) {
				$userid = ($member_id['user_id']) ? $member_id['user_id'] : "0";
				$date = time();
				$db->query("INSERT INTO " . PREFIX . "_pay_invoices (`userid`, `name`, `date`, `mob`, `status`, `refid`, `bank`, `amount`, `message`) VALUES ('{$userid}', '{$name}', '{$date}', '{$mob}', '', '{$result['ref']}', 'زرین پال', '{$amount}', '{$message}')");
				echo json_encode(
					array(
						'status' => true,
						'url' => $result['url']
					)
				);
			} else {
				echo json_encode(
					array(
						'status' => false,
						'msg' => $result['msg']
					)
				);
			}
			die();
			break;

		case "payir":
			require_once ENGINE_DIR . '/modules/pay/gateway/payir.php';
			$callback = $config['http_home_url'] . 'index.php?do=pay&action=verify&bankid=payir';
			$result = send($pay_config['payir_api'], $amount, $callback);
			$result = json_decode($result);
			if ($result->status) {
				$userid = ($member_id['user_id']) ? $member_id['user_id'] : "0";
				$date = time();
				$token = $result->token;
				$db->query("INSERT INTO " . PREFIX . "_pay_invoices (`userid`, `name`, `date`, `mob`, `status`, `refid`, `bank`, `amount`, `message`) VALUES ('{$userid}', '{$name}', '{$date}', '{$mob}', '', '{$token}', 'pay.ir', '{$amount}', '{$message}')");
				echo json_encode(
					array(
						'status' => true,
						'url' => "https://pay.ir/pg/" . $result->token
					)
				);
			} else {
				echo json_encode(
					array(
						'status' => false,
						'errorCode' => $result->errorCode,
						'msg' => $result->errorMessage
					)
				);
			}
			die();
			break;
	}
}
?>