<?php
date_default_timezone_set('Canada/Vancouver');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';

//retrieve pickip options, order deadline and Special_Messages
$Spreadsheet_ID = "1VTseFM0BM-x-haK_We9vsd0sWAYrC7gkGU1mSWsOxBg";//new id
//$Spreadsheet_ID = '1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y';
$GetOrder_URL = "https://spreadsheets.google.com/feeds/list/".$Spreadsheet_ID."/3/public/values?alt=json";
function isSuperEmail($emailAddress) {
	$emailList = array("giftcards@surreyknights.com", "fundraising@surreyknights.com");
	return in_array($emailAddress, $emailList);
}

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

function getOrderWithAccountAndDeadline_old($accountEmail, $deadline) {
	$Spreadsheet_ID_old = '1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y';

	$GetOrder_URL_old = "https://spreadsheets.google.com/feeds/list/".$Spreadsheet_ID_old."/3/public/values?alt=json";
	$orders = array();

	$json = file_get_contents($GetOrder_URL_old);
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

function reportByVendorAndPrice( $orders) {
	//$orders = getOrderWithAccountAndDeadline_old("", $deadline);
	$orderGrp = array();
	$vendorSummary = array();
	//var_dump($orders);
	foreach ($orders as $order) {
		$vendorName = $order[3];
		$count = $order[6];
		$price = $order[4];
		$priceStr= strval($price);
		$subtotal= $price * $count;
		//echo 'Price: '. $priceStr. " Count:". $count. ' <br>';
		if(array_key_exists( $vendorName, $orderGrp)) {
			$vendor = $orderGrp[$vendorName];
			if(array_key_exists($priceStr, $vendor ) ) {
				$vendorCard = $vendor[$priceStr];
				$orderGrp[$vendorName][$priceStr][0] = $vendorCard[0] + $count;
				$orderGrp[$vendorName][$priceStr][1] = $vendorCard[1] + $subtotal;
			} else {
				$orderGrp[$vendorName][$priceStr] = array($count, $subtotal);
			}
		} else {
			$orderGrp[$vendorName] = array($priceStr=> array($count, $subtotal));
		}

		if(array_key_exists($vendorName, $vendorSummary )){
			$summary = $vendorSummary[$vendorName] = $vendorSummary[$vendorName] + $subtotal;
		} else {
			$vendorSummary[$vendorName] = $subtotal;
		}
	}
	return array($orderGrp, $vendorSummary);
}

function reportByPickupOptionAndAccount( $orders) {
	$orderGrp = array();
	$accountSummary = array();
	//var_dump($orders);
	foreach ($orders as $order) {
		$pickupOption = $order[2];
		$accountEmail = $order[1];
		$remitRate = trim($order[5]);
		$count = $order[6];
		$price = $order[4];

		$subtotal= $price * $count;
		$remitTotal = $subtotal * $remitRate;
		$pickupItem = array($order[3],$price, $count, $subtotal, $remitTotal);//vendor, price, count, subtotal, remit

		if(array_key_exists($pickupOption, $orderGrp)) {
			if(array_key_exists($accountEmail, $orderGrp[$pickupOption] )){
				array_push($orderGrp[$pickupOption][$accountEmail], $pickupItem);
			} else {
				$orderGrp[$pickupOption][$accountEmail] = array($pickupItem);
			}
		}
		else {
			$orderGrp[$pickupOption] = array($accountEmail => array($pickupItem));
		}

		if(!array_key_exists($accountEmail, $accountSummary)){
			$accountSummary[$accountEmail] = array( $subtotal,$subtotal - $remitTotal, $remitTotal);
		} else {
			$accountSummaryPerClient = $accountSummary[$accountEmail];
			$accountSummaryPerClient[0] = $accountSummaryPerClient[0] + $subtotal;
			$accountSummaryPerClient[1] = $accountSummaryPerClient[1] + $subtotal- $remitTotal;
			$accountSummaryPerClient[2] = $accountSummaryPerClient[2] + $remitTotal;
		}
	}
	return array($orderGrp, $accountSummary);
}
//$data, a list of order
// return a string formatting data into html
function displayReportForAccount($data){
	$displayStr ='<table><tr>';
	if(is_null($data)  || !is_array($data) || count($data) === 0 ){
		$displayStr .="<td>No data available!</td></tr></table>";
		return;
	}
	$accountEmail = $data[0][1];
	$displayStr .='<th  collspan="6">Account: '.$accountEmail.'</th>';
	$displayStr .='</tr>';
	$displayStr .='
		<th style="text-align: left;">Order Time</th>
		<th  style="width: 300px; text-align: left;">PickupOption</th>
		<th  style="width: 200px; text-align: left;">Vendor</th>
		<th  style="width: 50px; text-align: left;">Price</th>
		<th  style="width: 50px; text-align: left;">Count</th>
		<th  style="width: 50px; text-align: left;">Remit</th>
	</tr>
	';
	//array($timeStamp, $account_email, $pickup, $vendor, $priceFloat, $remitRateFloat, $countInt));
	$subtotal = 0.0;
	$totalremit = 0.0;
	$totalDue = 0.0;
	setlocale(LC_MONETARY, 'en_CA');

	foreach($data as $order) {
		$subtotal = $subtotal + $order[4]*$order[6];
		$remit =  $order[4]*$order[6]*$order[5];
		$totalremit = $totalremit + $remit;
		$displayStr .= "<tr>";
			$displayStr .= "<td>".$order[0]."</td>";

			$displayStr .= "<td>".$order[2]."</td>";
			$displayStr .= "<td>".$order[3]."</td>";
			$displayStr .= "<td>".money_format('%i', $order[4])."</td>";
			$displayStr .= "<td>".$order[6]."</td>";//count
			$displayStr .= "<td>".money_format('%i', $remit)."</td>";//remit
		$displayStr .= "</tr>";
	}

	$displayStr .= '<tr><td collspan="6"><br></td></td>';
	$displayStr .= "<tr>";
		$displayStr .= "<td></td>";
		$displayStr .= "<td></td>";
		$displayStr .= "<td> TotalValue: ".money_format('%i', $subtotal)."</td>";
		$displayStr .= "<td> Total Remit: ".money_format('%i', $totalremit)."</td>";
		$displayStr .= "<td></td>";
		//$displayStr .= "<td> Total Due: ". money_format('%i', $subtotal - $totalremit) ."</td>";
		$displayStr .= "<td> </td>";
	$displayStr .= "</tr>";
	$displayStr .= '</table>';

	return $displayStr;
}

function exportExcelReportForPickup($data, $xlsFileName) {
	date_default_timezone_set('Canada/Vancouver');
	$pickupList = $data[0];
	$accountSummary = $data[1];
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("SKSC Fundraising")
							 ->setLastModifiedBy("SKSC Fundraising")
							 ->setTitle("Pickup and Account Report")
							 ->setSubject("Pickup and Account")
							 ->setDescription("This is report for Pickup and Account.")
							 ->setKeywords("Pickup Account Fundraising SKSC")
							 ->setCategory("SKSC Fundraising");

	$activeSheet = $objPHPExcel->setActiveSheetIndex(0);
	$activeSheet->setCellValue('A1', 'Pickup')
							->setCellValue('B1', 'Account')
							->setCellValue('C1', 'Vendor')
							->setCellValue('D1', 'Price')
							->setCellValue('E1', 'Count')
							->setCellValue('F1', 'Remit')
							->setCellValue('G1', 'Total Due');

		$rowId = 2;
	foreach($pickupList as $pickup => $pickupAccounts) {
		foreach ($pickupAccounts as $accountEmail => $orders) {
			foreach($orders as $orderItem) {
				$activeSheet->setCellValue('A'.$rowId, $pickup)
										->setCellValue('B'.$rowId, $accountEmail)
										->setCellValue('C'.$rowId, $orderItem[0])
										->setCellValue('D'.$rowId, strval($orderItem[1]))
										->setCellValue('E'.$rowId, $orderItem[2])
										->setCellValue('F'.$rowId, strval($orderItem[3]))
										->setCellValue('G'.$rowId, strval($orderItem[4]));
				$rowId = $rowId +1;
			}
		}
	}


	$activeSheet->getStyle('D1:D'.$rowId)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	$activeSheet->getStyle('F1:F'.$rowId)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	$activeSheet->getStyle('G1:G'.$rowId)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);



	$activeSheet->setCellValue('I1', 'Account')
							->setCellValue('J1', 'Total Value')
							->setCellValue('K1', 'Total Due')
							->setCellValue('L1', 'Total Remit');

	$rowId = 2;
	foreach ($accountSummary as $accountEmail => $summary) {
		$activeSheet->setCellValue('I'.$rowId, $accountEmail)
								->setCellValue('J'.$rowId, strval($summary[0]))
								->setCellValue('K'.$rowId, strval($summary[1]))
								->setCellValue('L'.$rowId, strval($summary[2]));
		$rowId = $rowId +1;
	}

	$activeSheet->getStyle('J1:J'.$rowId)
		->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	$activeSheet->getStyle('K1:K'.$rowId)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	$activeSheet->getStyle('L1:L'.$rowId)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

	//save to local
	$xlsFilePath = dirname(__FILE__) . '/' . $xlsFileName;
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save($xlsFilePath);
	return $xlsFilePath;
}

function displayReportForPickup($data) {
	/*
	data[0] is orderGrp
	orderrGrp{ PickupOption: {"AccountEmail: array(vendor,$price, $count, $subtotal, $remitTotal)"}}

	data[1] is orderSummary
	orderSummary { AccountEmail : arrray(subtoal, totalDue, remitTotal)}
		*/
	setlocale(LC_MONETARY, 'en_CA');
	$displayStr = '<table style =" border-collapse: collapse"> ';
	$pickupList = $data[0];
	$accountSummary = $data[1];
	foreach($pickupList as $pickup => $pickupAccounts) {
		$displayStr .= '<tr>
			<th  style=" width: 600px; text-align: center;border: 1px solid black" colspan="6" >Pickup: '.$pickup.'</th>
			</tr>';

			$displayStr .='<tr><th  style="width: 100px; text-align: left;border: 1px solid black">Account</th>
				<th  style="width: 250px; text-align: left;border: 1px solid black">Vendor</th>
				<th  style="width: 100px; text-align: left;border: 1px solid black">Price</th>
				<th  style="width: 50px; text-align: left;border: 1px solid black">Count</th>
				<th  style="width: 100px; text-align: left;border: 1px solid black">Remit</th>
				<th  style="width: 100px; text-align: left;border: 1px solid black">Total Due</th>
			</tr>
		';
		foreach ($pickupAccounts as $accountEmail => $orders) {
			foreach($orders as $orderItem) {
				$displayStr .='<tr><td  style="width: 100px; text-align: left;border: 1px solid black">'.$accountEmail.'</th>
					<td  style="width: 200px; text-align: left;border: 1px solid black">'.$orderItem[0].'</th>
					<td  style="width: 100px; text-align: left;border: 1px solid black">'.money_format('%i',$orderItem[1]).'</th>
					<td  style="width: 100px; text-align: left;border: 1px solid black">'.$orderItem[2].'</th>
					<td  style="width: 100px; text-align: left;border: 1px solid black">'.money_format('%i',$orderItem[3]).'</th>
					<td  style="width: 100px; text-align: left;border: 1px solid black">'.money_format('%i', $orderItem[4]).'</th>
				</tr>
			';
			}
		}

	}//for orderitems

	$displayStr.='</td></table>';

	$summaryDisplayStr ='<table style ="border-collapse: collapse">
		<tr>
			<th  style=" width: 400px; text-align: center;border: 1px solid black" colspan="4">Account Summary</th>
		</tr>
		';
		$summaryDisplayStr .='
		<tr>
			<th  style="width: 150px; text-align: left;border: 1px solid black">Account</th>
			<th  style="width: 80px; text-align: left;border: 1px solid black">Total Value</th>
			<th  style="width: 80px; text-align: left;border: 1px solid black">Total Due</th>
			<th  style="width: 120px; text-align: left;border: 1px solid black">Total Remit</th>
		</tr>';
	foreach ($accountSummary as $accountEmail => $summary) {
		$summaryDisplayStr .='<tr>
 				<td  style="width: 150px; text-align: left;border: 1px solid black">'.$accountEmail.'</th>
				<td  style="width: 80px; text-align: left;border: 1px solid black">'. money_format('%i',$summary[0]) . '</th>
				<td  style="width: 80px; text-align: left;border: 1px solid black">'. money_format('%i',$summary[1]) . '</th>
				<td  style="width: 120px; text-align: left;border: 1px solid black">'. money_format('%i',$summary[2]) . '</th>
			</tr>';
	}
	$summaryDisplayStr .= "</table>";

	$returnStr = '<table><tr>';
	$returnStr .= '<td>' .$summaryDisplayStr.'</td></tr>';
	$returnStr .= '<tr><td><br><br><br></td></tr>';
	$returnStr .= '<tr><td>' .$displayStr.'</td></tr>';
	$returnStr .= '<tr><td><br>=== End of the report ===</td></tr></table>';

	return $returnStr;
}


function displayReportForPurchase($data) {
	$orderSummary = $data[0];
	$vendorSummary = $data[1];
	setlocale(LC_MONETARY, 'en_CA');
	$orderSummaryStr = '<table style ="border-collapse: collapse">';
	foreach($orderSummary as $vendorName => $orders) {
		$orderSummaryStr .='<tr>
			<th  style="width: 150px; text-align: center;border: 1px solid black" colspan="3">'. $vendorName.'</th>
		</tr>
			<tr>
				<th  style="width: 50px; text-align: left;border: 1px solid black">Price</th>
				<th  style="width: 50px; text-align: left;border: 1px solid black">Count</th>
				<th  style="width: 50px; text-align: left;border: 1px solid black">Total</th>
			</tr>
			';
		foreach($orders as $priceTag => $orderItem) {
			$orderSummaryStr .='<tr>
				<td  style="width: 200px; text-align: left;border: 1px solid black">'.$priceTag.'</th>
				<td  style="width: 100px; text-align: left;border: 1px solid black">'.$orderItem[0].'</th>
				<td  style="width: 100px; text-align: left;border: 1px solid black">'.money_format('%i',$orderItem[1]).'</th>
			</tr>
			';
		}
	}
	$orderSummaryStr .='</table>';

	$vendorSummaryStr = '<table  style ="border-collapse: collapse">
			<tr>
			<th  style=" text-align: left;border: 1px solid black">Vendor</th>
			<th  style="width: 50px; text-align: left;border: 1px solid black">Price</th>
			</tr>';
	foreach($vendorSummary as $vendorName => $total) {
		$vendorSummaryStr .= '<tr>
				<td  style=" text-align: left;border: 1px solid black">'.$vendorName.'</th>
				<td  style="width: 50px; text-align: left;border: 1px solid black">'.money_format('%i',$total).'</th>
			</tr>';
	}
	$vendorSummaryStr .= '</table>';

	$returnStr = '<table><tr>';
	$returnStr .= '<td>' .$orderSummaryStr.'</td></tr>';
	$returnStr .= '<tr><td><br><br><br></td></tr>';
	$returnStr .= '<tr><td >' .$vendorSummaryStr.'</td></tr>';
	$returnStr .= '<tr><td>=== End of report ===</td></tr>';
	$returnStr .= '</table>';
	return $returnStr;
}

function exportExcelReportForPurchase($data, $xlsFileName) {
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("SKSC Fundraising")
							 ->setLastModifiedBy("SKSC Fundraising")
							 ->setTitle("Vendor and Pricing Report")
							 ->setSubject("Vendor and Pricing")
							 ->setDescription("This is report for Vendor and Pricing.")
							 ->setKeywords("Vendor and Pricing Fundraising SKSC")
							 ->setCategory("SKSC Fundraising");

	$activeSheet = $objPHPExcel->setActiveSheetIndex(0);
	$rowId = 1;
	$orderSummary = $data[0];
	$vendorSummary = $data[1];
	foreach($orderSummary as $vendorName => $orders) {
		$activeSheet->setCellValue('A'.$rowId, $VendorName);//TODO: set format to bold
		$rowId = $rowId + 1;
		$activeSheet->setCellValue('A'.$rowId, 'price');
		$activeSheet->setCellValue('B'.$rowId, 'Count');
		$activeSheet->setCellValue('C'.$rowId, 'Total');
		$rowId = $rowId + 1;
		foreach($orders as $priceTag => $orderItem) {
			$activeSheet->setCellValue('A'.$rowId, $priceTag);
			$activeSheet->setCellValue('B'.$rowId, $orderItem[0]);
			$activeSheet->setCellValue('C'.$rowId, money_format('%i',$orderItem[1]));
			$rowId = $rowId + 1;
		}
	}

	$rowId = 1;
	$activeSheet->setCellValue('F'.$rowId, 'Vendor');
	$activeSheet->setCellValue('G'.$rowId, 'Price');
	$rowId = $rowId + 1;
	foreach($vendorSummary as $vendorName => $total) {
		$activeSheet->setCellValue('F'.$rowId, $vendorName);
		$activeSheet->setCellValue('G'.$rowId, money_format('%i',$total));
		$rowId = $rowId + 1;
	}

	//save to local
	$xlsFilePath = dirname(__FILE__) . '/' . $xlsFileName;
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save($xlsFilePath);
	return $xlsFilePath;
}
?>
