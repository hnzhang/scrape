om<?php

$order_entries = array();
$orderDetailsInHTML='';
$Order_Deadline = "";
$AccountEmail  = "";
$PickupOption = "";
$orderRemitTotal = "";
$orderTotal = "";

function validateOrderDetails($order_details) {
	global $Order_Deadline, $AccountEmail, $PickupOption;
	global $order_entries;
	global $orderDetailsInHTML;
	global $orderRemitTotal;
	global $orderTotal;
	$orderDetailsInHTML =	'<br><h2>Order Details for your records:<h2>';

	foreach ($order_details as $vendor=> $order_per_price) {
		$orderDetailsInHTML .= '<h3>'.$vendor.'</h3>';
		$orderOfVendor = array();
		foreach($order_per_price as $price => $details){
			$displayStr = '<ul align="left">'. $price. ' * '. $details["Count"] . " Remit: $". $details["RemitVal"] . '  Subtotal: $' . $details["Subtotal"].'</ul>';
			$orderDetailsInHTML .= $displayStr;
			$orderPerPrice = array();
			foreach($details as $key =>$val) {
				$orderPerPrice[$key] = $val;
			}
			$orderPerPrice["Recurring"] = 'no';
			$orderPerPrice["Active"] = 'yes';
			$orderOfVendor[$price] = $orderPerPrice;
		}
		$order_entries[$vendor] = $orderOfVendor;
	}
	$orderDetailsInHTML .= '<h2>' . 'Remit Total: $' .$orderRemitTotal . '</h2><h2>    Order Total: $' .$orderTotal.'</h2>';
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
	$result = curl_exec($ch);
	if($result === FALSE){
		echo "Failed<br>";
	}
	else {
		echo "<br><br><br>Your Order Has been submit successfully!<br>";

		echo "<br>Thank you for your order. The Fundraising Chair has been notified.<br>";
	}

	//close connection
	curl_close($ch);
	return $result === TRUE;
}

function sendEmailNotification($emailAddress, $orderDeadline, $picupOption, $orders_details) {
	global $orderDetailsInHTML;
	$to = strip_tags($emailAddress);
	$subject = 'GiftCard Order; Pickup at ['.$picupOption ."]";

	//$headers = "From: " . strip_tags($_POST['req-email']) . "\r\n";
	$headers = "From: giftcards@surreyknights.net\r\n";
	$headers .= "Reply-To: giftcards@surreyknights.net\r\n";
	//$headers .= "CC: fundraising@surreyknights.com\r\nVersion: 1.0\r\n";
	$headers .= "CC: giftcards@surreyknights.com\r\nVersion: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$message .=  $orderDetailsInHTML;

	$message .= "<p> Ususally Order will be ready in 5-7 business days of <strong>Order deadline</strong>. Order Deadline for this order is ";
	$message .= "<strong>" .$orderDeadline."</strong> <p> ";
	$message .= "<p> You make payment of the order onsite of pickup.</p> ";

	if(mail($to, $subject, $message, $headers) === true) {
		echo  "<br>You should received an email about your oder details soon<br>";
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
				//var_dump($orders_to_post);
				if(postOrderWithCurl($orders_to_post)){
					sendEmailNotification($accountEmail,$order_Deadline,$pickupOption, $order_entries);
					echo $orderDetailsInHTML;
				}
			}
		}
	displayPageEnd();
}
?>
