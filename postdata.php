<?php

function postOrderWithCurl( $order_details){
	$post_url = 'https://script.google.com/macros/s/AKfycbzYcZMNXvNuP7pR1yNyLugz-qby4RWWumbdmJbz3J4F5Pu3SBU6/exec';
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

function sendEmailNotification($emailAddress,$orderDeadline,$picupOption, $orders_details) {
	$to = strip_tags($emailAddress);
	$subject = 'GiftCard Order; Pickup at['.$picupOption ."]";

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
	$message .= "<p> Ususally Order will be ready in 5-7 business days of <strong>Order deadline</strong>. Order Deadline for this order is";
	$message .= "<strong>" .$orderDeadline."</strong> <p> ";
	if(mail($to, $subject, $message, $headers) === true) {
		echo  "<br>You should received an email about your oder details soon<br>";
	}
}
$AccountEmail = 'hnzhang@ea.com';
$PickupOption = "Friday 4:00--6:00 Guildford";
$Order_Deadline = '2016/09/21';

$order1 = array( 'Remit' =>'6.5%', 'Count'=>"4", 'Recurring'=>"no", 'Active' => 'no');
$order2 = array( 'Remit' =>'2.5%', 'Count'=>"6", 'Recurring'=>"no", 'Active' => 'no');

$orders = array(
	"Shell"=> array(
		"20.0"=>$order2
	),
	"Choice's Market"=>array(
		 "25.0"=>$order1
	 )
 );

$fields = array(
'Order_Deadline' => $Order_Deadline,
'Account' => $AccountEmail,
'Pickup' => $PickupOption,
'Order' =>json_encode($orders)
);

if(postOrderWithCurl($fields)){
	sendEmailNotification($AccountEmail,$Order_Deadline,$PickupOption, $orders);
}
?>
