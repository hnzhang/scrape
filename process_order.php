<?php
require "giftcards_common.php";

$order_entries = array();
$orderDetailsInHTML='';
$Order_Deadline = "";
$AccountEmail  = "";
$PickupOption = "";
$orderRemitTotal = "";
$orderTotal = "";

function writeOrderToLog($emailAddress, $orderDeadline, $orderData) {
	$folder = "order_log_". str_replace('/', '_', $orderDeadline);
	if(!file_exists($folder)){
		mkdir($folder);
	}
	$file = $folder .'/'. str_replace("@", '-', $emailAddress);
	file_put_contents($file, $emailAddress."\r\n");
	file_put_contents($file, $orderData, FILE_APPEND |LOCK_EX);
	echo '<br>Logging<br>';
}

function validateOrderDetails($order_details) {
	global $Order_Deadline, $AccountEmail, $PickupOption;
	global $order_entries;
	global $orderDetailsInHTML;
	global $orderRemitTotal;
	global $orderTotal;

	$fileContent = '';

	foreach ($order_details as $vendor=> $order_per_price) {
		$fileContent .= "Vendor:".$vendor ."\r\n";
		$orderOfVendor = array();
		foreach($order_per_price as $price => $details){
			$fileContent .= "Card:".$price."\r\n";
			$orderPerPrice = array();
			foreach($details as $key =>$val) {
				$orderPerPrice[$key] = $val;
				$fileContent .= ' '. $key .':'.$val;
			}
			$orderPerPrice["Recurring"] = 'no';
			$orderPerPrice["Active"] = 'yes';
			$orderOfVendor[$price] = $orderPerPrice;
			$fileContent .= "\r\n";

		}
		$order_entries[$vendor] = $orderOfVendor;
	}
	$fileContent .= "\r\n";
	$fileContent .= 'Order deadline:' . $Order_Deadline ."\r\n";
	$fileContent .= 'Pickup: ' . $PickupOption . "\r\n";
	$fileContent .= "AccountEmail: ". $AccountEmail;
	writeOrderToLog($AccountEmail, $Order_Deadline, $fileContent);
	return true;
}

function postOrderWithCurl( $order_details){
	$post_url = "https://script.google.com/macros/s/AKfycbxeF8VkYYNgTI7V2ppGWA3U0udv3WI8UhSQEa6f_RPLcgo6fU4e/exec";//for SKSC new
	//$post_url = "https://script.google.com/macros/s/AKfycbyFuHnEvzt3XTNc9Sy8R5KZldFVLU75jD1tDvL6l5ck6kJ6nS8Z/exec";//for SKSC
	//$post_url = 'https://script.google.com/macros/s/AKfycbzYcZMNXvNuP7pR1yNyLugz-qby4RWWumbdmJbz3J4F5Pu3SBU6/exec';// for test
	//url-ify the data for the POST
	foreach($order_details as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');

	//open connection
	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $post_url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	echo "Send Orders...<br>";

	//execute post
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	$success = false;
	if($result === FALSE){
		echo "Failed<br>";
	}
	else {
		$success = true;
		echo "<br><br><br>Your Order Has been submit successfully!<br>";
		echo "<br>Thank you for your order. The Fundraising Chair has been notified.<br>";
	}

	//close connection
	curl_close($ch);
	return $success;
}


function sendEmailNotification($emailAddress, $orderDeadline, $pickupOption, $email_body) {
	//global $orderDetailsInHTML;
	$to = strip_tags($emailAddress);
	$subject = 'GiftCard Order; Pickup at ['.$pickupOption ."]";

	$headers = "From: giftcards@surreyknights.net\r\n";
	$headers .= "Reply-To: giftcards@surreyknights.net\r\n";
	$headers .= "CC: fundraising@surreyknights.com\r\nVersion: 1.0\r\n";
	$headers .= "CC: giftcards@surreyknights.com\r\nVersion: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$message .= $email_body;

	$message .= "<p>If you CANNOT see order details here or have WRONG order details, Please notify fundraising@surreyknights.com </p>";
	$message .= "<p> </p>";
	$message .= "<p> Ususally order will be ready in 5-7 business days of <strong>Order deadline</strong>. Order Deadline for this order is ";
	$message .= "<strong>" .$orderDeadline."</strong> <p> ";
	$message .= "<p> You make payment of the order onsite of pickup.</p> ";

	if(mail($to, $subject, $message, $headers) === true) {
		echo  "<br>You should received an email about your order details soon<br>";
	}
}

function displayPageHead(){
	echo '
	<html>
	<head>
		<title>
			Order Summary--SKSC GiftCard Ordering
		</title>
	</head>
	<body>
	<h1>Thanks for your GiftCard Order</h1>
	';
}

function displayPageEnd() {
	echo '
	</body>
	</html>
	';
}

if(!empty($_POST)) {
	displayPageHead();
		$order_Deadline = $_POST['Order_Deadline'];
		$accountEmail = $_POST['AccountEmail'];
		$pickupOption = $_POST['pickupOption'];
		$orderRemitTotal = $_POST['OrderRemitTotal'];
		$orderTotal = $_POST['OrderTotal'];

		$Order_Deadline = $order_Deadline;
		$AccountEmail = $accountEmail;
		$PickupOption = $pickupOption;


		echo "<h2>Order_Deadline:".$order_Deadline.'</h2>';
		echo "<h2>AccountEmail:".$accountEmail.'</h2>';
		echo "<h2>PickupOptions:".$pickupOption.'</h2>';
		$order_details = json_decode($_POST["order_details"], true);

		if(!is_null($order_details) ){
			if(validateOrderDetails($order_details)){

				$orders_to_post = array(
					'Order_Deadline' => $order_Deadline,
					'Account' => $accountEmail,
					'Pickup' => $pickupOption,
					'Order' =>json_encode($order_entries)
					);
				
				if(postOrderWithCurl($orders_to_post)){
					$orders = getOrderWithAccountAndDeadline($accountEmail, $order_Deadline);
					$displayStr = displayReportForAccount($orders);
					sendEmailNotification($accountEmail,$order_Deadline,$pickupOption, $displayStr);
					echo $displayStr;
				}
				
			}
		}
	displayPageEnd();
}
?>
