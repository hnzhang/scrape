<?php
require "giftcards_common.php";

function retrieveData()
{
	$url = "https://spreadsheets.google.com/feeds/list/1Kp0Lcneb_UUjE3Vi0FjpCNbXdTRLATjSpyNnLJx-E9Y/2/public/values?alt=json";

	$json = file_get_contents($url);
	$data = json_decode($json, TRUE);

	//print_r ($data);

	$rows = $data['feed']['entry'];
	$total = 0;

	foreach ($rows as $item) {
		$id = $item['gsx$id']['$t'];
		$vender   = $item['gsx$vendor']['$t'];
		$category =  $item['gsx$category']['$t'];
		echo "ID:".$id. " Vender: ". $vender . "  Type: " . $category . '<br>';
		$total =$total +1;
	}

	echo "Total:". $total;
}

function sendemailTest()
{
	$to = 'giftcards@surreyknights.com';

	$subject = 'Website Change Reqest';

	//$headers = "From: " . strip_tags($_POST['req-email']) . "\r\n";
	$headers = "From: giftcards@surreyknights.net\r\n";
	$headers .= "Reply-To: ". strip_tags($to) . "\r\n";
	$headers .= "CC: hnzhang@gmail.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$message .= '<p><strong>This is strong text</strong> while this is not.</p>';


	mail($to, $subject, $message, $headers);
}

//sendemailTest();
//retrieveData();
date_default_timezone_set('America/Los_Angeles');//pacific time
	//year/mm/dd hh:mm
	$datetime = date("m") .'/' . date("d") .'/'. date("Y").' '.date("H"). ':'.date("i");
	echo $datetime;

$orders = getOrderWithAccountAndDeadline("hnzhang@gmail.com", "10/7/2016");
var_dump($orders);
?>
