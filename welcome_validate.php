<?php
require "giftcards_common.php";

if(!empty($_POST)) {
	$val = $_POST['AccountEmail'];
	$Order_Deadline = $_POST['Order_Deadline'];
	
	if(filter_var($val, FILTER_VALIDATE_EMAIL)) {
		if(isSuperEmail($val)){
				header("Location: http://giftcards.surreyknights.net/giftcards_report.php?SuperEmail=".$val.'&Deadline='.$Order_Deadline);
				die();
		}
		else {

			session_start();

			$_SESSION['AccountEmail'] = $val;
			$_SESSION['Order_Deadline'] = $Order_Deadline;

			//$orders = getOrderWithAccountAndDeadline($val, $Order_Deadline);
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
}
?>
