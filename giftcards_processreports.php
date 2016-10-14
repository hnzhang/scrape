<?php
require "giftcards_common.php";
	$reportType = $_POST["reportType"];
	$superEmail = $_POST["superEmail"];
	$deadline = $_POST["deadline"];
	//echo "Deadline:".$deadline.'<br>';
	if($reportType === "PickupAccount"){
		$xlsFileName = 'Pickup_Account_'. str_replace('/', '_', $deadline ) . '.xlsx';

		echo "<h2>Report for pickup with deadline ".$deadline."</h2>";
		echo "Please print this report out.  ";
		echo 'Or download Report in Excel from <a href="' . $xlsFileName . '">Here</a><br><br>';
		$orders = getOrderWithAccountAndDeadline("",$deadline);
		//$orders = getOrderWithAccountAndDeadline_old("",$deadline);
		$data = reportByPickupOptionAndAccount($orders);
		exportExcelReportForPickup($data,$xlsFileName );

		echo displayReportForPickup($data);
	} else if($reportType === "VenderPrice") {
		$xlsFileName = 'Vendor_Price_'. str_replace('/', '_', $deadline ) . '.xlsx';
		echo "<h2>Report for Vendor and Price with deadline ".$deadline."</h2>";
		echo "Please print this report out. ";
		echo 'Or download Report in Excel from <a href="' . $xlsFileName . '">Here</a><br><br>';

		$orders = getOrderWithAccountAndDeadline("",$deadline);
		//$orders = getOrderWithAccountAndDeadline_old("",$deadline);
		$data = reportByVendorAndPrice($orders);
		exportExcelReportForPurchase($data, $xlsFileName);
		echo displayReportForPurchase($data);
	}

	/*
	code to report single user order
	$orders = getOrderWithAccountAndDeadline($accountEmail, $date);
	echo displayReportForAccount($orders);
	*/
?>
