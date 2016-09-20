<?php

$order_entries = array();

function validateOrderDetails($order_details) {
	global $Order_Deadline, $AccountEmail, $PickupOption;
	global $order_entries;
	echo '<br><br>';

	foreach ($order_details as $vendor=> $order_per_price) {
		echo '<h1>'.$vendor.'</h1>';
		 $orderOfVendor = array();
		foreach($order_per_price as $price => $details){
			echo '<h2>'.$price.'</h2>';
			$orderPerPrice = array();
			foreach($details as $key =>$val) {
				echo $key."--".$val;
				$orderPerPrice[$key] = $val;
			}
			$orderPerPrice["Recurring"] = 'no';
			$orderPerPrice["Active"] = 'yes';
			//echo '</h3>';//[remit, count]
			$orderOfVendor[$price] = $orderPerPrice;
		}
		$order_entries[$vendor] = $orderOfVendor;
	}
	//echo "<br>~~~~~~~~~~~order_entries------<br>";
	//var_dump($order_entries);
	return true;
}

function postOrderWithCurl( $order_details){
	$post_url = "https://script.google.com/macros/s/AKfycbyFuHnEvzt3XTNc9Sy8R5KZldFVLU75jD1tDvL6l5ck6kJ6nS8Z/exec";//for SKSC
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
	$to = strip_tags($emailAddress);
	$subject = 'GiftCard Order; Pickup at ['.$picupOption ."]";

	//$headers = "From: " . strip_tags($_POST['req-email']) . "\r\n";
	$headers = "From: giftcards@surreyknights.net\r\n";
	$headers .= "Reply-To: giftcards@surreyknights.net\r\n";
	//$headers .= "CC: fundraising@surreyknights.com\r\nVersion: 1.0\r\n";
	$headers .= "CC: giftcards@surreyknights.com\r\nVersion: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$message .= '<h2>Order Details</h2>';
	foreach($orders_details as $vendor=>$orderentries) {
		foreach($orderentries as $price => $entry){
			$message .= $vendor ."  |  ";
			$message .= $entry['Remit'] ."  | ". $price ." | ";
			$message .= $entry['Count'] ."  |<br> ";
		}
	}
	$message .= "<p> Ususally Order will be ready in 5-7 business days of <strong>Order deadline</strong>. Order Deadline for this order is ";
	$message .= "<strong>" .$orderDeadline."</strong> <p> ";
	$message .= "<p> You make payment of the order onsite of pickup.</p> ";

	if(mail($to, $subject, $message, $headers) === true) {
		echo  "<br>You should received an email about your oder details soon<br>";
	}
}


if(!empty($_POST)) {

		$order_Deadline = $_POST['Order_Deadline'];
		$accountEmail = $_POST['AccountEmail'];
		$pickupOption = $_POST['pickupOption'];
		echo "Order_Deadline:".$order_Deadline.'<br>';
		echo "AccountEmail:".$accountEmail.'<br>';
		echo "PickupOptions:".$pickupOption.'<br>';
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
				}

			}
		}
}
?>
