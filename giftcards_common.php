<?php
//retrieve pickip options, order deadline and Special_Messages
$Spreadsheet_ID = "1VTseFM0BM-x-haK_We9vsd0sWAYrC7gkGU1mSWsOxBg";
$GetOrder_URL = "https://spreadsheets.google.com/feeds/list/".$Spreadsheet_ID."/3/public/values?alt=json";
function getTemplateInfo() {
	global $Spreadsheet_ID;

	$Special_Messages= "";
	$PickupOptions= array();
	$Order_Deadline = "";

	$template_url= "https://spreadsheets.google.com/feeds/list/".$Spreadsheet_ID."/1/public/values?alt=json";

	$json = file_get_contents($template_url);
	$data = json_decode($json, TRUE);
	$rows = $data['feed']['entry'];
	foreach ($rows as $item) {
		$opt = $item['gsx$pickups']['$t'];
		$opt = trim($opt);
		if (strlen($opt) > 0) {
				array_push($PickupOptions, $opt);
		}
		$nextOrderData = trim($item['gsx$nextorderdate']['$t']);
		if (strlen($nextOrderData) >0) {
				$Order_Deadline = $nextOrderData;
		}

		$msg = trim($item['gsx$specialmessages']['$t']);
		if (strlen($msg)) {
			$Special_Messages = $msg;
		}
	}
	return array($Special_Messages, $PickupOptions, $Order_Deadline);
}
//if $accountEmail is zero sized, return all
function getOrderWithAccountAndDeadline($accountEmail, $deadline) {
	global $GetOrder_URL ;
	$orders = array();

	$json = file_get_contents($GetOrder_URL);
	$data = json_decode($json, TRUE);
	$rows = $data['feed']['entry'];
	foreach($rows as $item) {
		$account_email = $item['gsx$account']['$t'];
		$orderDeadline = $item['gsx$orderdeadline']['$t'];

		if($orderDeadline === $deadline && ( strlen($accountEmail) === 0  || $accountEmail == $account_email ) ) {
			$timeStamp = $item['gsx$timestamp']['$t'];
			$pickup =  $item['gsx$pickup']['$t'];
			$vendor = $item['gsx$vendor']['$t'];
			$price = $item['gsx$price']['$t'];
			$remit = $item['gsx$remit']['$t'];

			$count = $item['gsx$count']['$t'];

			$remitRate = trim($remit);
			$remitRateFloat = floatVal($remitRate)/100.0;//trim off % sign;
			$countInt = intVal($count);

			$price = trim($price);
			$price = substr($price, 1, strlen($price) - 1);
			$priceFloat= floatVal($price);

			array_push($orders, array($timeStamp, $account_email, $pickup, $vendor, $priceFloat, $remitRateFloat, $countInt));
		}
	}
	return $orders;
}

function reportByVendorAndPrice($accountEmail, $deadline) {
	$orders = getOrderWithAccountAndDeadline("", $deadline);
	$orderGrp = array();
	$vendorSummary = array();
	foreach ($orders as $order) {
		$vendor = $order[3];
		$count = $order[6]);
		$price = $order[4];
		$priceStr= strval($price);
		$subtotal= $price * $count;
		if(array_key_exists($orderGrp, $vendor)) {
			$vendor = $orderGrp[$vendor];
			$if(array_key_exists($vendor, $priceStr)){
				$vendorCard = $vendor[$priceStr];
				$vendorCard[0] = $vendorCard[0] + $count;
				$vendorCard[1] = $vendorCard[1] + $subtotal;
			}
		} else {
			$vendor = array($priceStr, array($count, $subtotal));
		}
		if(array_key_exists($vendorSummary, $vendor)){
			$summary = $vendorSummary[$vendor];
			$vendorSummary[$vendor] = $summary + $subtotal;
		} else {
			array_push($vendorSummary, $vendor, $subtotal);
		}
	}
	return array($orderGrp, $vendorSummary);
}

function reportByPickupOPtionAndAccount($accountEmail, $deadline) {
	$orders = getOrderWithAccountAndDeadline("", $deadline);
	$orderGrp = array();
	$accountSummary = array();
	foreach ($orders as $order) {
		$pickupOption = $order[2];
		$accountEmail = $order[1];
		$remitRate = trim($order[5]);
		$count = $order[6]);
		$price = $order[4];

		$subtotal= $price * $count;
		$remitTotal = $subtotal * $remitRate;
		$pickupItem = array($order[3],$price, $count, $subtotal, $remitTotal);//vendor, price, count, subtotal, remit
		if(array_key_exists($pickupOption, $orderGrp)) {
			if(array_key_exists($orderGrp[$pickupOption], $accountEmail)){
				array_push($orderGrp[$pickupOption][$accountEmail], $pickupItem);
			} else {
				array_push($orderGrp[$pickupOption], array($accountEmail => array($pickupItem)));
			}
		}
		else {
			array_push($orderGrp, $pickupOption, array($accountEmail => array($pickupItem)));
		}
		if(array_key_exists($accountSummary, $accountEmail))
		{
			array_push($accountSummary, array( $subtotal,$subtotal - $remitTotal, $remitTotal));
		} else {
			$accountSummaryPerClient = $accountSummary[$accountEmail];
			$accountSummaryPerClient[0] = $accountSummaryPerClient[0] + $subtotal;
			$accountSummaryPerClient[1] = $accountSummaryPerClient[1] + $subtotal- $remitTotal;
			$accountSummaryPerClient[2] = $accountSummaryPerClient[2] + $remitTotal;
		}
	}
	return array($orderGrp, $accountSummary);
}
?>
