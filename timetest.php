<?php 
function sendEmailNotificationBeforeSubmit($emailAddress, $logEmail, $orderDeadline, $pickupOption, $email_body, $stageInfo) {
	//global $orderDetailsInHTML;
	$to = strip_tags($logEmail);
	$subject = 'GiftCard Order--Debug Logging; Pickup at ['.$pickupOption ."]";

	$headers = "From: giftcards@surreyknights.net\r\n";
	$headers .= "Reply-To: giftcards@surreyknights.net\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$message .= "<p>Order Summary</p>";
	$message .= $email_body;

	$message .= "<p>This should cover all you have ordered in this Fundraising run </p>";
	$message .= "<p></p>";
	$message .= "<p>If you CANNOT see order details here or have WRONG order details, Please notify fundraising@surreynights.com </p>";
	$message .= "<p>Email ID:".$emailAddress." </p>";
	$message .= "<p>Order Stage Info:".$stageInfo." </p>";
	$message .= "<p> Ususally Order will be ready in 5-7 business days of <strong>Order deadline</strong>. Order Deadline for this order is ";
	$message .= "<strong>" .$orderDeadline."</strong> <p> ";
	$message .= "<p> You make payment of the order onsite of pickup.</p> ";

	if(mail($to, $subject, $message, $headers) === true) {
		echo  "<br>Logging...<br>";
	}
}



#time test
$timestamp_tmr='11/16/2016';
$deadline_date_tmr = strtotime($timestamp_tmr);
$deadline_str_tmr = date("Y-m-d", $deadline_date_tmr);
echo 'Tomorrow: '. $deadline_str_tmr . '<br>';

$timestamp_yes='11/14/2016';
$deadline_date_yes = strtotime($timestamp_yes);
$deadline_str_yes = date("Y-m-d", $deadline_date_yes);
echo "Yesterday: " . $deadline_str_yes . '<br>';

$today = time();
echo $deadline_date;
echo '<br>';
echo $today;
echo '<br>';
$today_str =  date("Y-m-d", $today);
echo $today_str .'<br>';
if($today_str < $deadline_str_tmr) {
	echo "less than tomorrow<br>";
}

if($today_str < $deadline_str_yes) {
	echo "we are in the past<br>";
}
else {
	echo "we are in today, greater than yesterday";
}

sendEmailNotificationBeforeSubmit("hnzhang@gmail.com","harry.zhn@gmail.com", "deadline", "pickupOption", "Hello", "tesing");
sendEmailNotificationBeforeSubmit("giftcards@surreyknights.net","harry.zhn@gmail.com", "deadline", "pickupOption", "Hello", "tesing");

?>
