<?php
//get order deadline, and pickip location
require "giftcards_common.php";

$accountKey = 'shannon';
if( array_key_exists($accountKey,$_REQUEST)) {
	$val = trim($_REQUEST[$accountKey]);
	if($val === $Spreadsheet_ID){

		$displayStr = '
		<html>
		<head>
		<title>
			Welcome--SKSC GiftCard Ordering
		</title>
		</head>
		<body>
			<table align="center">
				<tr>
					<td><image src="sksc.jpg"></td>
				</tr>
		';
		$packagedData = getTemplateInfo();
		$Order_Deadline = $packagedData[2];
		$deadline_date = strtotime($Order_Deadline);

		 '';
		$today = time();
		if($today < $deadline_date)
		{
			$displayStr .= '
				<tr>
					<td>Enter Your SKSC Account Email</td>
				</tr>
				<tr>
					<form method="post" action="welcome_validate.php" autocomplete="on">
					<td>
						<input type="email" name="AccountEmail" size="20"/>
						<input type="submit" id="go" value="Go" />
					</td>
					';
					$displayStr .= '<input type="hidden" name="Order_Deadline" value="'. $Order_Deadline.'" />';
					$displayStr .= '
					</form>
				</tr>
			';
		}
		else {
			$displayStr .= '
			<tr>
				<td>Order Deadline is expired. Please wait for next time. Order Deadline : '.$Order_Deadline.' </td>
			</tr>
			';
		}

		$displayStr .= '
			</table>
		</body>
		</html>
			';
		echo $displayStr;
	}
}
?>
