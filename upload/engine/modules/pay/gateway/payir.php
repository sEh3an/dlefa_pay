<?php
/*
=====================================================
 DLEFA Pay  Ver 1.0
-----------------------------------------------------
 Persian support site: https://dlefa.ir
-----------------------------------------------------
 FileName :  payir.php
-----------------------------------------------------
 Copyright (c) 2018, All rights reserved.
=====================================================
 */
function curl_post($url, $params)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}

function send($api, $amount, $redirect, $mobile = null, $factorNumber = null, $description = null)
{
    return curl_post('https://pay.ir/pg/send', [
        'api' => $api,
        'amount' => $amount * 10, // convert toman to rial!
        'redirect' => $redirect,
        'mobile' => $mobile,
        'factorNumber' => $factorNumber,
        'description' => $description,
    ]);
}

function Verify($api, $token)
{
    return curl_post('https://pay.ir/pg/verify', [
        'api' => $api,
        'token' => $token,
    ]);
}

?>