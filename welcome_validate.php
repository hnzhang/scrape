<?php
if(!empty($_POST)) {
	$val = $_POST['AccountEmail'];
	if(filter_var($val, FILTER_VALIDATE_EMAIL)) {
		$AccountEmail = $val;
		header("Location: http://giftcards.surreyknights.net/newform0.php?AccountEmail=".$AccountEmail);
		die();
	}
}
?>
