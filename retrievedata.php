<?php
require "giftcards_common.php";
$type = $_REQUEST["Type"];
$accountEmail = $_REQUEST["Account"];

$date = $_REQUEST["Date"];
if($type === '1') {//per user
	$orders = getOrderWithAccountAndDeadline_old($accountEmail, $date);
	echo displayReportForAccount($orders);
} else if( $type === '2') {// pickup and deadline
	echo "<h2>Report for pickup with deadline ".$date."</h2>";
	echo "Please print this report out <br><br>";
	$orders = getOrderWithAccountAndDeadline_old("",$date);
	$data = reportByPickupOptionAndAccount($orders);

	echo displayReportForPickup($data);
} else if ( $type === '3') {//purchase
	echo "<h2>Report for Vendor and Price with deadline ".$date."</h2>";
	echo "Please print this report out <br><br>";

	$orders = getOrderWithAccountAndDeadline_old("",$date);
	$data = reportByVendorAndPrice($orders);
	echo displayReportForPurchase($data);
}

?>
