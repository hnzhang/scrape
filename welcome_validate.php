<?php
require "giftcards_common.php";

if(!empty($_POST)) {
	$val = $_POST['AccountEmail'];
	if(filter_var($val, FILTER_VALIDATE_EMAIL)) {
		$Order_Deadline = $_POST['Order_Deadline'];

		$orders = getOrderWithAccountAndDeadline($val, $Order_Deadline);
		//if(count($orders) === 0){// no order before
			$AccountEmail = $val;
			header("Location: http://giftcards.surreyknights.net/giftcards_ordering.php?AccountEmail=".$AccountEmail);
			die();
/* disable for testing purpose
		}
		else {
			echo '<h1>You have ordered placed order for this ordering season.</h1>';
			//displayOrderInHTML($orders)
		}
*/
	}
}
?>
