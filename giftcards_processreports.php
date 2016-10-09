<?php
require "giftcards_common.php";
	$reportType = $_POST["reportType"];
	$superEmail = $_POST["superEmail"];
	$deadline = $_POST["deadline"];
	//echo "Deadline:".$deadline.'<br>';
	if($reportType === "PickupAccount"){
		echo "<h2>Report for pickup with deadline ".$deadline."</h2>";
		echo "Please print this report out <br><br>";
		$orders = getOrderWithAccountAndDeadline_old("",$deadline);
		$data = reportByPickupOptionAndAccount($orders);

		echo displayReportForPickup($data);
	} else if($reportType === "VenderPrice") {
		echo "<h2>Report for Vendor and Price with deadline ".$deadline."</h2>";
		echo "Please print this report out <br><br>";

		$orders = getOrderWithAccountAndDeadline_old("",$deadline);
		$data = reportByVendorAndPrice($orders);
		echo displayReportForPurchase($data);
	}

	/*
	code to report single user order
	$orders = getOrderWithAccountAndDeadline_old($accountEmail, $date);
	echo displayReportForAccount($orders);
	*/
?>
