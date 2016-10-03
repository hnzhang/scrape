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
			array_push($orders, array($timeStamp, $pickup, $vendor, $price, $remit, $count));
		}
	}
	return $orders;
}
?>
